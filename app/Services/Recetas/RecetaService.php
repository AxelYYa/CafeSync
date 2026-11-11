<?php

namespace App\Services\Recetas;

use App\Enums\UnidadMedida;
use App\Exceptions\ReglaNegocioException;
use App\Models\DetalleReceta;
use App\Models\Ingrediente;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class RecetaService
{
    /**
     * Guarda la receta de un producto como una versión nueva y desactiva la anterior.
     * Las versiones previas se conservan porque los renglones vendidos las referencian.
     *
     * @param  list<array{id_ingrediente: int, cantidad: string, unidad: string}>  $lineas
     *
     * @throws ReglaNegocioException
     */
    public function guardarNuevaVersion(Producto $producto, ?string $instrucciones, array $lineas, Usuario $autor): Receta
    {
        if ($lineas === []) {
            throw new ReglaNegocioException('La receta debe incluir al menos un ingrediente.');
        }

        $ingredientes = Ingrediente::query()
            ->whereIn('id_ingrediente', array_column($lineas, 'id_ingrediente'))
            ->get()
            ->keyBy('id_ingrediente');

        $convertidas = $this->convertir($lineas, $ingredientes->all());

        return DB::transaction(function () use ($producto, $instrucciones, $convertidas, $autor): Receta {
            $ultima = Receta::query()->where('id_producto', $producto->id_producto)->lockForUpdate()->max('version');

            Receta::query()->where('id_producto', $producto->id_producto)->update(['activa' => false]);

            $receta = Receta::create([
                'id_producto' => $producto->id_producto,
                'version' => ((int) $ultima) + 1,
                'instrucciones' => $instrucciones,
                'activa' => true,
                'id_usuario_creador' => $autor->id_usuario,
            ]);

            foreach ($convertidas as $idIngrediente => $cantidad) {
                DetalleReceta::create([
                    'id_receta' => $receta->id_receta,
                    'id_ingrediente' => $idIngrediente,
                    'cantidad' => $cantidad,
                ]);
            }

            return $receta->load('detalles');
        });
    }

    /**
     * Convierte cada cantidad a la unidad base de su ingrediente y rechaza unidades incompatibles o duplicados.
     *
     * @param  list<array{id_ingrediente: int, cantidad: string, unidad: string}>  $lineas
     * @param  array<int, Ingrediente>  $ingredientes
     * @return array<int, string>
     *
     * @throws ReglaNegocioException
     */
    private function convertir(array $lineas, array $ingredientes): array
    {
        $convertidas = [];

        foreach ($lineas as $linea) {
            $ingrediente = $ingredientes[$linea['id_ingrediente']] ?? null;

            if ($ingrediente === null) {
                throw new ReglaNegocioException('Uno de los ingredientes seleccionados no existe.');
            }

            if (isset($convertidas[$ingrediente->id_ingrediente])) {
                throw new ReglaNegocioException("{$ingrediente->nombre} aparece más de una vez en la receta.");
            }

            $unidad = UnidadMedida::tryFrom($linea['unidad']);

            if ($unidad === null || ! $unidad->esCompatibleCon($ingrediente->unidad())) {
                throw new ReglaNegocioException(
                    "La unidad «{$linea['unidad']}» no es compatible con {$ingrediente->nombre}, que se controla en {$ingrediente->unidad()->value}."
                );
            }

            $enBase = $unidad->convertirABase($linea['cantidad']);

            if (bccomp($enBase, '0', 3) <= 0) {
                throw new ReglaNegocioException("La cantidad de {$ingrediente->nombre} debe ser mayor que cero.");
            }

            $convertidas[$ingrediente->id_ingrediente] = $enBase;
        }

        return $convertidas;
    }
}
