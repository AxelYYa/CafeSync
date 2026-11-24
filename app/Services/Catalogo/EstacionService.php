<?php

namespace App\Services\Catalogo;

use App\Enums\EstadoOrden;
use App\Enums\EstadoPreparacion;
use App\Exceptions\ReglaNegocioException;
use App\Models\DetalleOrden;
use App\Models\Estacion;
use Illuminate\Database\Eloquent\Builder;

/**
 * Administración de las estaciones (zonas de preparación) a las que se enrutan los productos del KDS.
 * Las estaciones no se borran: se desactivan para conservar el historial de los renglones vendidos.
 */
class EstacionService
{
    /**
     * @param  array{nombre: string, descripcion: ?string, activa: bool}  $datos
     */
    public function crear(array $datos): Estacion
    {
        return Estacion::create([
            'nombre' => trim($datos['nombre']),
            'descripcion' => $datos['descripcion'],
            'activa' => $datos['activa'],
        ]);
    }

    /**
     * @param  array{nombre: string, descripcion: ?string, activa: bool}  $datos
     *
     * @throws ReglaNegocioException si se intenta desactivar una estación con productos por preparar.
     */
    public function actualizar(Estacion $estacion, array $datos): Estacion
    {
        if ($estacion->activa && ! $datos['activa'] && ($pendientes = $this->renglonesPendientes($estacion)) > 0) {
            throw new ReglaNegocioException(
                "{$estacion->nombre} tiene {$pendientes} pendiente(s) en su pantalla KDS. Termínalos o cancela sus órdenes antes de desactivarla."
            );
        }

        $estacion->update([
            'nombre' => trim($datos['nombre']),
            'descripcion' => $datos['descripcion'],
            'activa' => $datos['activa'],
        ]);

        return $estacion;
    }

    /**
     * Renglones de la estación que todavía no están listos dentro de órdenes en preparación.
     */
    public function renglonesPendientes(Estacion $estacion): int
    {
        return $this->filtrarPendientes(DetalleOrden::query()->where('id_estacion', $estacion->id_estacion))->count();
    }

    /**
     * Restringe una consulta de renglones a los que siguen por preparar; se reutiliza en conteos agregados.
     *
     * @param  Builder<DetalleOrden>  $renglones
     * @return Builder<DetalleOrden>
     */
    public function filtrarPendientes(Builder $renglones): Builder
    {
        return $renglones
            ->where('estado_preparacion', '!=', EstadoPreparacion::Lista->value)
            ->whereHas('orden', fn ($ordenes) => $ordenes->whereIn('estado', [EstadoOrden::Capturada->value, EstadoOrden::EnPreparacion->value]));
    }
}
