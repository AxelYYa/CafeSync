<?php

namespace Database\Factories;

use App\Enums\NombreRol;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\PinService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    protected static ?string $password = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_rol' => fn () => Rol::firstOrCreate(['nombre' => NombreRol::Empleado->value])->id_rol,
            'nombre' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'correo' => fake()->unique()->safeEmail(),
            'telefono' => null,
            'password' => static::$password ??= Hash::make('password'),
            'activo' => true,
            'remember_token' => null,
        ];
    }

    public function gerente(): static
    {
        return $this->state(fn () => [
            'id_rol' => Rol::firstOrCreate(['nombre' => NombreRol::Gerente->value])->id_rol,
        ]);
    }

    public function empleado(): static
    {
        return $this->state(fn () => [
            'id_rol' => Rol::firstOrCreate(['nombre' => NombreRol::Empleado->value])->id_rol,
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }

    public function conPin(string $pin = '1234'): static
    {
        return $this->state(fn () => [
            'pin_hash' => app(PinService::class)->hashear($pin),
            'pin_lookup' => app(PinService::class)->indice($pin),
        ]);
    }

    public function conPreguntaSecreta(string $pregunta = '¿Nombre de tu primera mascota?', string $respuesta = 'Firulais'): static
    {
        return $this->state(fn () => [
            'pregunta_secreta' => $pregunta,
            'respuesta_secreta_hash' => Hash::make(mb_strtolower(trim($respuesta))),
        ]);
    }
}
