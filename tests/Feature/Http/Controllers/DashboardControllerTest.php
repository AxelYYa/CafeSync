<?php

use App\Enums\EstadoOrden;
use App\Models\Ingrediente;
use App\Models\Orden;
use App\Models\Usuario;

beforeEach(function () {
    Ingrediente::factory()->create(['nombre' => 'Leche entera', 'stock_actual' => '100.000', 'stock_minimo' => '200.000']);
    Orden::factory()->create(['estado' => EstadoOrden::Cerrada, 'total' => '150.00', 'cerrada_en' => now()]);
});

it('shows the manager the day sales and the low stock alert', function () {
    $response = $this->actingAs(Usuario::factory()->gerente()->create())->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Ventas cerradas hoy')
        ->assertSee('$150.00')
        ->assertSee('Reabastece estos ingredientes')
        ->assertSee('Leche entera');
});

it('hides sales and inventory figures from employees', function () {
    $response = $this->actingAs(Usuario::factory()->empleado()->create())->get(route('dashboard'));

    $response->assertOk()
        ->assertDontSee('Ventas cerradas hoy')
        ->assertDontSee('Leche entera')
        ->assertDontSee('Administración');
});
