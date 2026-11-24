<?php

use App\Enums\EstadoOrden;
use App\Enums\TipoMovimiento;
use App\Exceptions\ReglaNegocioException;
use App\Exceptions\StockInsuficienteException;
use App\Models\Ingrediente;
use App\Models\MovimientoInventario;
use App\Models\Usuario;
use App\Services\Ventas\CierreVentaService;

beforeEach(function () {
    $this->cafe = Ingrediente::factory()->create(['nombre' => 'Café', 'unidad_medida' => 'g', 'stock_actual' => '1000.000']);
    $this->leche = Ingrediente::factory()->create(['nombre' => 'Leche', 'unidad_medida' => 'ml', 'stock_actual' => '5000.000']);
    $this->latte = productoConReceta([[$this->cafe, '18.000'], [$this->leche, '200.000']], '55.00');
    $this->cajero = Usuario::factory()->create();
});

it('discounts recipe quantity times units sold when closing a sale', function () {
    $orden = ordenLista([[$this->latte, 3]], $this->cajero);

    $cerrada = app(CierreVentaService::class)->cerrar($orden, $this->cajero, 'efectivo');

    expect($cerrada->estado)->toBe(EstadoOrden::Cerrada)
        ->and($cerrada->cerrada_en)->not->toBeNull()
        ->and($cerrada->metodo_pago_registrado)->toBe('efectivo');
    expect($this->cafe->fresh()->stock_actual)->toBe('946.000');
    expect($this->leche->fresh()->stock_actual)->toBe('4400.000');
    expect(MovimientoInventario::where('tipo', TipoMovimiento::Consumo->value)->count())->toBe(2);
    expect($orden->detalles()->first()->id_receta_aplicada)->toBe($this->latte->recetaActiva->id_receta);
});

it('does not discount inventory twice when the close is repeated', function () {
    $orden = ordenLista([[$this->latte, 2]], $this->cajero);
    $servicio = app(CierreVentaService::class);

    $servicio->cerrar($orden, $this->cajero);
    $servicio->cerrar($orden, $this->cajero);

    expect($this->cafe->fresh()->stock_actual)->toBe('964.000');
    expect(MovimientoInventario::count())->toBe(2);
});

it('rolls back every movement when an ingredient runs out at closing time', function () {
    $croissant = Ingrediente::factory()->create(['unidad_medida' => 'unidad', 'stock_actual' => '5.000']);
    $pan = productoConReceta([[$croissant, '1.000']]);
    $orden = ordenLista([[$this->latte, 1], [$pan, 2]], $this->cajero);
    $croissant->forceFill(['stock_actual' => '1.000'])->save();

    expect(fn () => app(CierreVentaService::class)->cerrar($orden, $this->cajero))
        ->toThrow(StockInsuficienteException::class);

    expect($orden->fresh()->estado)->toBe(EstadoOrden::Lista);
    expect($this->cafe->fresh()->stock_actual)->toBe('1000.000');
    expect(MovimientoInventario::count())->toBe(0);
});

it('rejects closing an order that is still being prepared', function () {
    $orden = ordenLista([[$this->latte, 1]], $this->cajero);
    $orden->forceFill(['estado' => EstadoOrden::EnPreparacion])->save();

    expect(fn () => app(CierreVentaService::class)->cerrar($orden, $this->cajero))
        ->toThrow(ReglaNegocioException::class, 'Solo se puede cerrar una orden lista o entregada.');

    expect(MovimientoInventario::count())->toBe(0);
});

it('returns the consumed ingredients when a manager reverts a closed sale', function () {
    $gerente = Usuario::factory()->gerente()->create();
    $orden = ordenLista([[$this->latte, 2]], $this->cajero);
    $servicio = app(CierreVentaService::class);
    $servicio->cerrar($orden, $this->cajero);

    $revertida = $servicio->revertir($orden, $gerente, 'Cliente devolvió la bebida');
    $servicio->revertir($orden, $gerente, 'Repetición accidental');

    expect($revertida->estado)->toBe(EstadoOrden::Cancelada);
    expect($this->cafe->fresh()->stock_actual)->toBe('1000.000');
    expect(MovimientoInventario::where('tipo', TipoMovimiento::Reversion->value)->count())->toBe(2);
});

it('forbids an employee from reverting a closed sale', function () {
    $orden = ordenLista([[$this->latte, 1]], $this->cajero);
    app(CierreVentaService::class)->cerrar($orden, $this->cajero);

    expect(fn () => app(CierreVentaService::class)->revertir($orden, $this->cajero, 'Sin permiso'))
        ->toThrow(ReglaNegocioException::class, 'Solo el gerente puede revertir una venta cerrada.');

    expect($orden->fresh()->estado)->toBe(EstadoOrden::Cerrada);
});
