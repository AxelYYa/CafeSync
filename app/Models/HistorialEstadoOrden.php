<?php

namespace App\Models;

use App\Enums\EstadoOrden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstadoOrden extends Model
{
    public $timestamps = false;

    protected $table = 'historial_estados_orden';

    protected $primaryKey = 'id_historial';

    protected $fillable = [
        'id_orden',
        'id_usuario',
        'estado_anterior',
        'estado_nuevo',
        'observacion',
        'fecha_cambio',
    ];

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'estado_anterior' => EstadoOrden::class,
            'estado_nuevo' => EstadoOrden::class,
            'fecha_cambio' => 'datetime',
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
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
