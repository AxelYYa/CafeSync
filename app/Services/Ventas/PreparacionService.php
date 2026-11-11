<?php

namespace App\Services\Ventas;

use App\Enums\EstadoOrden;
use App\Enums\EstadoPreparacion;
use App\Exceptions\ReglaNegocioException;
use App\Models\DetalleOrden;
use Illuminate\Support\Facades\DB;

class PreparacionService
{
    /**
     * Avanza el estado de preparación de un renglón. Las transiciones sólo van hacia adelante:
     * pendiente → en preparación → lista. El observador del renglón sincroniza el estado de la orden.
     *
     * @throws ReglaNegocioException
     */
    public function avanzar(DetalleOrden $detalle, EstadoPreparacion $destino): DetalleOrden
    {
        return DB::transaction(function () use ($detalle, $destino): DetalleOrden {
            $detalle = DetalleOrden::query()->with('orden')->lockForUpdate()->findOrFail($detalle->id_detalle_orden);

            if (! in_array($detalle->orden->estado, [EstadoOrden::EnPreparacion, EstadoOrden::Lista], true)) {
                throw new ReglaNegocioException('La orden ya no está en preparación.');
            }

            if ($detalle->estado_preparacion === $destino) {
                return $detalle;
            }

            if (! $detalle->estado_preparacion->puedePasarA($destino)) {
                throw new ReglaNegocioException(
                    "No se puede pasar de «{$detalle->estado_preparacion->etiqueta()}» a «{$destino->etiqueta()}»."
                );
            }

            $detalle->estado_preparacion = $destino;
            $detalle->save();

            return $detalle;
        });
    }
}
