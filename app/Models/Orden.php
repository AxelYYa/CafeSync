<?php

namespace App\Models;

use App\Enums\CanalOrigen;
use App\Enums\EstadoOrden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orden extends Model
{
    use HasFactory;

    protected $table = 'ordenes';

    protected $primaryKey = 'id_orden';

    protected $fillable = [
        'folio',
        'id_usuario',
        'canal_origen',
        'estado',
        'total',
        'metodo_pago_registrado',
        'nota',
        'cerrada_en',
    ];

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'canal_origen' => CanalOrigen::class,
            'estado' => EstadoOrden::class,
            'total' => 'decimal:2',
            'cerrada_en' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * @return HasMany<DetalleOrden, $this>
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleOrden::class, 'id_orden', 'id_orden');
    }

    /**
     * @return HasMany<HistorialEstadoOrden, $this>
     */
    public function historial(): HasMany
    {
        return $this->hasMany(HistorialEstadoOrden::class, 'id_orden', 'id_orden');
    }

    public function estaCerrada(): bool
    {
        return $this->estado === EstadoOrden::Cerrada;
    }
}
