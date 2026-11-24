<?php

namespace App\Services\Inventario;

use App\Models\Producto;
use Illuminate\Support\Collection;

/**
 * Calcula cuántas unidades de cada producto se pueden preparar con las existencias actuales.
 */
class DisponibilidadService
{
    /**
     * @return Collection<int, array{producto: Producto, tiene_receta: bool, maximo: ?int}>
     *                                                                                      Indexado por id de producto; `maximo` es null cuando no hay receta.
     */
    public function porProducto(): Collection
    {
        return Producto::query()
            ->vendibles()
            ->with(['estacion', 'recetaActiva.detalles.ingrediente'])
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(function (Producto $producto): array {
                $receta = $producto->recetaActiva;
                $tieneReceta = $receta !== null && $receta->detalles->isNotEmpty();

                return [$producto->id_producto => [
                    'producto' => $producto,
                    'tiene_receta' => $tieneReceta,
                    'maximo' => $tieneReceta ? $this->maximoPreparable($producto) : null,
                ]];
            });
    }

    private function maximoPreparable(Producto $producto): int
    {
        $maximo = null;

        foreach ($producto->recetaActiva->detalles as $componente) {
            $ingrediente = $componente->ingrediente;

            if (! $ingrediente->activo || bccomp((string) $componente->cantidad, '0', 3) <= 0) {
                return 0;
            }

            $alcanza = (int) floor((float) bcdiv((string) $ingrediente->stock_actual, (string) $componente->cantidad, 6));
            $maximo = $maximo === null ? $alcanza : min($maximo, $alcanza);
        }

        return $maximo ?? 0;
    }
}
