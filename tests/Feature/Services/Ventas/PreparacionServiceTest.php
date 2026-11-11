<?php

use App\Enums\CanalOrigen;
use App\Enums\EstadoOrden;
use App\Enums\EstadoPreparacion;
use App\Exceptions\ReglaNegocioException;
use App\Models\Estacion;
use App\Models\Ingrediente;
use App\Models\Usuario;
use App\Services\Ventas\OrdenService;
use App\Services\Ventas\PreparacionService;

beforeEach(function () {
    $insumo = Ingrediente::factory()->create(['stock_actual' => '1000.000']);
    $bebida = productoConReceta([[$insumo, '10.000']], estacion: Estacion::factory()->create());
    $alimento = productoConReceta([[$insumo, '10.000']], estacion: Estacion::factory()->create());

    $this->orden = app(OrdenService::class)->crear(Usuario::factory()->create(), CanalOrigen::DriveThru, [
        ['id_producto' => $bebida->id_producto, 'cantidad' => 1],
        ['id_producto' => $alimento->id_producto, 'cantidad' => 1],
    ]);
    [$this->primero, $this->segundo] = $this->orden->detalles->all();
});

it('persists each forward step of a line', function () {
    $servicio = app(PreparacionService::class);

    $servicio->avanzar($this->primero, EstadoPreparacion::EnPreparacion);
    $servicio->avanzar($this->primero, EstadoPreparacion::Lista);

    expect($this->primero->fresh()->estado_preparacion)->toBe(EstadoPreparacion::Lista);
});

it('rejects skipping from pending straight to ready', function () {
    expect(fn () => app(PreparacionService::class)->avanzar($this->primero, EstadoPreparacion::Lista))
        ->toThrow(ReglaNegocioException::class, 'No se puede pasar de «Pendiente» a «Lista».');

    expect($this->primero->fresh()->estado_preparacion)->toBe(EstadoPreparacion::Pendiente);
});

it('marks the order ready only when every station has finished its lines', function () {
    $servicio = app(PreparacionService::class);

    foreach ([EstadoPreparacion::EnPreparacion, EstadoPreparacion::Lista] as $estado) {
        $servicio->avanzar($this->primero, $estado);
    }
    $despuesDeUnaEstacion = $this->orden->fresh()->estado;

    foreach ([EstadoPreparacion::EnPreparacion, EstadoPreparacion::Lista] as $estado) {
        $servicio->avanzar($this->segundo, $estado);
    }

    expect($despuesDeUnaEstacion)->toBe(EstadoOrden::EnPreparacion);
    expect($this->orden->fresh())
        ->estado->toBe(EstadoOrden::Lista)
        ->canal_origen->toBe(CanalOrigen::DriveThru);
    expect($this->orden->historial()->latest('id_historial')->first()->estado_nuevo)->toBe(EstadoOrden::Lista);
});

it('rejects changes on a cancelled order', function () {
    $this->orden->forceFill(['estado' => EstadoOrden::Cancelada])->save();

    expect(fn () => app(PreparacionService::class)->avanzar($this->primero, EstadoPreparacion::EnPreparacion))
        ->toThrow(ReglaNegocioException::class, 'La orden ya no está en preparación.');
});
