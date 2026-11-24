<?php

use App\Enums\CanalOrigen;
use App\Enums\EstadoOrden;
use App\Enums\EstadoPreparacion;

it('moves a preparation line only one step forward', function () {
    expect(EstadoPreparacion::Pendiente->siguiente())->toBe(EstadoPreparacion::EnPreparacion)
        ->and(EstadoPreparacion::EnPreparacion->siguiente())->toBe(EstadoPreparacion::Lista)
        ->and(EstadoPreparacion::Lista->siguiente())->toBeNull();
});

it('rejects skipped or backward preparation steps', function (EstadoPreparacion $desde, EstadoPreparacion $hacia) {
    expect($desde->puedePasarA($hacia))->toBeFalse();
})->with([
    'pendiente a lista' => [EstadoPreparacion::Pendiente, EstadoPreparacion::Lista],
    'lista a pendiente' => [EstadoPreparacion::Lista, EstadoPreparacion::Pendiente],
    'en preparación a pendiente' => [EstadoPreparacion::EnPreparacion, EstadoPreparacion::Pendiente],
]);

it('allows closing a sale only when the order is ready or delivered', function () {
    expect(EstadoOrden::cerrables())->toBe([EstadoOrden::Lista, EstadoOrden::Entregada]);
});

it('keeps closed and cancelled orders out of the active flow', function () {
    expect(EstadoOrden::activos())->not->toContain(EstadoOrden::Cerrada)->not->toContain(EstadoOrden::Cancelada);
});

it('defines exactly the three channels of the README', function () {
    expect(CanalOrigen::valores())->toBe(['mostrador', 'drive_thru', 'delivery'])
        ->and(CanalOrigen::DriveThru->etiqueta())->toBe('Drive-Thru');
});
