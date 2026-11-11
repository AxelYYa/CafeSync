<?php

namespace App\Services\Reportes;

use App\Enums\EstadoOrden;
use App\Models\DetalleOrden;
use App\Models\Orden;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reporte operativo de ventas: sólo considera órdenes cerradas dentro del periodo.
 */
class ReporteVentasService
{
    /**
     * @return array{
     *     desde: CarbonImmutable,
     *     hasta: CarbonImmutable,
     *     total: string,
     *     ordenes: int,
     *     ticket_promedio: string,
     *     por_canal: list<array{canal: string, ordenes: int, total: string}>,
     *     por_producto: list<array{producto: string, unidades: int, total: string}>
     * }
     */
    public function resumen(?string $desde, ?string $hasta): array
    {
        [$inicio, $fin] = $this->rango($desde, $hasta);

        $base = Orden::query()
            ->where('estado', EstadoOrden::Cerrada->value)
            ->whereBetween('cerrada_en', [$inicio, $fin]);

        $total = (string) (clone $base)->sum('total');
        $cantidad = (clone $base)->count();

        $porCanal = (clone $base)
            ->select('canal_origen', DB::raw('COUNT(*) as ordenes'), DB::raw('SUM(total) as total'))
            ->groupBy('canal_origen')
            ->orderBy('canal_origen')
            ->get()
            ->map(fn ($fila) => [
                'canal' => $fila->canal_origen->etiqueta(),
                'ordenes' => (int) $fila->ordenes,
                'total' => number_format((float) $fila->total, 2, '.', ''),
            ])->all();

        $porProducto = DetalleOrden::query()
            ->join('ordenes', 'ordenes.id_orden', '=', 'detalle_ordenes.id_orden')
            ->where('ordenes.estado', EstadoOrden::Cerrada->value)
            ->whereBetween('ordenes.cerrada_en', [$inicio, $fin])
            ->select('detalle_ordenes.nombre_producto_snapshot', DB::raw('SUM(detalle_ordenes.cantidad) as unidades'), DB::raw('SUM(detalle_ordenes.subtotal) as total'))
            ->groupBy('detalle_ordenes.nombre_producto_snapshot')
            ->orderByDesc('unidades')
            ->get()
            ->map(fn ($fila) => [
                'producto' => $fila->nombre_producto_snapshot,
                'unidades' => (int) $fila->unidades,
                'total' => number_format((float) $fila->total, 2, '.', ''),
            ])->all();

        return [
            'desde' => $inicio,
            'hasta' => $fin,
            'total' => number_format((float) $total, 2, '.', ''),
            'ordenes' => $cantidad,
            'ticket_promedio' => $cantidad > 0 ? bcdiv($total, (string) $cantidad, 2) : '0.00',
            'por_canal' => $porCanal,
            'por_producto' => $porProducto,
        ];
    }

    /**
     * @return Collection<int, Orden>
     */
    public function ordenes(?string $desde, ?string $hasta): Collection
    {
        [$inicio, $fin] = $this->rango($desde, $hasta);

        return Orden::query()
            ->with('usuario:id_usuario,nombre')
            ->where('estado', EstadoOrden::Cerrada->value)
            ->whereBetween('cerrada_en', [$inicio, $fin])
            ->orderBy('cerrada_en')
            ->get();
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function rango(?string $desde, ?string $hasta): array
    {
        $inicio = $desde ? CarbonImmutable::parse($desde)->startOfDay() : CarbonImmutable::now()->startOfDay();
        $fin = $hasta ? CarbonImmutable::parse($hasta)->endOfDay() : CarbonImmutable::now()->endOfDay();

        return [$inicio, $fin];
    }
}
