<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receta extends Model
{
    use HasFactory;

    protected $table = 'recetas';

    protected $primaryKey = 'id_receta';

    protected $fillable = ['id_producto', 'version', 'instrucciones', 'activa', 'id_usuario_creador'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'activa' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_creador', 'id_usuario');
    }

    /**
     * @return HasMany<DetalleReceta, $this>
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleReceta::class, 'id_receta', 'id_receta');
    }

    /**
     * Pasos de preparación como lista; cada línea no vacía es un paso.
     *
     * @return list<string>
     */
    public function pasos(): array
    {
        $lineas = preg_split('/\r\n|\r|\n/', (string) $this->instrucciones) ?: [];

        return array_values(array_filter(array_map('trim', $lineas), fn (string $linea) => $linea !== ''));
    }
}
