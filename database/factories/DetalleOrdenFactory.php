<?php

namespace Database\Factories;

use App\Enums\EstadoPreparacion;
use App\Models\DetalleOrden;
use App\Models\Orden;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleOrden>
 */
class DetalleOrdenFactory extends Factory
{
    protected $model = DetalleOrden::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $producto = Producto::factory()->create();

        return [
            'id_orden' => Orden::factory(),
            'id_producto' => $producto->id_producto,
            'id_estacion' => $producto->id_estacion,
            'nombre_producto_snapshot' => $producto->nombre,
            'cantidad' => 1,
            'precio_unitario' => $producto->precio,
            'subtotal' => $producto->precio,
            'estado_preparacion' => EstadoPreparacion::Pendiente,
        ];
    }
}
