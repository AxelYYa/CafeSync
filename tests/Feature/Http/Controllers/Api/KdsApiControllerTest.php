<?php

use App\Enums\EstadoPreparacion;
use App\Models\Estacion;
use App\Models\Ingrediente;
use App\Models\Usuario;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->barra = Estacion::factory()->create();
    $insumo = Ingrediente::factory()->create(['stock_actual' => '1000.000']);
    $latte = productoConReceta([[$insumo, '10.000']], estacion: $this->barra);
    $sandwich = productoConReceta([[$insumo, '10.000']]);
    $this->orden = ordenLista([[$latte, 1], [$sandwich, 1]]);
    $this->orden->forceFill(['estado' => 'en_preparacion'])->save();
    $this->detalleBarra = $this->orden->detalles()->where('id_estacion', $this->barra->id_estacion)->sole();
    $this->pantalla = Usuario::factory()->create();
});

it('returns 401 without a token', function () {
    $this->getJson(route('api.kds.ordenes', $this->barra))->assertUnauthorized();
});

it('lists only the lines of the requested station', function () {
    Sanctum::actingAs($this->pantalla, ['kds:read']);

    $response = $this->getJson(route('api.kds.ordenes', $this->barra));

    $response->assertOk()
        ->assertJsonPath('ordenes.0.folio', $this->orden->folio)
        ->assertJsonPath('ordenes.0.canal', 'mostrador')
        ->assertJsonCount(1, 'ordenes.0.renglones')
        ->assertJsonPath('ordenes.0.renglones.0.id_detalle_orden', $this->detalleBarra->id_detalle_orden);
});

it('returns 403 when the token cannot update preparation', function () {
    Sanctum::actingAs($this->pantalla, ['kds:read']);

    $this->patchJson(route('api.kds.estado', $this->detalleBarra), ['estado' => 'en_preparacion'])->assertForbidden();

    expect($this->detalleBarra->fresh()->estado_preparacion)->toBe(EstadoPreparacion::Pendiente);
});

it('advances a line with a token that can update preparation', function () {
    Sanctum::actingAs($this->pantalla, ['kds:update']);

    $this->patchJson(route('api.kds.estado', $this->detalleBarra), ['estado' => 'en_preparacion'])
        ->assertOk()
        ->assertJson(['estado' => 'en_preparacion']);

    expect($this->detalleBarra->fresh()->estado_preparacion)->toBe(EstadoPreparacion::EnPreparacion);
});

it('returns 422 for a transition that skips a step', function () {
    Sanctum::actingAs($this->pantalla, ['kds:update']);

    $this->patchJson(route('api.kds.estado', $this->detalleBarra), ['estado' => 'lista'])->assertUnprocessable();
});

it('returns 403 when the token belongs to an inactive user', function () {
    $this->pantalla->update(['activo' => false]);
    Sanctum::actingAs($this->pantalla, ['kds:read']);

    $this->getJson(route('api.kds.ordenes', $this->barra))->assertForbidden();
});
