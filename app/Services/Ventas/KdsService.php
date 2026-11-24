<?php

namespace App\Services\Ventas;

use App\Enums\EstadoOrden;
use App\Models\Estacion;
use App\Models\Orden;
use Illuminate\Database\Eloquent\Collection;

/**
 * Consulta de lo que debe mostrar cada pantalla KDS: sólo los renglones de su estación,
 * conservando la referencia a la orden principal.
 */
class KdsService
{
    /**
     * @return Collection<int, Orden>
     */
    public function ordenesDeEstacion(Estacion $estacion): Collection
    {
        return Orden::query()
            ->whereIn('estado', [EstadoOrden::Capturada->value, EstadoOrden::EnPreparacion->value, EstadoOrden::Lista->value])
            ->whereHas('detalles', fn ($consulta) => $consulta->where('id_estacion', $estacion->id_estacion))
            ->with([
                'detalles' => fn ($consulta) => $consulta
                    ->where('id_estacion', $estacion->id_estacion)
                    ->orderBy('id_detalle_orden'),
                'detalles.producto:id_producto,nombre',
            ])
            ->orderBy('created_at')
            ->orderBy('id_orden')
            ->get();
    }

    /**
     * Huella del contenido mostrado: cambia cuando entra una orden o cambia el estado de un renglón.
     *
     * @param  Collection<int, Orden>  $ordenes
     */
    public function version(Collection $ordenes): string
    {
        $firma = $ordenes->map(fn (Orden $orden) => [
            $orden->id_orden,
            $orden->estado->value,
            $orden->detalles->map(fn ($detalle) => [$detalle->id_detalle_orden, $detalle->estado_preparacion->value])->all(),
        ])->all();

        return md5(json_encode($firma, JSON_THROW_ON_ERROR));
    }
}
