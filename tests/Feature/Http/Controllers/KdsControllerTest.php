<?php

use App\Enums\EstadoPreparacion;
use App\Models\Estacion;
use App\Models\Ingrediente;
use App\Models\Usuario;

beforeEach(function () {
    $this->barra = Estacion::factory()->create(['nombre' => 'Barra caliente']);
    $this->cocina = Estacion::factory()->create(['nombre' => 'Cocina']);
    $insumo = Ingrediente::factory()->create(['stock_actual' => '1000.000']);
    $this->latte = productoConReceta([[$insumo, '10.000']], estacion: $this->barra);
    $this->sandwich = productoConReceta([[$insumo, '10.000']], estacion: $this->cocina);
    $this->orden = ordenLista([[$this->latte, 2], [$this->sandwich, 1]]);
    $this->orden->forceFill(['estado' => 'en_preparacion'])->save();
    $this->empleado = Usuario::factory()->empleado()->create();
});

it('lists the active stations with their pending lines', function () {
    Estacion::factory()->create(['nombre' => 'Estación cerrada', 'activa' => false]);

    $response = $this->actingAs($this->empleado)->get(route('kds.index'));

    $response->assertOk()
        ->assertSeeInOrder(['1 por preparar', 'Barra caliente', '1 por preparar', 'Cocina'])
        ->assertDontSee('Estación cerrada');
});

it('renders only the lines of the station with the order channel', function () {
    $response = $this->actingAs($this->empleado)->get(route('kds.show', $this->barra));

    $response->assertOk()
        ->assertSee($this->latte->nombre)
        ->assertDontSee($this->sandwich->nombre)
        ->assertSee('Mostrador')
        ->assertSee('Iniciar');
});

it('sends new HTML only when the station content changed', function () {
    $this->actingAs($this->empleado);

    $primera = $this->getJson(route('kds.datos', $this->barra));
    $sinCambios = $this->getJson(route('kds.datos', [$this->barra, 'version' => $primera->json('version')]));

    $primera->assertOk()->assertJson(['cambio' => true, 'total' => 1]);
    expect($primera->json('html'))->toContain($this->latte->nombre)->not->toContain($this->sandwich->nombre);
    $sinCambios->assertOk()->assertExactJson(['version' => $primera->json('version'), 'cambio' => false]);
});

it('advances a line from the screen and answers in JSON', function () {
    $detalle = $this->orden->detalles()->where('id_estacion', $this->barra->id_estacion)->sole();

    $response = $this->actingAs($this->empleado)->patchJson(route('kds.estado', $detalle), ['estado' => 'en_preparacion']);

    $response->assertOk()->assertJson(['id_detalle_orden' => $detalle->id_detalle_orden, 'estado' => 'en_preparacion']);
    expect($detalle->fresh()->estado_preparacion)->toBe(EstadoPreparacion::EnPreparacion);
});

it('returns 422 with a readable message for an invalid step', function () {
    $detalle = $this->orden->detalles()->where('id_estacion', $this->barra->id_estacion)->sole();

    $this->actingAs($this->empleado)->patchJson(route('kds.estado', $detalle), ['estado' => 'lista'])
        ->assertUnprocessable()
        ->assertJson(['message' => 'No se puede pasar de «Pendiente» a «Lista».']);
});

it('redirects guests away from the station screen', function () {
    $this->get(route('kds.show', $this->barra))->assertRedirect(route('login'));
});
