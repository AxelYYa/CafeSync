<?php

namespace App\Http\Controllers;

use App\Enums\CanalOrigen;
use App\Enums\EstadoOrden;
use App\Exceptions\ReglaNegocioException;
use App\Models\Orden;
use App\Services\Ventas\CierreVentaService;
use App\Services\Ventas\OrdenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrdenController extends Controller
{
    public function __construct(
        private readonly OrdenService $ordenes,
        private readonly CierreVentaService $cierre,
    ) {}

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'estado' => ['nullable', Rule::in(array_column(EstadoOrden::cases(), 'value'))],
            'canal' => ['nullable', Rule::in(CanalOrigen::valores())],
        ]);

        $ordenes = Orden::query()
            ->with(['usuario:id_usuario,nombre', 'detalles:id_detalle_orden,id_orden,nombre_producto_snapshot,cantidad,estado_preparacion'])
            ->when($filtros['estado'] ?? null, fn ($consulta, $estado) => $consulta->where('estado', $estado))
            ->when(! ($filtros['estado'] ?? null), fn ($consulta) => $consulta->whereIn('estado', array_column(EstadoOrden::activos(), 'value')))
            ->when($filtros['canal'] ?? null, fn ($consulta, $canal) => $consulta->where('canal_origen', $canal))
            ->latest('id_orden')
            ->paginate(20)
            ->withQueryString();

        return view('pos.ordenes', [
            'ordenes' => $ordenes,
            'filtros' => $filtros,
            'estados' => EstadoOrden::cases(),
            'canales' => CanalOrigen::cases(),
        ]);
    }

    public function show(Orden $orden): View
    {
        $orden->load([
            'usuario:id_usuario,nombre',
            'detalles.estacion:id_estacion,nombre',
            'historial.usuario:id_usuario,nombre',
            'detalles.movimientos.ingrediente:id_ingrediente,nombre,unidad_medida',
        ]);

        return view('pos.orden', ['orden' => $orden]);
    }

    public function entregar(Orden $orden): RedirectResponse
    {
        return $this->ejecutar(
            fn () => $this->ordenes->entregar($orden),
            "Orden {$orden->folio} entregada."
        );
    }

    public function cerrar(Request $request, Orden $orden): RedirectResponse
    {
        $datos = $request->validate([
            'metodo_pago_registrado' => ['nullable', Rule::in(['efectivo', 'tarjeta', 'transferencia', 'otro'])],
        ]);

        $yaCerrada = $orden->fresh()?->estaCerrada();

        return $this->ejecutar(
            fn () => $this->cierre->cerrar($orden, $request->user(), $datos['metodo_pago_registrado'] ?? null),
            $yaCerrada
                ? "La orden {$orden->folio} ya estaba cerrada; no se volvió a descontar inventario."
                : "Venta {$orden->folio} cerrada. Inventario actualizado."
        );
    }

    public function cancelar(Request $request, Orden $orden): RedirectResponse
    {
        $datos = $request->validate(['motivo' => ['nullable', 'string', 'max:255']]);

        return $this->ejecutar(
            fn () => $this->ordenes->cancelar($orden, $datos['motivo'] ?? null),
            "Orden {$orden->folio} cancelada."
        );
    }

    public function revertir(Request $request, Orden $orden): RedirectResponse
    {
        $datos = $request->validate(['motivo' => ['required', 'string', 'min:5', 'max:200']]);

        return $this->ejecutar(
            fn () => $this->cierre->revertir($orden, $request->user(), $datos['motivo']),
            "Venta {$orden->folio} revertida. Los ingredientes regresaron al inventario."
        );
    }

    private function ejecutar(callable $accion, string $mensajeExito): RedirectResponse
    {
        try {
            $accion();
        } catch (ReglaNegocioException $error) {
            return back()->withErrors(['orden' => $error->getMessage()]);
        }

        return back()->with('estado', $mensajeExito);
    }
}
