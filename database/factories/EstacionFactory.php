<?php

namespace Database\Factories;

use App\Models\Estacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estacion>
 */
class EstacionFactory extends Factory
{
    protected $model = Estacion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(2, true),
            'descripcion' => fake()->sentence(),
            'activa' => true,
        ];
    }
}
