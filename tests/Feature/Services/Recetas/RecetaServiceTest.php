<?php

use App\Exceptions\ReglaNegocioException;
use App\Models\Ingrediente;
use App\Models\Receta;
use App\Models\Usuario;
use App\Services\Recetas\RecetaService;

beforeEach(function () {
    $this->gerente = Usuario::factory()->gerente()->create();
    $this->leche = Ingrediente::factory()->create(['nombre' => 'Leche', 'unidad_medida' => 'ml']);
    $this->cafe = Ingrediente::factory()->create(['nombre' => 'Café', 'unidad_medida' => 'g']);
    $this->producto = productoConReceta([[$this->cafe, '18.000']]);
});

it('saves a new active version and keeps the previous one inactive', function () {
    $anterior = $this->producto->recetaActiva;

    $nueva = app(RecetaService::class)->guardarNuevaVersion($this->producto, "Paso 1\nPaso 2", [
        ['id_ingrediente' => $this->cafe->id_ingrediente, 'cantidad' => '20', 'unidad' => 'g'],
        ['id_ingrediente' => $this->leche->id_ingrediente, 'cantidad' => '0.25', 'unidad' => 'l'],
    ], $this->gerente);

    expect($nueva)
        ->version->toBe(2)
        ->activa->toBeTrue()
        ->id_usuario_creador->toBe($this->gerente->id_usuario);
    expect($nueva->pasos())->toBe(['Paso 1', 'Paso 2']);
    expect($nueva->detalles->pluck('cantidad', 'id_ingrediente')->all())->toHaveCount(2)->toMatchArray([
        $this->cafe->id_ingrediente => '20.000',
        $this->leche->id_ingrediente => '250.000',
    ]);
    expect($anterior->fresh()->activa)->toBeFalse();
    expect(Receta::where('id_producto', $this->producto->id_producto)->where('activa', true)->count())->toBe(1);
});

it('rejects a unit that does not match the ingredient', function () {
    expect(fn () => app(RecetaService::class)->guardarNuevaVersion($this->producto, null, [
        ['id_ingrediente' => $this->cafe->id_ingrediente, 'cantidad' => '20', 'unidad' => 'ml'],
    ], $this->gerente))->toThrow(ReglaNegocioException::class, 'La unidad «ml» no es compatible con Café, que se controla en g.');

    expect($this->producto->recetaActiva()->first()->version)->toBe(1);
});

it('rejects the same ingredient twice in a recipe', function () {
    expect(fn () => app(RecetaService::class)->guardarNuevaVersion($this->producto, null, [
        ['id_ingrediente' => $this->cafe->id_ingrediente, 'cantidad' => '10', 'unidad' => 'g'],
        ['id_ingrediente' => $this->cafe->id_ingrediente, 'cantidad' => '5', 'unidad' => 'g'],
    ], $this->gerente))->toThrow(ReglaNegocioException::class, 'Café aparece más de una vez en la receta.');
});

it('rejects a zero quantity', function () {
    expect(fn () => app(RecetaService::class)->guardarNuevaVersion($this->producto, null, [
        ['id_ingrediente' => $this->cafe->id_ingrediente, 'cantidad' => '0', 'unidad' => 'g'],
    ], $this->gerente))->toThrow(ReglaNegocioException::class, 'La cantidad de Café debe ser mayor que cero.');
});
