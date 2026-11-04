<?php

namespace App\Models;

use App\Enums\EstadoTarea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionTarea extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_tareas';

    protected $primaryKey = 'id_asignacion';

    protected $fillable = [
        'id_plantilla',
        'id_turno',
        'id_estacion',
        'id_usuario_asignado',
        'id_usuario_asigno',
        'id_usuario_completo',
        'estado',
        'fecha_limite',
        'completada_en',
    ];

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'estado' => EstadoTarea::class,
            'fecha_limite' => 'datetime',
            'completada_en' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<PlantillaTarea, $this>
     */
    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaTarea::class, 'id_plantilla', 'id_plantilla');
    }

    /**
     * @return BelongsTo<Turno, $this>
     */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class, 'id_turno', 'id_turno');
    }

    /**
     * @return BelongsTo<Estacion, $this>
     */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'id_estacion', 'id_estacion');
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_asignado', 'id_usuario');
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function asignador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_asigno', 'id_usuario');
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function completadaPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_completo', 'id_usuario');
    }

    public function estaCompletada(): bool
    {
        return $this->estado === EstadoTarea::Completada;
    }
}
