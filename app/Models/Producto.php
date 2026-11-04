<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    protected $fillable = ['id_estacion', 'nombre', 'descripcion', 'precio', 'activo'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Estacion, $this>
     */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(Estacion::class, 'id_estacion', 'id_estacion');
    }

    /**
     * @return HasMany<Receta, $this>
     */
    public function recetas(): HasMany
    {
        return $this->hasMany(Receta::class, 'id_producto', 'id_producto');
    }

    /**
     * @return HasOne<Receta, $this>
     */
    public function recetaActiva(): HasOne
    {
        return $this->hasOne(Receta::class, 'id_producto', 'id_producto')->where('activa', true);
    }

    /**
     * @param  Builder<Producto>  $query
     * @return Builder<Producto>
     */
    public function scopeVendibles(Builder $query): Builder
    {
        return $query->where('activo', true)
            ->whereHas('estacion', fn (Builder $estaciones) => $estaciones->where('activa', true));
    }
}
