<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    use HasFactory;

    protected $table = 'turnos';

    protected $primaryKey = 'id_turno';

    protected $fillable = ['nombre', 'hora_inicio', 'hora_fin', 'activo'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /**
     * @return HasMany<Asistencia, $this>
     */
    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'id_turno', 'id_turno');
    }

    /**
     * Indica si una hora (HH:MM:SS) cae dentro del turno, incluso si cruza la medianoche.
     */
    public function incluyeHora(string $hora): bool
    {
        $inicio = substr((string) $this->hora_inicio, 0, 8);
        $fin = substr((string) $this->hora_fin, 0, 8);

        if ($inicio <= $fin) {
            return $hora >= $inicio && $hora < $fin;
        }

        return $hora >= $inicio || $hora < $fin;
    }
}
