<?php

use App\Enums\CanalOrigen;
use App\Models\Ingrediente;
use App\Models\Orden;
use App\Models\Usuario;

beforeEach(function () {
    $this->cajero = Usuario::factory()->create();
    $this->latte = productoConReceta([[Ingrediente::factory()->create(['stock_actual' => '1000.000']), '18.000']], '55.00');
});

it('saves the order with the price from the catalog, ignoring a price sent by the browser', function () {
    $response = $this->actingAs($this->cajero)->post(route('pos.ordenes.store'), [
        'canal_origen' => 'delivery',
        'items' => [['id_producto' => $this->latte->id_producto, 'cantidad' => 2, 'precio_unitario' => '1.00']],
        'total' => '2.00',
    ]);

    $orden = Orden::with('detalles')->sole();
    $response->assertRedirect(route('pos.index'))
        ->assertSessionHas('estado', "Orden {$orden->folio} enviada a preparación.");
    expect($orden)
        ->canal_origen->toBe(CanalOrigen::Delivery)
        ->total->toBe('110.00')
        ->id_usuario->toBe($this->cajero->id_usuario);
    expect($orden->detalles->sole()->precio_unitario)->toBe('55.00');
});

it('asks for at least one product', function () {
    $this->actingAs($this->cajero)->post(route('pos.ordenes.store'), ['canal_origen' => 'mostrador', 'items' => []])
        ->assertSessionHasErrors(['items' => 'Agrega al menos un producto a la orden.']);

    expect(Orden::count())->toBe(0);
});

it('rejects an unknown channel and a non positive quantity', function () {
    $this->actingAs($this->cajero)->post(route('pos.ordenes.store'), [
        'canal_origen' => 'rappi',
        'items' => [['id_producto' => $this->latte->id_producto, 'cantidad' => -1]],
    ])->assertSessionHasErrors(['canal_origen', 'items.0.cantidad']);

    expect(Orden::count())->toBe(0);
});

it('reports missing ingredients as a validation error', function () {
    $this->actingAs($this->cajero)->post(route('pos.ordenes.store'), [
        'canal_origen' => 'mostrador',
        'items' => [['id_producto' => $this->latte->id_producto, 'cantidad' => 99]],
    ])->assertSessionHasErrors('items');

    expect(Orden::count())->toBe(0);
});

it('redirects guests to the login', function () {
    $this->post(route('pos.ordenes.store'), [])->assertRedirect(route('login'));
});
