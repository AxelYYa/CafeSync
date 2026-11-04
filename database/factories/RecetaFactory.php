<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Receta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receta>
 */
class RecetaFactory extends Factory
{
    protected $model = Receta::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_producto' => Producto::factory(),
            'version' => 1,
            'instrucciones' => "Preparar los ingredientes.\nMezclar y servir.",
            'activa' => true,
        ];
    }
}
