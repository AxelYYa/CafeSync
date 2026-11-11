<?php

namespace App\Observers;

use App\Models\HistorialEstadoOrden;
use App\Models\Orden;
use Illuminate\Support\Facades\Auth;

/**
 * Patrón Observer: cada cambio de estado de una orden deja su rastro en `historial_estados_orden`,
 * sin que los servicios o controladores tengan que acordarse de registrarlo.
 */
class OrdenObserver
{
    public function created(Orden $orden): void
    {
        $this->registrar($orden, null);
    }

    public function updated(Orden $orden): void
    {
        if ($orden->isDirty('estado')) {
            $this->registrar($orden, $orden->getOriginal('estado'));
        }
    }

    private function registrar(Orden $orden, mixed $estadoAnterior): void
    {
        HistorialEstadoOrden::create([
            'id_orden' => $orden->id_orden,
            'id_usuario' => Auth::id(),
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $orden->estado,
            'fecha_cambio' => now(),
        ]);
    }
}
