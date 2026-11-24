<?php

namespace App\Repositories;

use App\Enums\TipoMovimiento;
use App\Exceptions\ReglaNegocioException;
use App\Models\Ingrediente;
use App\Models\MovimientoInventario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Patrón Repository: concentra la persistencia de existencias y movimientos de inventario.
 * Es el único lugar donde se modifica `ingredientes.stock_actual`, siempre junto con su movimiento.
 */
class InventarioRepository
{
    /**
     * Bloquea las filas de los ingredientes (en orden de id para evitar interbloqueos) dentro de una transacción.
     *
     * @param  list<int>  $ids
     * @return Collection<int, Ingrediente>
     */
    public function bloquear(array $ids): Collection
    {
        sort($ids);

        return Ingrediente::query()
            ->whereIn('id_ingrediente', $ids)
            ->orderBy('id_ingrediente')
            ->lockForUpdate()
            ->get()
            ->keyBy('id_ingrediente');
    }

    public function existeMovimiento(int $idDetalleOrden, int $idIngrediente, TipoMovimiento $tipo): bool
    {
        return MovimientoInventario::query()
            ->where('id_detalle_orden', $idDetalleOrden)
            ->where('id_ingrediente', $idIngrediente)
            ->where('tipo', $tipo->value)
            ->exists();
    }

    /**
     * Aplica un movimiento: actualiza la existencia y deja la bitácora con el saldo resultante.
     *
     * @throws ReglaNegocioException si el saldo resultante sería negativo.
     */
    public function registrarMovimiento(
        Ingrediente $ingrediente,
        TipoMovimiento $tipo,
        bool $incrementa,
        string $cantidad,
        ?int $idUsuario = null,
        ?int $idDetalleOrden = null,
        ?string $motivo = null,
    ): MovimientoInventario {
        if (bccomp($cantidad, '0', 3) <= 0) {
            throw new ReglaNegocioException('La cantidad del movimiento debe ser mayor que cero.');
        }

        $saldo = $incrementa
            ? bcadd((string) $ingrediente->stock_actual, $cantidad, 3)
            : bcsub((string) $ingrediente->stock_actual, $cantidad, 3);

        if (bccomp($saldo, '0', 3) < 0) {
            throw new ReglaNegocioException("El saldo de {$ingrediente->nombre} no puede quedar negativo.");
        }

        $ingrediente->forceFill(['stock_actual' => $saldo])->save();

        return MovimientoInventario::create([
            'id_ingrediente' => $ingrediente->id_ingrediente,
            'id_usuario' => $idUsuario,
            'id_detalle_orden' => $idDetalleOrden,
            'tipo' => $tipo,
            'incrementa' => $incrementa,
            'cantidad' => $cantidad,
            'stock_resultante' => $saldo,
            'motivo' => $motivo,
            'fecha_movimiento' => now(),
        ]);
    }

    /**
     * @param  array{id_ingrediente?: ?int, tipo?: ?string, desde?: ?string, hasta?: ?string}  $filtros
     * @return LengthAwarePaginator<int, MovimientoInventario>
     */
    public function movimientos(array $filtros, int $porPagina = 25): LengthAwarePaginator
    {
        return MovimientoInventario::query()
            ->with(['ingrediente:id_ingrediente,nombre,unidad_medida', 'usuario:id_usuario,nombre', 'detalleOrden:id_detalle_orden,id_orden', 'detalleOrden.orden:id_orden,folio'])
            ->when($filtros['id_ingrediente'] ?? null, fn ($consulta, $id) => $consulta->where('id_ingrediente', $id))
            ->when($filtros['tipo'] ?? null, fn ($consulta, $tipo) => $consulta->where('tipo', $tipo))
            ->when($filtros['desde'] ?? null, fn ($consulta, $desde) => $consulta->where('fecha_movimiento', '>=', $desde.' 00:00:00'))
            ->when($filtros['hasta'] ?? null, fn ($consulta, $hasta) => $consulta->where('fecha_movimiento', '<=', $hasta.' 23:59:59'))
            ->orderByDesc('fecha_movimiento')
            ->orderByDesc('id_movimiento')
            ->paginate($porPagina)
            ->withQueryString();
    }

    /**
     * @return Collection<int, Ingrediente>
     */
    public function bajoMinimo(): Collection
    {
        return Ingrediente::query()->bajoMinimo()->orderBy('nombre')->get();
    }
}
