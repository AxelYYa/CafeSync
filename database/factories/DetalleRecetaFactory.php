<?php

namespace Database\Factories;

use App\Models\DetalleReceta;
use App\Models\Ingrediente;
use App\Models\Receta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleReceta>
 */
class DetalleRecetaFactory extends Factory
{
    protected $model = DetalleReceta::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_receta' => Receta::factory(),
            'id_ingrediente' => Ingrediente::factory(),
            'cantidad' => 10,
        ];
    }
}
