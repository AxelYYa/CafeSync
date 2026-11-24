<?php

use App\Enums\EstadoOrden;
use App\Models\Ingrediente;
use App\Models\MovimientoInventario;
use App\Models\Usuario;
use App\Services\Ventas\CierreVentaService;

beforeEach(function () {
    $this->cajero = Usuario::factory()->empleado()->create();
    $this->gerente = Usuario::factory()->gerente()->create();
    $this->cafe = Ingrediente::factory()->create(['stock_actual' => '1000.000']);
    $this->orden = ordenLista([[productoConReceta([[$this->cafe, '18.000']]), 2]], $this->cajero);
});

it('closes the sale once even when the button is pressed twice', function () {
    $this->actingAs($this->cajero)->post(route('ordenes.cerrar', $this->orden), ['metodo_pago_registrado' => 'tarjeta'])
        ->assertSessionHas('estado', "Venta {$this->orden->folio} cerrada. Inventario actualizado.");

    $this->post(route('ordenes.cerrar', $this->orden))
        ->assertSessionHas('estado', "La orden {$this->orden->folio} ya estaba cerrada; no se volvió a descontar inventario.");

    expect($this->cafe->fresh()->stock_actual)->toBe('964.000');
    expect(MovimientoInventario::count())->toBe(1);
});

it('marks a ready order as delivered', function () {
    $this->actingAs($this->cajero)->post(route('ordenes.entregar', $this->orden))
        ->assertSessionHas('estado', "Orden {$this->orden->folio} entregada.");

    expect($this->orden->fresh()->estado)->toBe(EstadoOrden::Entregada);
});

it('forbids an employee from reverting a closed sale', function () {
    app(CierreVentaService::class)->cerrar($this->orden, $this->cajero);

    $this->actingAs($this->cajero)->post(route('ordenes.revertir', $this->orden), ['motivo' => 'Devolución del cliente'])
        ->assertForbidden();

    expect($this->orden->fresh()->estado)->toBe(EstadoOrden::Cerrada);
    expect($this->cafe->fresh()->stock_actual)->toBe('964.000');
});

it('lets the manager revert a closed sale with a reason', function () {
    app(CierreVentaService::class)->cerrar($this->orden, $this->cajero);

    $this->actingAs($this->gerente)->post(route('ordenes.revertir', $this->orden), ['motivo' => 'Devolución del cliente'])
        ->assertSessionHas('estado', "Venta {$this->orden->folio} revertida. Los ingredientes regresaron al inventario.");

    expect($this->orden->fresh()->estado)->toBe(EstadoOrden::Cancelada);
    expect($this->cafe->fresh()->stock_actual)->toBe('1000.000');
});

it('requires a reason to revert', function () {
    app(CierreVentaService::class)->cerrar($this->orden, $this->cajero);

    $this->actingAs($this->gerente)->post(route('ordenes.revertir', $this->orden), ['motivo' => ''])
        ->assertSessionHasErrors('motivo');

    expect($this->orden->fresh()->estado)->toBe(EstadoOrden::Cerrada);
});

it('does not cancel a closed sale through the regular cancel action', function () {
    app(CierreVentaService::class)->cerrar($this->orden, $this->cajero);

    $this->actingAs($this->cajero)->post(route('ordenes.cancelar', $this->orden))->assertSessionHasErrors('orden');

    expect($this->orden->fresh()->estado)->toBe(EstadoOrden::Cerrada);
});

it('lists the orders in progress with their channel and actions', function () {
    $this->actingAs($this->cajero)->get(route('ordenes.index'))
        ->assertOk()
        ->assertSee($this->orden->folio)
        ->assertSee('Lista para entrega')
        ->assertSee('Cerrar venta');
});

it('shows the order history and the inventory movements of a closed sale', function () {
    app(CierreVentaService::class)->cerrar($this->orden, $this->cajero);

    $this->actingAs($this->gerente)->get(route('ordenes.show', $this->orden))
        ->assertOk()
        ->assertSee('Historial de estados')
        ->assertSee($this->cafe->nombre)
        ->assertSee('Consumo por venta')
        ->assertSee('Revertir venta');
});

it('does not offer the revert action to employees', function () {
    app(CierreVentaService::class)->cerrar($this->orden, $this->cajero);

    $this->actingAs($this->cajero)->get(route('ordenes.show', $this->orden))
        ->assertOk()
        ->assertDontSee('Revertir venta');
});

it('lets an employee cancel an order that is not closed yet, without touching inventory', function () {
    $this->actingAs($this->cajero)->post(route('ordenes.cancelar', $this->orden), ['motivo' => 'El cliente se retiró'])
        ->assertSessionHas('estado', "Orden {$this->orden->folio} cancelada.");

    expect($this->orden->fresh())
        ->estado->toBe(EstadoOrden::Cancelada)
        ->nota->toContain('Cancelación: El cliente se retiró');
    expect($this->cafe->fresh()->stock_actual)->toBe('1000.000');
    expect(MovimientoInventario::count())->toBe(0);
});
