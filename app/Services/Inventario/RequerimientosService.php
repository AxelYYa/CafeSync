<?php

namespace App\Services\Inventario;

use App\Exceptions\StockInsuficienteException;
use App\Models\Ingrediente;
use App\Models\Receta;
use Illuminate\Support\Collection;

/**
 * Calcula cuánto de cada ingrediente consumen unas líneas de venta y valida que las existencias alcancen.
 * Es la única implementación de la regla "cantidad de receta × unidades vendidas".
 */
class RequerimientosService
{
    /**
     * @param  iterable<array{receta: Receta, cantidad: int}>  $lineas  Receta con `detalles` cargados.
     * @return array<int, string> Cantidad total requerida por id de ingrediente (3 decimales).
     */
    public function calcular(iterable $lineas): array
    {
        $requeridos = [];

        foreach ($lineas as $linea) {
            foreach ($linea['receta']->detalles as $componente) {
                $consumo = bcmul((string) $componente->cantidad, (string) $linea['cantidad'], 3);
                $actual = $requeridos[$componente->id_ingrediente] ?? '0.000';
                $requeridos[$componente->id_ingrediente] = bcadd($actual, $consumo, 3);
            }
        }

        return $requeridos;
    }

    /**
     * @param  array<int, string>  $requeridos
     * @param  Collection<int, Ingrediente>|null  $ingredientes  Ingredientes ya cargados (p. ej. bloqueados), por id.
     *
     * @throws StockInsuficienteException
     */
    public function validarExistencias(array $requeridos, ?Collection $ingredientes = null): void
    {
        $ingredientes ??= Ingrediente::query()->whereIn('id_ingrediente', array_keys($requeridos))->get()->keyBy('id_ingrediente');

        $faltantes = [];

        foreach ($requeridos as $idIngrediente => $cantidad) {
            $ingrediente = $ingredientes->get($idIngrediente);

            if ($ingrediente === null || ! $ingrediente->activo || bccomp((string) $ingrediente->stock_actual, $cantidad, 3) < 0) {
                $faltantes[] = $ingrediente?->nombre ?? "ingrediente #{$idIngrediente}";
            }
        }

        if ($faltantes !== []) {
            throw new StockInsuficienteException($faltantes);
        }
    }
}
