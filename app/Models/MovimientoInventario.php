<?php

namespace App\Models;

use App\Enums\TipoMovimiento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'movimientos_inventario';

    protected $primaryKey = 'id_movimiento';

    protected $fillable = [
        'id_ingrediente',
        'id_usuario',
        'id_detalle_orden',
        'tipo',
        'incrementa',
        'cantidad',
        'stock_resultante',
        'motivo',
        'fecha_movimiento',
    ];

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'tipo' => TipoMovimiento::class,
            'incrementa' => 'boolean',
            'cantidad' => 'decimal:3',
            'stock_resultante' => 'decimal:3',
            'fecha_movimiento' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Ingrediente, $this>
     */
    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class, 'id_ingrediente', 'id_ingrediente');
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * @return BelongsTo<DetalleOrden, $this>
     */
    public function detalleOrden(): BelongsTo
    {
        return $this->belongsTo(DetalleOrden::class, 'id_detalle_orden', 'id_detalle_orden');
    }
}
