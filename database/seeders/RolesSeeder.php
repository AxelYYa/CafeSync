<?php

namespace Database\Seeders;

use App\Enums\NombreRol;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Rol::updateOrCreate(
            ['nombre' => NombreRol::Gerente->value],
            ['descripcion' => 'Administra catálogo, recetas, inventario, usuarios, tareas y reportes.']
        );

        Rol::updateOrCreate(
            ['nombre' => NombreRol::Empleado->value],
            ['descripcion' => 'Opera caja, estaciones de preparación, asistencia y tareas asignadas.']
        );
    }
}
