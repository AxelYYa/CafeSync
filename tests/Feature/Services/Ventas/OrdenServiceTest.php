<?php

use App\Enums\CanalOrigen;
use App\Enums\EstadoOrden;
use App\Enums\EstadoPreparacion;
use App\Exceptions\ReglaNegocioException;
use App\Exceptions\StockInsuficienteException;
use App\Models\Ingrediente;
use App\Models\Orden;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\Ventas\OrdenService;

beforeEach(function () {
    $this->cajero = Usuario::factory()->create();
    $this->cafe = Ingrediente::factory()->create(['stock_actual' => '100.000']);
    $this->espresso = productoConReceta([[$this->cafe, '18.000']], '35.00');
});

it('keeps the selected channel and sends the order to preparation', function (CanalOrigen $canal) {
    $this->travelTo('2026-11-10 09:30:00');

    $orden = app(OrdenService::class)->crear($this->cajero, $canal, [
        ['id_producto' => $this->espresso->id_producto, 'cantidad' => 2],
    ]);

    expect($orden->fresh())
        ->canal_origen->toBe($canal)
        ->estado->toBe(EstadoOrden::EnPreparacion)
        ->total->toBe('70.00')
        ->folio->toMatch('/^CS-20261110-\d{5}$/');
    expect($orden->historial()->pluck('estado_nuevo')->all())
        ->toBe([EstadoOrden::Capturada, EstadoOrden::EnPreparacion]);
})->with([
    'mostrador' => [CanalOrigen::Mostrador],
    'drive-thru' => [CanalOrigen::DriveThru],
    'delivery' => [CanalOrigen::Delivery],
]);

it('stores the catalog price and station snapshot on each line', function () {
    $orden = app(OrdenService::class)->crear($this->cajero, CanalOrigen::Mostrador, [
        ['id_producto' => $this->espresso->id_producto, 'cantidad' => 1],
    ]);
    $this->espresso->update(['precio' => '99.00', 'nombre' => 'Espresso doble']);

    expect($orden->detalles()->first())
        ->precio_unitario->toBe('35.00')
        ->subtotal->toBe('35.00')
        ->nombre_producto_snapshot->not->toBe('Espresso doble')
        ->id_estacion->toBe($this->espresso->id_estacion)
        ->estado_preparacion->toBe(EstadoPreparacion::Pendiente);
});

it('merges repeated products into a single line', function () {
    $orden = app(OrdenService::class)->crear($this->cajero, CanalOrigen::Mostrador, [
        ['id_producto' => $this->espresso->id_producto, 'cantidad' => 1],
        ['id_producto' => $this->espresso->id_producto, 'cantidad' => 2],
    ]);

    expect($orden->detalles)->toHaveCount(1)
        ->and($orden->detalles->first()->cantidad)->toBe(3);
});

it('does not discount inventory when the order is created', function () {
    app(OrdenService::class)->crear($this->cajero, CanalOrigen::Mostrador, [
        ['id_producto' => $this->espresso->id_producto, 'cantidad' => 2],
    ]);

    expect($this->cafe->fresh()->stock_actual)->toBe('100.000');
});

it('rejects a product without a configured recipe', function () {
    $sinReceta = Producto::factory()->create(['nombre' => 'Galleta']);

    expect(fn () => app(OrdenService::class)->crear($this->cajero, CanalOrigen::Mostrador, [
        ['id_producto' => $sinReceta->id_producto, 'cantidad' => 1],
    ]))->toThrow(ReglaNegocioException::class, 'Galleta no tiene receta configurada. Avisa al gerente.');

    expect(Orden::count())->toBe(0);
});

it('rejects an inactive product', function () {
    $this->espresso->update(['activo' => false]);

    expect(fn () => app(OrdenService::class)->crear($this->cajero, CanalOrigen::Mostrador, [
        ['id_producto' => $this->espresso->id_producto, 'cantidad' => 1],
    ]))->toThrow(ReglaNegocioException::class, 'Uno de los productos ya no está disponible en el catálogo.');
});

it('rejects the order when the ingredients are not enough', function () {
    expect(fn () => app(OrdenService::class)->crear($this->cajero, CanalOrigen::Mostrador, [
        ['id_producto' => $this->espresso->id_producto, 'cantidad' => 6],
    ]))->toThrow(StockInsuficienteException::class);

    expect(Orden::count())->toBe(0);
});
