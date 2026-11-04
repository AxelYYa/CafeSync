<?php

namespace App\Models;

use App\Enums\CanalRecuperacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecuperacionPassword extends Model
{
    protected $table = 'recuperaciones_password';

    protected $primaryKey = 'id_recuperacion';

    protected $fillable = [
        'id_usuario',
        'canal',
        'codigo_hash',
        'token_hash',
        'intentos',
        'expira_en',
        'verificado_en',
        'usado_en',
    ];

    protected $hidden = ['codigo_hash', 'token_hash'];

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'canal' => CanalRecuperacion::class,
            'intentos' => 'integer',
            'expira_en' => 'datetime',
            'verificado_en' => 'datetime',
            'usado_en' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function estaVigente(): bool
    {
        return $this->usado_en === null && $this->expira_en->isFuture();
    }
}
