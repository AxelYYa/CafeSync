<?php

namespace Database\Seeders;

use App\Models\PlantillaTarea;
use App\Models\Turno;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class TurnosYTareasSeeder extends Seeder
{
    public function run(): void
    {
        Turno::updateOrCreate(['nombre' => 'Matutino'], ['hora_inicio' => '07:00:00', 'hora_fin' => '15:00:00', 'activo' => true]);
        Turno::updateOrCreate(['nombre' => 'Vespertino'], ['hora_inicio' => '15:00:00', 'hora_fin' => '23:00:00', 'activo' => true]);

        $gerente = Usuario::where('username', 'gerente')->firstOrFail();

        $plantillas = [
            ['Limpiar máquina de espresso', 'Retrolavar los grupos, purgar el vapor y limpiar los portafiltros.'],
            ['Reabastecer hielo', 'Llenar la hielera de la barra fría y verificar limpieza.'],
            ['Limpiar vitrina de panadería', 'Retirar migajas, sanitizar vidrios y reacomodar productos.'],
            ['Sanitizar superficies de cocina', 'Aplicar sanitizante en planchas, tablas y mesas de trabajo.'],
        ];

        foreach ($plantillas as [$titulo, $descripcion]) {
            PlantillaTarea::updateOrCreate(
                ['titulo' => $titulo],
                ['descripcion' => $descripcion, 'activa' => true, 'id_usuario_creador' => $gerente->id_usuario]
            );
        }
    }
}
