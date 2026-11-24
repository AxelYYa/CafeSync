<?php

use App\Enums\TipoMovimiento;
use App\Exceptions\ReglaNegocioException;
use App\Models\Ingrediente;
use App\Models\MovimientoInventario;
use App\Models\Usuario;
use App\Services\Inventario\InventarioService;

beforeEach(function () {
    $this->gerente = Usuario::factory()->gerente()->create();
    $this->cafe = Ingrediente::factory()->create(['nombre' => 'Café', 'unidad_medida' => 'g', 'stock_actual' => '500.000']);
});

it('converts an entry captured in kilograms to grams', function () {
    $movimiento = app(InventarioService::class)->registrarManual(
        $this->gerente, $this->cafe, TipoMovimiento::Entrada, '1.5', 'kg', null, 'Compra semanal'
    );

    expect($this->cafe->fresh()->stock_actual)->toBe('2000.000');
    expect($movimiento)
        ->cantidad->toBe('1500.000')
        ->stock_resultante->toBe('2000.000')
        ->incrementa->toBeTrue()
        ->id_usuario->toBe($this->gerente->id_usuario);
});

it('forbids an employee from changing stock', function () {
    $empleado = Usuario::factory()->empleado()->create();

    expect(fn () => app(InventarioService::class)->registrarManual(
        $empleado, $this->cafe, TipoMovimiento::Entrada, '100', 'g', null, null
    ))->toThrow(ReglaNegocioException::class, 'Solo el gerente puede modificar existencias.');

    expect($this->cafe->fresh()->stock_actual)->toBe('500.000');
});

it('requires a reason for every adjustment', function () {
    expect(fn () => app(InventarioService::class)->registrarManual(
        $this->gerente, $this->cafe, TipoMovimiento::Ajuste, '10', 'g', false, ''
    ))->toThrow(ReglaNegocioException::class, 'Todo ajuste requiere una observación que explique el motivo.');
});

it('rejects a unit that is not compatible with the ingredient', function () {
    expect(fn () => app(InventarioService::class)->registrarManual(
        $this->gerente, $this->cafe, TipoMovimiento::Entrada, '1', 'l', null, null
    ))->toThrow(ReglaNegocioException::class, 'La unidad «l» no es compatible con Café (g).');
});

it('rejects an exit that would leave a negative balance', function () {
    expect(fn () => app(InventarioService::class)->registrarManual(
        $this->gerente, $this->cafe, TipoMovimiento::Salida, '600', 'g', null, 'Merma'
    ))->toThrow(ReglaNegocioException::class, 'El saldo de Café no puede quedar negativo.');

    expect($this->cafe->fresh()->stock_actual)->toBe('500.000');
    expect(MovimientoInventario::count())->toBe(0);
});

it('does not allow consumption movements to be recorded by hand', function () {
    expect(fn () => app(InventarioService::class)->registrarManual(
        $this->gerente, $this->cafe, TipoMovimiento::Consumo, '10', 'g', null, null
    ))->toThrow(ReglaNegocioException::class);
});
