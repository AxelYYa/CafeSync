<?php

namespace App\Models;

use App\Enums\UnidadMedida;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingrediente extends Model
{
    use HasFactory;

    protected $table = 'ingredientes';

    protected $primaryKey = 'id_ingrediente';

    protected $fillable = ['nombre', 'unidad_medida', 'stock_actual', 'stock_minimo', 'activo'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stock_actual' => 'decimal:3',
            'stock_minimo' => 'decimal:3',
            'activo' => 'boolean',
        ];
    }

    /**
     * @return HasMany<MovimientoInventario, $this>
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'id_ingrediente', 'id_ingrediente');
    }

    /**
     * @return HasMany<DetalleReceta, $this>
     */
    public function detallesReceta(): HasMany
    {
        return $this->hasMany(DetalleReceta::class, 'id_ingrediente', 'id_ingrediente');
    }

    public function unidad(): UnidadMedida
    {
        return UnidadMedida::from($this->unidad_medida);
    }

    public function estaBajoMinimo(): bool
    {
        return bccomp((string) $this->stock_minimo, '0', 3) > 0
            && bccomp((string) $this->stock_actual, (string) $this->stock_minimo, 3) <= 0;
    }

    /**
     * @param  Builder<Ingrediente>  $query
     * @return Builder<Ingrediente>
     */
    public function scopeBajoMinimo(Builder $query): Builder
    {
        return $query->where('activo', true)
            ->where('stock_minimo', '>', 0)
            ->whereColumn('stock_actual', '<=', 'stock_minimo');
    }
}
