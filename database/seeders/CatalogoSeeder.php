<?php

namespace Database\Seeders;

use App\Models\DetalleReceta;
use App\Models\Estacion;
use App\Models\Ingrediente;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $estaciones = $this->estaciones();
            $ingredientes = $this->ingredientes();
            $gerente = Usuario::where('username', 'gerente')->value('id_usuario');

            foreach ($this->productos() as $datos) {
                $producto = Producto::updateOrCreate(
                    ['nombre' => $datos['nombre']],
                    [
                        'id_estacion' => $estaciones[$datos['estacion']]->id_estacion,
                        'descripcion' => $datos['descripcion'],
                        'precio' => $datos['precio'],
                        'activo' => true,
                    ]
                );

                $receta = Receta::updateOrCreate(
                    ['id_producto' => $producto->id_producto, 'version' => 1],
                    [
                        'instrucciones' => implode("\n", $datos['pasos']),
                        'activa' => true,
                        'id_usuario_creador' => $gerente,
                    ]
                );

                foreach ($datos['ingredientes'] as $nombre => $cantidad) {
                    DetalleReceta::updateOrCreate(
                        ['id_receta' => $receta->id_receta, 'id_ingrediente' => $ingredientes[$nombre]->id_ingrediente],
                        ['cantidad' => $cantidad]
                    );
                }
            }
        });
    }

    /**
     * @return array<string, Estacion>
     */
    private function estaciones(): array
    {
        $datos = [
            'Barra caliente' => 'Espresso y bebidas calientes.',
            'Barra fría' => 'Frappés y bebidas frías.',
            'Cocina' => 'Alimentos y panadería.',
        ];

        $estaciones = [];
        foreach ($datos as $nombre => $descripcion) {
            $estaciones[$nombre] = Estacion::updateOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion, 'activa' => true]
            );
        }

        return $estaciones;
    }

    /**
     * @return array<string, Ingrediente>
     */
    private function ingredientes(): array
    {
        $datos = [
            ['Café molido', 'g', 5000, 500],
            ['Leche entera', 'ml', 20000, 2000],
            ['Jarabe de vainilla', 'ml', 2000, 200],
            ['Hielo', 'g', 10000, 1000],
            ['Polvo de chocolate', 'g', 3000, 300],
            ['Concentrado de chai', 'ml', 3000, 300],
            ['Pan ciabatta', 'unidad', 40, 8],
            ['Jamón', 'g', 2000, 200],
            ['Queso', 'g', 2000, 200],
            ['Mantequilla', 'g', 1500, 150],
            ['Croissant', 'unidad', 30, 6],
        ];

        $ingredientes = [];
        foreach ($datos as [$nombre, $unidad, $stock, $minimo]) {
            $ingredientes[$nombre] = Ingrediente::updateOrCreate(
                ['nombre' => $nombre],
                ['unidad_medida' => $unidad, 'stock_actual' => $stock, 'stock_minimo' => $minimo, 'activo' => true]
            );
        }

        return $ingredientes;
    }

    /**
     * @return list<array{nombre: string, estacion: string, precio: string, descripcion: string, ingredientes: array<string, string>, pasos: list<string>}>
     */
    private function productos(): array
    {
        return [
            [
                'nombre' => 'Espresso',
                'estacion' => 'Barra caliente',
                'precio' => '35.00',
                'descripcion' => 'Shot de espresso sencillo.',
                'ingredientes' => ['Café molido' => '18.000'],
                'pasos' => [
                    'Moler y dosificar 18 g de café en el portafiltro.',
                    'Compactar de forma pareja.',
                    'Extraer durante 25 a 30 segundos.',
                ],
            ],
            [
                'nombre' => 'Latte',
                'estacion' => 'Barra caliente',
                'precio' => '55.00',
                'descripcion' => 'Espresso con leche vaporizada.',
                'ingredientes' => ['Café molido' => '18.000', 'Leche entera' => '200.000'],
                'pasos' => [
                    'Extraer un espresso con 18 g de café.',
                    'Vaporizar 200 ml de leche entera a 60-65 °C.',
                    'Verter la leche sobre el espresso y terminar con una capa fina de espuma.',
                ],
            ],
            [
                'nombre' => 'Capuchino',
                'estacion' => 'Barra caliente',
                'precio' => '52.00',
                'descripcion' => 'Espresso con leche y espuma abundante.',
                'ingredientes' => ['Café molido' => '18.000', 'Leche entera' => '150.000'],
                'pasos' => [
                    'Extraer un espresso con 18 g de café.',
                    'Vaporizar 150 ml de leche incorporando bastante aire.',
                    'Servir con la espuma encima.',
                ],
            ],
            [
                'nombre' => 'Mocha',
                'estacion' => 'Barra caliente',
                'precio' => '62.00',
                'descripcion' => 'Latte con chocolate.',
                'ingredientes' => ['Café molido' => '18.000', 'Leche entera' => '180.000', 'Polvo de chocolate' => '20.000'],
                'pasos' => [
                    'Colocar 20 g de polvo de chocolate en el vaso.',
                    'Extraer el espresso sobre el chocolate y mezclar.',
                    'Agregar 180 ml de leche vaporizada.',
                ],
            ],
            [
                'nombre' => 'Frappé de vainilla',
                'estacion' => 'Barra fría',
                'precio' => '68.00',
                'descripcion' => 'Bebida fría licuada con vainilla.',
                'ingredientes' => ['Café molido' => '18.000', 'Leche entera' => '150.000', 'Jarabe de vainilla' => '30.000', 'Hielo' => '150.000'],
                'pasos' => [
                    'Extraer un espresso y dejarlo enfriar un minuto.',
                    'Licuar el espresso con 150 ml de leche, 30 ml de jarabe y 150 g de hielo.',
                    'Servir en vaso frío.',
                ],
            ],
            [
                'nombre' => 'Chai frío',
                'estacion' => 'Barra fría',
                'precio' => '58.00',
                'descripcion' => 'Té chai con leche sobre hielo.',
                'ingredientes' => ['Concentrado de chai' => '120.000', 'Leche entera' => '100.000', 'Hielo' => '150.000'],
                'pasos' => [
                    'Llenar el vaso con 150 g de hielo.',
                    'Agregar 120 ml de concentrado de chai.',
                    'Completar con 100 ml de leche y mezclar.',
                ],
            ],
            [
                'nombre' => 'Sándwich de jamón y queso',
                'estacion' => 'Cocina',
                'precio' => '75.00',
                'descripcion' => 'Pan ciabatta con jamón y queso.',
                'ingredientes' => ['Pan ciabatta' => '1.000', 'Jamón' => '60.000', 'Queso' => '40.000', 'Mantequilla' => '10.000'],
                'pasos' => [
                    'Abrir el pan ciabatta y untar 10 g de mantequilla.',
                    'Colocar 60 g de jamón y 40 g de queso.',
                    'Calentar en la plancha 3 minutos por lado.',
                ],
            ],
            [
                'nombre' => 'Croissant',
                'estacion' => 'Cocina',
                'precio' => '38.00',
                'descripcion' => 'Croissant de mantequilla.',
                'ingredientes' => ['Croissant' => '1.000'],
                'pasos' => [
                    'Calentar el croissant 2 minutos a 160 °C.',
                    'Servir en plato.',
                ],
            ],
        ];
    }
}
