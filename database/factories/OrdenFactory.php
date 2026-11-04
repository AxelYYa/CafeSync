<?php

namespace Database\Factories;

use App\Enums\CanalOrigen;
use App\Enums\EstadoOrden;
use App\Models\Orden;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Orden>
 */
class OrdenFactory extends Factory
{
    protected $model = Orden::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'folio' => 'CS-'.Str::upper(Str::random(10)),
            'id_usuario' => Usuario::factory(),
            'canal_origen' => CanalOrigen::Mostrador,
            'estado' => EstadoOrden::EnPreparacion,
            'total' => 0,
        ];
    }
}
