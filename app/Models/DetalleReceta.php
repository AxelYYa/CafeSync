<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleReceta extends Model
{
    use HasFactory;

    protected $table = 'detalle_recetas';

    protected $primaryKey = 'id_detalle_receta';

    protected $fillable = ['id_receta', 'id_ingrediente', 'cantidad'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['cantidad' => 'decimal:3'];
    }

    /**
     * @return BelongsTo<Receta, $this>
     */
    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'id_receta', 'id_receta');
    }

    /**
     * @return BelongsTo<Ingrediente, $this>
     */
    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class, 'id_ingrediente', 'id_ingrediente');
    }
}
