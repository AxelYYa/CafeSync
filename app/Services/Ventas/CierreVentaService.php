<?php

namespace App\Services\Ventas;

use App\Enums\EstadoOrden;
use App\Enums\TipoMovimiento;
use App\Exceptions\ReglaNegocioException;
use App\Models\DetalleOrden;
use App\Models\Orden;
use App\Models\Receta;
use App\Models\Usuario;
use App\Repositories\InventarioRepository;
use App\Services\Inventario\RequerimientosService;
use Illuminate\Support\Facades\DB;

/**
 * Servicio de dominio del cierre de venta: valida, descuenta ingredientes por receta y cierra la orden
 * dentro de una sola transacción. Es idempotente: cerrar una orden ya cerrada no vuelve a descontar.
 */
class CierreVentaService
{
    public function __construct(
        private readonly InventarioRepository $inventario,
        private readonly RequerimientosService $requerimientos,
    ) {}

    /**
     * @throws ReglaNegocioException
     */
    public function cerrar(Orden $orden, Usuario $usuario, ?string $metodoPago = null): Orden
    {
        return DB::transaction(function () use ($orden, $usuario, $metodoPago): Orden {
            $orden = Orden::query()
                ->with(['detalles.recetaAplicada.detalles', 'detalles.producto.recetaActiva.detalles'])
                ->lockForUpdate()
                ->findOrFail($orden->id_orden);

            if ($orden->estaCerrada()) {
                return $orden;
            }

            if (! in_array($orden->estado, EstadoOrden::cerrables(), true)) {
                throw new ReglaNegocioException('Solo se puede cerrar una orden lista o entregada.');
            }

            $recetas = $this->recetasAplicables($orden);
            $requeridos = $this->requerimientos->calcular(
                $orden->detalles->map(fn (DetalleOrden $detalle) => [
                    'receta' => $recetas[$detalle->id_detalle_orden],
                    'cantidad' => $detalle->cantidad,
                ])
            );

            $ingredientes = $this->inventario->bloquear(array_keys($requeridos));
            $this->requerimientos->validarExistencias($requeridos, $ingredientes);

            foreach ($orden->detalles as $detalle) {
                $receta = $recetas[$detalle->id_detalle_orden];

                foreach ($receta->detalles as $componente) {
                    if ($this->inventario->existeMovimiento($detalle->id_detalle_orden, $componente->id_ingrediente, TipoMovimiento::Consumo)) {
                        continue;
                    }

                    $this->inventario->registrarMovimiento(
                        $ingredientes[$componente->id_ingrediente],
                        TipoMovimiento::Consumo,
                        false,
                        bcmul((string) $componente->cantidad, (string) $detalle->cantidad, 3),
                        $usuario->id_usuario,
                        $detalle->id_detalle_orden,
                        "Venta {$orden->folio}",
                    );
                }

                $detalle->forceFill(['id_receta_aplicada' => $receta->id_receta])->save();
            }

            $orden->forceFill([
                'estado' => EstadoOrden::Cerrada,
                'cerrada_en' => now(),
                'metodo_pago_registrado' => $metodoPago,
            ])->save();

            return $orden;
        });
    }

    /**
     * Cancela una venta cerrada devolviendo los ingredientes mediante movimientos de reversión.
     * Es una operación reservada al gerente; también es idempotente.
     *
     * @throws ReglaNegocioException
     */
    public function revertir(Orden $orden, Usuario $gerente, string $motivo): Orden
    {
        if (! $gerente->esGerente()) {
            throw new ReglaNegocioException('Solo el gerente puede revertir una venta cerrada.');
        }

        return DB::transaction(function () use ($orden, $gerente, $motivo): Orden {
            $orden = Orden::query()->with('detalles.movimientos')->lockForUpdate()->findOrFail($orden->id_orden);

            if ($orden->estado === EstadoOrden::Cancelada) {
                return $orden;
            }

            if (! $orden->estaCerrada()) {
                throw new ReglaNegocioException('Solo se puede revertir una venta cerrada.');
            }

            $consumos = $orden->detalles->flatMap(fn (DetalleOrden $detalle) => $detalle->movimientos)
                ->filter(fn ($movimiento) => $movimiento->tipo === TipoMovimiento::Consumo);

            $ingredientes = $this->inventario->bloquear($consumos->pluck('id_ingrediente')->unique()->values()->all());

            foreach ($consumos as $consumo) {
                if ($this->inventario->existeMovimiento($consumo->id_detalle_orden, $consumo->id_ingrediente, TipoMovimiento::Reversion)) {
                    continue;
                }

                $this->inventario->registrarMovimiento(
                    $ingredientes[$consumo->id_ingrediente],
                    TipoMovimiento::Reversion,
                    true,
                    (string) $consumo->cantidad,
                    $gerente->id_usuario,
                    $consumo->id_detalle_orden,
                    "Reversión de {$orden->folio}: {$motivo}",
                );
            }

            $orden->forceFill([
                'estado' => EstadoOrden::Cancelada,
                'nota' => trim(($orden->nota ? $orden->nota."\n" : '')."Reversión: {$motivo}"),
            ])->save();

            return $orden;
        });
    }

    /**
     * Receta de cada renglón: la ya aplicada o, si es la primera vez, la receta activa del producto.
     *
     * @return array<int, Receta>
     *
     * @throws ReglaNegocioException
     */
    private function recetasAplicables(Orden $orden): array
    {
        $recetas = [];

        foreach ($orden->detalles as $detalle) {
            $receta = $detalle->recetaAplicada ?? $detalle->producto->recetaActiva;

            if ($receta === null || $receta->detalles->isEmpty()) {
                throw new ReglaNegocioException("{$detalle->nombre_producto_snapshot} no tiene receta configurada; no se puede cerrar la venta.");
            }

            $recetas[$detalle->id_detalle_orden] = $receta;
        }

        return $recetas;
    }
}
