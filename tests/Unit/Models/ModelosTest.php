<?php

use App\Models\Ingrediente;
use App\Models\Receta;
use App\Models\Turno;

it('detects whether an hour falls inside a daytime shift', function (string $hora, bool $esperado) {
    $turno = new Turno(['hora_inicio' => '07:00:00', 'hora_fin' => '15:00:00']);

    expect($turno->incluyeHora($hora))->toBe($esperado);
})->with([
    'inicio exacto' => ['07:00:00', true],
    'mediodía' => ['12:30:00', true],
    'fin exacto (excluido)' => ['15:00:00', false],
    'antes del turno' => ['06:59:59', false],
]);

it('detects hours of a shift that crosses midnight', function (string $hora, bool $esperado) {
    $turno = new Turno(['hora_inicio' => '22:00:00', 'hora_fin' => '06:00:00']);

    expect($turno->incluyeHora($hora))->toBe($esperado);
})->with([
    'noche' => ['23:30:00', true],
    'madrugada' => ['03:00:00', true],
    'tarde' => ['18:00:00', false],
]);

it('splits the recipe instructions into non empty steps', function () {
    $receta = new Receta(['instrucciones' => "  Moler el café.\r\n\n  Extraer 25 segundos.  \nServir."]);

    expect($receta->pasos())->toBe(['Moler el café.', 'Extraer 25 segundos.', 'Servir.']);
});

it('returns no steps when the recipe has no instructions', function () {
    expect((new Receta)->pasos())->toBe([]);
});

it('flags an ingredient at or below its minimum stock', function (string $actual, string $minimo, bool $esperado) {
    $ingrediente = new Ingrediente(['stock_actual' => $actual, 'stock_minimo' => $minimo]);

    expect($ingrediente->estaBajoMinimo())->toBe($esperado);
})->with([
    'debajo' => ['150.000', '200.000', true],
    'igual al mínimo' => ['200.000', '200.000', true],
    'arriba' => ['200.001', '200.000', false],
    'sin mínimo definido' => ['0.000', '0.000', false],
]);
