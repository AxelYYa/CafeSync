<?php

namespace App\Models;

use App\Enums\TipoAsistencia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    public $timestamps = false;

    protected $table = 'asistencias';

    protected $primaryKey = 'id_asistencia';

    protected $fillable = ['id_usuario', 'id_turno', 'tipo', 'metodo', 'fecha_hora', 'nota'];

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'tipo' => TipoAsistencia::class,
            'fecha_hora' => 'datetime',
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
     * @return BelongsTo<Turno, $this>
     */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'id_turno', 'id_turno');
    }
}
