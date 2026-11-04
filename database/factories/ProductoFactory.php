<?php

namespace Database\Factories;

use App\Models\Estacion;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_estacion' => Estacion::factory(),
            'nombre' => fake()->unique()->words(2, true),
            'descripcion' => fake()->sentence(),
            'precio' => fake()->randomFloat(2, 20, 120),
            'activo' => true,
        ];
    }
}
