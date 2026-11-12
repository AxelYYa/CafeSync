<?php

use App\Exceptions\StockInsuficienteException;
use App\Models\DetalleReceta;
use App\Models\Ingrediente;
use App\Models\Receta;
use App\Services\Inventario\RequerimientosService;
use Illuminate\Database\Eloquent\Collection;

function recetaCon(array $componentes): Receta
{
    return (new Receta)->setRelation('detalles', new Collection(array_map(
        fn (array $componente) => new DetalleReceta(['id_ingrediente' => $componente[0], 'cantidad' => $componente[1]]),
        $componentes,
    )));
}

it('multiplies each recipe quantity by the units sold and adds up shared ingredients', function () {
    $latte = recetaCon([[1, '18.000'], [2, '200.000']]);
    $espresso = recetaCon([[1, '18.000']]);

    $requeridos = (new RequerimientosService)->calcular([
        ['receta' => $latte, 'cantidad' => 3],
        ['receta' => $espresso, 'cantidad' => 2],
    ]);

    expect($requeridos)->toBe([1 => '90.000', 2 => '600.000']);
});

it('keeps three decimals for fractional quantities', function () {
    $requeridos = (new RequerimientosService)->calcular([['receta' => recetaCon([[7, '0.125']]), 'cantidad' => 3]]);

    expect($requeridos)->toBe([7 => '0.375']);
});

it('accepts the sale when every ingredient has enough stock', function () {
    $ingredientes = collect([1 => new Ingrediente(['nombre' => 'Café', 'stock_actual' => '90.000', 'activo' => true])]);

    expect(fn () => (new RequerimientosService)->validarExistencias([1 => '90.000'], $ingredientes))
        ->not->toThrow(StockInsuficienteException::class);
});

it('names every ingredient that runs short', function () {
    $ingredientes = collect([
        1 => new Ingrediente(['nombre' => 'Café', 'stock_actual' => '10.000', 'activo' => true]),
        2 => new Ingrediente(['nombre' => 'Leche', 'stock_actual' => '600.000', 'activo' => false]),
    ]);

    expect(fn () => (new RequerimientosService)->validarExistencias([1 => '18.000', 2 => '200.000'], $ingredientes))
        ->toThrow(StockInsuficienteException::class, 'Falta Café, Leche. Reabastece el inventario o avisa al gerente.');
});
