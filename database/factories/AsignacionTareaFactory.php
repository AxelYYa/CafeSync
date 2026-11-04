<?php

namespace Database\Factories;

use App\Enums\EstadoTarea;
use App\Models\AsignacionTarea;
use App\Models\PlantillaTarea;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AsignacionTarea>
 */
class AsignacionTareaFactory extends Factory
{
    protected $model = AsignacionTarea::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_plantilla' => PlantillaTarea::factory(),
            'id_usuario_asigno' => Usuario::factory()->gerente(),
            'estado' => EstadoTarea::Pendiente,
        ];
    }
}
