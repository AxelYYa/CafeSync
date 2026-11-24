<?php

namespace App\Observers;

use App\Enums\EstadoOrden;
use App\Enums\EstadoPreparacion;
use App\Models\DetalleOrden;

/**
 * Cuando todos los renglones de una orden quedan listos en sus estaciones, la orden pasa a «lista».
 */
class DetalleOrdenObserver
{
    public function updated(DetalleOrden $detalle): void
    {
        if (! $detalle->isDirty('estado_preparacion')) {
            return;
        }

        $detalle->loadMissing('orden');
        $orden = $detalle->orden;

        if ($orden->estado !== EstadoOrden::EnPreparacion) {
            return;
        }

        $pendientes = DetalleOrden::query()
            ->where('id_orden', $orden->id_orden)
            ->where('estado_preparacion', '!=', EstadoPreparacion::Lista->value)
            ->exists();

        if (! $pendientes) {
            $orden->estado = EstadoOrden::Lista;
            $orden->save();
        }
    }
}
