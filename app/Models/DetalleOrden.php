<?php

namespace App\Models;

use App\Enums\EstadoPreparacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetalleOrden extends Model
{
    use HasFactory;

    protected $table = 'detalle_ordenes';

    protected $primaryKey = 'id_detalle_orden';

    protected $fillable = [
        'id_orden',
        'id_producto',
        'id_estacion',
        'id_receta_aplicada',
        'nombre_producto_snapshot',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'estado_preparacion',
    ];

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'estado_preparacion' => EstadoPreparacion::class,
        ];
    }

    /**
     * @return BelongsTo<Orden, $this>
     */
    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class, 'id_orden', 'id_orden');
    }

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    /**
     * @return BelongsTo<Estacion, $this>
     */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'id_estacion', 'id_estacion');
    }

    /**
     * @return BelongsTo<Receta, $this>
     */
    public function recetaAplicada(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'id_receta_aplicada', 'id_receta');
    }

    /**
     * @return HasMany<MovimientoInventario, $this>
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_detalle_orden', 'id_detalle_orden');
    }
}
