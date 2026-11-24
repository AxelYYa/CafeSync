<?php

use App\Models\Estacion;
use App\Models\Ingrediente;
use App\Models\Producto;
use App\Models\Usuario;

beforeEach(function () {
    $this->gerente = Usuario::factory()->gerente()->create();
    $this->barra = Estacion::factory()->create(['nombre' => 'Barra caliente', 'descripcion' => 'Espresso y bebidas calientes.']);
});

describe('index', function () {
    it('lists the stations with their products and the lines waiting to be prepared', function () {
        $insumo = Ingrediente::factory()->create(['stock_actual' => '1000.000']);
        $latte = productoConReceta([[$insumo, '10.000']], estacion: $this->barra);
        Producto::factory()->create(['id_estacion' => $this->barra->id_estacion, 'activo' => false]);
        $orden = ordenLista([[$latte, 1]]);
        $orden->forceFill(['estado' => 'en_preparacion'])->save();

        $this->actingAs($this->gerente)->get(route('admin.estaciones.index'))
            ->assertOk()
            ->assertSeeInOrder(['Barra caliente', 'Productos activos', '1', 'de 2', 'Por preparar ahora', '1']);
    });
});

describe('store', function () {
    it('creates an active station that shows up in the KDS list', function () {
        $this->actingAs($this->gerente)->post(route('admin.estaciones.store'), ['nombre' => '  Barra fría ', 'descripcion' => 'Frappés', 'activa' => '1'])
            ->assertRedirect(route('admin.estaciones.index'))
            ->assertSessionHas('estado', 'Estación Barra fría creada.');

        $this->assertDatabaseHas('estaciones', ['nombre' => 'Barra fría', 'descripcion' => 'Frappés', 'activa' => true]);
        $this->get(route('kds.index'))->assertSee('Barra fría');
    });

    it('rejects a duplicated or too short name', function (string $nombre) {
        $this->actingAs($this->gerente)->post(route('admin.estaciones.store'), ['nombre' => $nombre, 'activa' => '1'])
            ->assertSessionHasErrors('nombre');

        expect(Estacion::count())->toBe(1);
    })->with(['duplicado' => ['Barra caliente'], 'corto' => ['Ba'], 'vacío' => ['']]);
});

describe('update', function () {
    it('renames a station and keeps the name of lines already sold', function () {
        $insumo = Ingrediente::factory()->create(['stock_actual' => '1000.000']);
        $orden = ordenLista([[productoConReceta([[$insumo, '10.000']], estacion: $this->barra), 1]]);

        $this->actingAs($this->gerente)->put(route('admin.estaciones.update', $this->barra), ['nombre' => 'Barra de espresso', 'activa' => '1'])
            ->assertSessionHasNoErrors();

        expect($this->barra->fresh()->nombre)->toBe('Barra de espresso');
        expect($orden->detalles()->first()->id_estacion)->toBe($this->barra->id_estacion);
    });

    it('takes the products of a deactivated station out of the POS', function () {
        $insumo = Ingrediente::factory()->create(['stock_actual' => '1000.000']);
        $latte = productoConReceta([[$insumo, '10.000']], estacion: $this->barra);

        $this->actingAs($this->gerente)->put(route('admin.estaciones.update', $this->barra), ['nombre' => 'Barra caliente', 'activa' => '0'])
            ->assertSessionHasNoErrors();

        expect($this->barra->fresh()->activa)->toBeFalse();
        $this->get(route('pos.index'))->assertOk()->assertDontSee($latte->nombre);
        $this->get(route('kds.index'))->assertDontSee('Barra caliente');
    });

    it('refuses to deactivate a station that still has lines to prepare', function () {
        $insumo = Ingrediente::factory()->create(['stock_actual' => '1000.000']);
        $orden = ordenLista([[productoConReceta([[$insumo, '10.000']], estacion: $this->barra), 2]]);
        $orden->forceFill(['estado' => 'en_preparacion'])->save();

        $this->actingAs($this->gerente)->put(route('admin.estaciones.update', $this->barra), ['nombre' => 'Barra caliente', 'activa' => '0'])
            ->assertSessionHasErrors(['estacion' => 'Barra caliente tiene 1 pendiente(s) en su pantalla KDS. Termínalos o cancela sus órdenes antes de desactivarla.']);

        expect($this->barra->fresh()->activa)->toBeTrue();
    });

    it('allows deactivating once the pending orders are closed or cancelled', function () {
        $insumo = Ingrediente::factory()->create(['stock_actual' => '1000.000']);
        $orden = ordenLista([[productoConReceta([[$insumo, '10.000']], estacion: $this->barra), 1]]);
        $orden->forceFill(['estado' => 'cancelada'])->save();

        $this->actingAs($this->gerente)->put(route('admin.estaciones.update', $this->barra), ['nombre' => 'Barra caliente', 'activa' => '0'])
            ->assertSessionHasNoErrors();

        expect($this->barra->fresh()->activa)->toBeFalse();
    });
});
