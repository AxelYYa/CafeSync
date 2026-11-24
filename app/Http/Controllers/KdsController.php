<?php

namespace App\Http\Controllers;

use App\Enums\EstadoPreparacion;
use App\Exceptions\ReglaNegocioException;
use App\Models\DetalleOrden;
use App\Models\Estacion;
use App\Services\Ventas\KdsService;
use App\Services\Ventas\PreparacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KdsController extends Controller
{
    public const INTERVALO_CONSULTA_MS = 800;

    public function __construct(
        private readonly KdsService $kds,
        private readonly PreparacionService $preparacion,
    ) {}

    public function index(): View
    {
        $estaciones = Estacion::query()
            ->where('activa', true)
            ->withCount(['detallesOrden as pendientes_count' => fn ($consulta) => $consulta
                ->whereIn('estado_preparacion', [EstadoPreparacion::Pendiente->value, EstadoPreparacion::EnPreparacion->value])
                ->whereHas('orden', fn ($ordenes) => $ordenes->whereIn('estado', ['capturada', 'en_preparacion']))])
            ->orderBy('nombre')
            ->get();

        return view('kds.index', ['estaciones' => $estaciones]);
    }

    public function show(Estacion $estacion): View
    {
        $ordenes = $this->kds->ordenesDeEstacion($estacion);

        return view('kds.show', [
            'estacion' => $estacion,
            'ordenes' => $ordenes,
            'version' => $this->kds->version($ordenes),
            'intervalo' => self::INTERVALO_CONSULTA_MS,
        ]);
    }

    /**
     * Respuesta de la consulta periódica: si la versión no cambió, no se vuelve a renderizar el HTML.
     */
    public function datos(Request $request, Estacion $estacion): JsonResponse
    {
        $ordenes = $this->kds->ordenesDeEstacion($estacion);
        $version = $this->kds->version($ordenes);

        if ($request->query('version') === $version) {
            return response()->json(['version' => $version, 'cambio' => false]);
        }

        return response()->json([
            'version' => $version,
            'cambio' => true,
            'total' => $ordenes->count(),
            'html' => view('kds._tarjetas', ['ordenes' => $ordenes, 'estacion' => $estacion])->render(),
        ]);
    }

    public function avanzar(Request $request, DetalleOrden $detalle): JsonResponse|RedirectResponse
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in([EstadoPreparacion::EnPreparacion->value, EstadoPreparacion::Lista->value])],
        ]);

        try {
            $detalle = $this->preparacion->avanzar($detalle, EstadoPreparacion::from($datos['estado']));
        } catch (ReglaNegocioException $error) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $error->getMessage()], 422);
            }

            return back()->withErrors(['kds' => $error->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json(['id_detalle_orden' => $detalle->id_detalle_orden, 'estado' => $detalle->estado_preparacion->value]);
        }

        return back();
    }
}
