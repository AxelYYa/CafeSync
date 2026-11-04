<?php

namespace Database\Factories;

use App\Models\PlantillaTarea;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantillaTarea>
 */
class PlantillaTareaFactory extends Factory
{
    protected $model = PlantillaTarea::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_usuario_creador' => Usuario::factory()->gerente(),
            'titulo' => fake()->sentence(3),
            'descripcion' => fake()->sentence(),
            'activa' => true,
        ];
    }
}
