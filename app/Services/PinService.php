<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

/**
 * Los PIN se guardan con bcrypt (pin_hash). Para identificar al empleado sin recorrer
 * todos los hashes se usa un índice HMAC con la clave de la aplicación (pin_lookup).
 */
class PinService
{
    public function hashear(string $pin): string
    {
        return Hash::make($pin);
    }

    public function indice(string $pin): string
    {
        return hash_hmac('sha256', $pin, (string) config('app.key'));
    }

    /**
     * Devuelve el usuario activo cuyo PIN coincide, o null si no existe o está inactivo.
     */
    public function resolver(string $pin): ?Usuario
    {
        $usuario = Usuario::query()
            ->with('rol')
            ->where('pin_lookup', $this->indice($pin))
            ->where('activo', true)
            ->first();

        if ($usuario === null || $usuario->pin_hash === null) {
            return null;
        }

        return Hash::check($pin, $usuario->pin_hash) ? $usuario : null;
    }

    public function pinDisponible(string $pin, ?int $exceptoUsuario = null): bool
    {
        return ! Usuario::query()
            ->where('pin_lookup', $this->indice($pin))
            ->when($exceptoUsuario, fn ($consulta) => $consulta->where('id_usuario', '!=', $exceptoUsuario))
            ->exists();
    }

    public function asignar(Usuario $usuario, string $pin): void
    {
        $usuario->forceFill([
            'pin_hash' => $this->hashear($pin),
            'pin_lookup' => $this->indice($pin),
        ])->save();
    }
}
