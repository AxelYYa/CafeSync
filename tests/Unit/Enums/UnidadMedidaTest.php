<?php

use App\Enums\UnidadMedida;

it('converts capture units to the base unit of their family', function (UnidadMedida $unidad, string $cantidad, string $esperado) {
    expect($unidad->convertirABase($cantidad))->toBe($esperado);
})->with([
    'kilogramos a gramos' => [UnidadMedida::Kilogramo, '1.5', '1500.000'],
    'litros a mililitros' => [UnidadMedida::Litro, '0.25', '250.000'],
    'gramos sin cambio' => [UnidadMedida::Gramo, '18', '18.000'],
    'unidades sin cambio' => [UnidadMedida::Unidad, '2', '2.000'],
    'fracción de gramo' => [UnidadMedida::Kilogramo, '0.0005', '0.500'],
]);

it('treats only units of the same family as compatible', function () {
    expect(UnidadMedida::Kilogramo->esCompatibleCon(UnidadMedida::Gramo))->toBeTrue()
        ->and(UnidadMedida::Litro->esCompatibleCon(UnidadMedida::Mililitro))->toBeTrue()
        ->and(UnidadMedida::Litro->esCompatibleCon(UnidadMedida::Gramo))->toBeFalse()
        ->and(UnidadMedida::Unidad->esCompatibleCon(UnidadMedida::Gramo))->toBeFalse();
});

it('lists the capture units allowed for a base unit', function () {
    expect(UnidadMedida::compatiblesCon(UnidadMedida::Mililitro))->toBe([UnidadMedida::Mililitro, UnidadMedida::Litro]);
});

it('only stores grams, milliliters and units as base units', function () {
    expect(UnidadMedida::valoresBase())->toBe(['g', 'ml', 'unidad']);
});

it('rejects a quantity that is not numeric', function () {
    UnidadMedida::Gramo->convertirABase('diez');
})->throws(InvalidArgumentException::class, 'La cantidad debe ser numérica.');
