<?php

namespace App\Models;

use App\Enums\NombreRol;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'id_rol',
        'nombre',
        'username',
        'correo',
        'telefono',
        'password',
        'pregunta_secreta',
        'activo',
    ];

    protected $hidden = [
        'password',
        'pin_hash',
        'pin_lookup',
        'respuesta_secreta_hash',
        'mfa_secret',
        'mfa_recovery_codes',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
            'mfa_secret' => 'encrypted',
            'mfa_recovery_codes' => 'encrypted:array',
            'mfa_confirmado_en' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Rol, $this>
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    /**
     * @return HasMany<Asistencia, $this>
     */
    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class, 'id_usuario', 'id_usuario');
    }

    /**
     * @return HasMany<AsignacionTarea, $this>
     */
    public function tareasAsignadas(): HasMany
    {
        return $this->hasMany(AsignacionTarea::class, 'id_usuario_asignado', 'id_usuario');
    }

    public function esGerente(): bool
    {
        return $this->nombreRol() === NombreRol::Gerente->value;
    }

    public function esEmpleado(): bool
    {
        return $this->nombreRol() === NombreRol::Empleado->value;
    }

    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->nombreRol(), $roles, true);
    }

    private function nombreRol(): ?string
    {
        return $this->loadMissing('rol')->rol?->nombre;
    }

    public function tieneMfaActivo(): bool
    {
        return $this->mfa_confirmado_en !== null && $this->mfa_secret !== null;
    }

    public function tienePin(): bool
    {
        return $this->pin_hash !== null;
    }

    public function tienePreguntaSecreta(): bool
    {
        return $this->pregunta_secreta !== null && $this->respuesta_secreta_hash !== null;
    }

    public function routeNotificationForMail(): string
    {
        return $this->correo;
    }
}
