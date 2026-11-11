<?php

use App\Enums\EstadoOrden;
use App\Models\Estacion;
use App\Models\Ingrediente;
use App\Services\Ventas\KdsService;

beforeEach(function () {
    $this->barra = Estacion::factory()->create(['nombre' => 'Barra caliente']);
    $this->cocina = Estacion::factory()->create(['nombre' => 'Cocina']);
    $insumo = Ingrediente::factory()->create(['stock_actual' => '1000.000']);
    $this->latte = productoConReceta([[$insumo, '10.000']], estacion: $this->barra);
    $this->sandwich = productoConReceta([[$insumo, '10.000']], estacion: $this->cocina);
});

it('shows each station only the lines it must prepare, under the same order', function () {
    $orden = ordenLista([[$this->latte, 1], [$this->sandwich, 1]]);
    $kds = app(KdsService::class);

    $enBarra = $kds->ordenesDeEstacion($this->barra);
    $enCocina = $kds->ordenesDeEstacion($this->cocina);

    expect($enBarra->pluck('id_orden')->all())->toBe([$orden->id_orden]);
    expect($enBarra->first()->detalles->pluck('id_producto')->all())->toBe([$this->latte->id_producto]);
    expect($enCocina->pluck('id_orden')->all())->toBe([$orden->id_orden]);
    expect($enCocina->first()->detalles->pluck('id_producto')->all())->toBe([$this->sandwich->id_producto]);
});

it('hides closed and cancelled orders from the station', function (EstadoOrden $estado) {
    $orden = ordenLista([[$this->latte, 1]]);
    $orden->forceFill(['estado' => $estado])->save();

    expect(app(KdsService::class)->ordenesDeEstacion($this->barra))->toBeEmpty();
})->with([
    'cerrada' => [EstadoOrden::Cerrada],
    'cancelada' => [EstadoOrden::Cancelada],
    'entregada' => [EstadoOrden::Entregada],
]);

it('changes the screen version only when the displayed content changes', function () {
    $kds = app(KdsService::class);
    $vacia = $kds->version($kds->ordenesDeEstacion($this->barra));

    $orden = ordenLista([[$this->latte, 1]]);
    $conOrden = $kds->version($kds->ordenesDeEstacion($this->barra));
    $sinCambios = $kds->version($kds->ordenesDeEstacion($this->barra));

    expect($conOrden)->not->toBe($vacia)
        ->and($sinCambios)->toBe($conOrden);
});
