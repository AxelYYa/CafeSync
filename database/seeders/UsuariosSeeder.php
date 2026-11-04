<?php

namespace Database\Seeders;

use App\Enums\NombreRol;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\PinService;
use Illuminate\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    public function run(PinService $pines): void
    {
        $contrasena = (string) env('SEED_DEMO_PASSWORD', 'CafeSync#2026');

        $usuarios = [
            ['gerente', 'Gabriela Gerente (demo)', NombreRol::Gerente, '9999'],
            ['cajero', 'Carlos Cajero (demo)', NombreRol::Empleado, '1111'],
            ['barista', 'Brenda Barista (demo)', NombreRol::Empleado, '2222'],
            ['cocina', 'Cristian Cocina (demo)', NombreRol::Empleado, '3333'],
        ];

        foreach ($usuarios as [$username, $nombre, $rol, $pin]) {
            $usuario = Usuario::updateOrCreate(
                ['username' => $username],
                [
                    'id_rol' => Rol::where('nombre', $rol->value)->value('id_rol'),
                    'nombre' => $nombre,
                    'correo' => "{$username}@cafesync.test",
                    'password' => $contrasena,
                    'activo' => true,
                ]
            );

            $pines->asignar($usuario, $pin);
        }
    }
}
