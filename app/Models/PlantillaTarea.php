<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantillaTarea extends Model
{
    use HasFactory;

    protected $table = 'plantillas_tareas';

    protected $primaryKey = 'id_plantilla';

    protected $fillable = ['id_usuario_creador', 'titulo', 'descripcion', 'activa'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_creador', 'id_usuario');
    }

    /**
     * @return HasMany<AsignacionTarea, $this>
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionTarea::class, 'id_plantilla', 'id_plantilla');
    }
}
