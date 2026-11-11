<?php

namespace App\Services\Ventas;

use App\Enums\CanalOrigen;
use App\Enums\EstadoOrden;
use App\Enums\EstadoPreparacion;
use App\Exceptions\ReglaNegocioException;
use App\Models\DetalleOrden;
use App\Models\Orden;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\Inventario\RequerimientosService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrdenService
{
    public function __construct(private readonly RequerimientosService $requerimientos) {}

    /**
     * Guarda la orden con sus renglones y la envía a preparación en una sola transacción.
     * Precios y estaciones se toman siempre del catálogo del servidor, nunca de la petición.
     *
     * @param  list<array{id_producto: int, cantidad: int}>  $items
     *
     * @throws ReglaNegocioException
     */
    public function crear(Usuario $cajero, CanalOrigen $canal, array $items, ?string $nota = null): Orden
    {
        $cantidades = $this->consolidar($items);

        if ($cantidades === []) {
            throw new ReglaNegocioException('Agrega al menos un producto a la orden.');
        }

        $productos = Producto::query()
            ->with(['estacion', 'recetaActiva.detalles'])
            ->whereIn('id_producto', array_keys($cantidades))
            ->get()
            ->keyBy('id_producto');

        $this->validarProductos($productos, $cantidades);

        $lineas = [];
        foreach ($cantidades as $idProducto => $cantidad) {
            $lineas[] = ['receta' => $productos[$idProducto]->recetaActiva, 'cantidad' => $cantidad];
        }
        $this->requerimientos->validarExistencias($this->requerimientos->calcular($lineas));

        return DB::transaction(function () use ($cajero, $canal, $cantidades, $productos, $nota): Orden {
            $orden = Orden::create([
                'folio' => 'TMP-'.Str::uuid(),
                'id_usuario' => $cajero->id_usuario,
                'canal_origen' => $canal,
                'estado' => EstadoOrden::Capturada,
                'total' => '0.00',
                'nota' => $nota,
            ]);

            $total = '0.00';
            foreach ($cantidades as $idProducto => $cantidad) {
                $producto = $productos[$idProducto];
                $subtotal = bcmul((string) $producto->precio, (string) $cantidad, 2);
                $total = bcadd($total, $subtotal, 2);

                DetalleOrden::create([
                    'id_orden' => $orden->id_orden,
                    'id_producto' => $producto->id_producto,
                    'id_estacion' => $producto->id_estacion,
                    'nombre_producto_snapshot' => $producto->nombre,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                    'estado_preparacion' => EstadoPreparacion::Pendiente,
                ]);
            }

            $orden->forceFill([
                'folio' => sprintf('CS-%s-%05d', now()->format('Ymd'), $orden->id_orden),
                'total' => $total,
            ])->save();

            $orden->estado = EstadoOrden::EnPreparacion;
            $orden->save();

            return $orden->load('detalles');
        });
    }

    /**
     * Marca como entregada una orden lista. Es un paso previo al cierre de la venta.
     *
     * @throws ReglaNegocioException
     */
    public function entregar(Orden $orden): Orden
    {
        return DB::transaction(function () use ($orden): Orden {
            $orden = Orden::query()->lockForUpdate()->findOrFail($orden->id_orden);

            if ($orden->estado !== EstadoOrden::Lista) {
                throw new ReglaNegocioException('Solo se pueden entregar órdenes que ya están listas.');
            }

            $orden->update(['estado' => EstadoOrden::Entregada]);

            return $orden;
        });
    }

    /**
     * Cancela una orden que todavía no se cierra. No toca el inventario porque aún no se descuenta.
     *
     * @throws ReglaNegocioException
     */
    public function cancelar(Orden $orden, ?string $motivo = null): Orden
    {
        return DB::transaction(function () use ($orden, $motivo): Orden {
            $orden = Orden::query()->lockForUpdate()->findOrFail($orden->id_orden);

            if (! in_array($orden->estado, EstadoOrden::activos(), true)) {
                throw new ReglaNegocioException('La orden ya está cerrada o cancelada; una venta cerrada requiere una reversión autorizada por el gerente.');
            }

            if ($motivo !== null) {
                $orden->nota = trim(($orden->nota ? $orden->nota."\n" : '').'Cancelación: '.$motivo);
            }

            $orden->estado = EstadoOrden::Cancelada;
            $orden->save();

            return $orden;
        });
    }

    /**
     * @param  list<array{id_producto: int, cantidad: int}>  $items
     * @return array<int, int> Cantidad total por producto, sumando renglones repetidos.
     */
    private function consolidar(array $items): array
    {
        $cantidades = [];

        foreach ($items as $item) {
            $idProducto = (int) $item['id_producto'];
            $cantidad = (int) $item['cantidad'];

            if ($cantidad < 1) {
                throw new ReglaNegocioException('Las cantidades deben ser mayores que cero.');
            }

            $cantidades[$idProducto] = ($cantidades[$idProducto] ?? 0) + $cantidad;
        }

        return $cantidades;
    }

    /**
     * @param  Collection<int, Producto>  $productos
     * @param  array<int, int>  $cantidades
     */
    private function validarProductos(Collection $productos, array $cantidades): void
    {
        foreach (array_keys($cantidades) as $idProducto) {
            $producto = $productos->get($idProducto);

            if ($producto === null || ! $producto->activo) {
                throw new ReglaNegocioException('Uno de los productos ya no está disponible en el catálogo.');
            }

            if (! $producto->estacion->activa) {
                throw new ReglaNegocioException("{$producto->nombre} no tiene una estación de preparación activa. Avisa al gerente.");
            }

            if ($producto->recetaActiva === null || $producto->recetaActiva->detalles->isEmpty()) {
                throw new ReglaNegocioException("{$producto->nombre} no tiene receta configurada. Avisa al gerente.");
            }
        }
    }
}
