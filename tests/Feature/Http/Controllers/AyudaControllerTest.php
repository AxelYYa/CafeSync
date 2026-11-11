<?php

use App\Models\Ingrediente;
use App\Models\Producto;
use App\Models\Usuario;

beforeEach(function () {
    $this->empleado = Usuario::factory()->empleado()->create();
    $this->cafe = Ingrediente::factory()->create(['nombre' => 'Café molido', 'unidad_medida' => 'g']);
    $this->leche = Ingrediente::factory()->create(['nombre' => 'Leche entera', 'unidad_medida' => 'ml']);
    $this->espresso = productoConReceta([[$this->cafe, '18.000']]);
    $this->espresso->recetaActiva->update(['instrucciones' => "Moler el café.\nExtraer 25 segundos."]);
    $this->latte = productoConReceta([[$this->leche, '200.000']]);
});

it('returns only the recipe card of the selected product for the KDS overlay', function () {
    $response = $this->actingAs($this->empleado)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get(route('ayuda.show', $this->espresso));

    $response->assertOk()
        ->assertSee($this->espresso->nombre)
        ->assertSee('Café molido')
        ->assertSee('18')
        ->assertSeeInOrder(['Moler el café.', 'Extraer 25 segundos.'])
        ->assertDontSee('Leche entera')
        ->assertDontSee('<html', false);
});

it('renders the full page when opened directly', function () {
    $this->actingAs($this->empleado)->get(route('ayuda.show', $this->latte))
        ->assertOk()
        ->assertSee('Help Mode')
        ->assertSee('Leche entera')
        ->assertSee('200');
});

it('explains when a product has no recipe', function () {
    $sinReceta = Producto::factory()->create();

    $this->actingAs($this->empleado)->get(route('ayuda.show', $sinReceta))
        ->assertOk()
        ->assertSee('Este producto no tiene receta configurada');
});
