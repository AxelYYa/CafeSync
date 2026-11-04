<?php

namespace Database\Factories;

use App\Models\Ingrediente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingrediente>
 */
class IngredienteFactory extends Factory
{
    protected $model = Ingrediente::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(2, true),
            'unidad_medida' => 'g',
            'stock_actual' => 1000,
            'stock_minimo' => 100,
            'activo' => true,
        ];
    }
}
