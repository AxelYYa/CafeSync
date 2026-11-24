<?php

use App\Enums\CanalOrigen;
use App\Enums\EstadoOrden;
use App\Models\DetalleReceta;
use App\Models\Estacion;
use App\Models\Ingrediente;
use App\Models\Orden;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\Usuario;
use App\Services\Ventas\OrdenService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Ayudantes para construir el catálogo mínimo de una venta de prueba.
|
*/

/**
 * Producto vendible con una receta activa.
 *
 * @param  array<int, array{0: Ingrediente, 1: string}>  $componentes  Pares [ingrediente, cantidad en su unidad base].
 */
function productoConReceta(array $componentes, string $precio = '50.00', ?Estacion $estacion = null): Producto
{
    $producto = Producto::factory()->create([
        'precio' => $precio,
        'id_estacion' => ($estacion ?? Estacion::factory()->create())->id_estacion,
    ]);

    $receta = Receta::factory()->create(['id_producto' => $producto->id_producto]);

    foreach ($componentes as [$ingrediente, $cantidad]) {
        DetalleReceta::factory()->create([
            'id_receta' => $receta->id_receta,
            'id_ingrediente' => $ingrediente->id_ingrediente,
            'cantidad' => $cantidad,
        ]);
    }

    return $producto;
}

/**
 * Crea una orden por el flujo real del POS y la deja lista para cerrarse.
 *
 * @param  array<int, array{0: Producto, 1: int}>  $lineas  Pares [producto, cantidad].
 */
function ordenLista(array $lineas, ?Usuario $cajero = null): Orden
{
    $orden = app(OrdenService::class)->crear(
        $cajero ?? Usuario::factory()->create(),
        CanalOrigen::Mostrador,
        array_map(fn (array $linea) => ['id_producto' => $linea[0]->id_producto, 'cantidad' => $linea[1]], $lineas),
    );

    $orden->forceFill(['estado' => EstadoOrden::Lista])->save();

    return $orden;
}
