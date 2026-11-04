<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estacion extends Model
{
    use HasFactory;

    protected $table = 'estaciones';

    protected $primaryKey = 'id_estacion';

    protected $fillable = ['nombre', 'descripcion', 'activa'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    /**
     * @return HasMany<Producto, $this>
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_estacion', 'id_estacion');
    }

    /**
     * @return HasMany<DetalleOrden, $this>
     */
    public function detallesOrden(): HasMany
    {
        return $this->hasMany(DetalleOrden::class, 'id_estacion', 'id_estacion');
    }
}
