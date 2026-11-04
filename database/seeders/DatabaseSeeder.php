<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Datos base ficticios para demostrar el flujo completo. No ejecutar en producción.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UsuariosSeeder::class,
            CatalogoSeeder::class,
            TurnosYTareasSeeder::class,
        ]);
    }
}
