<?php

namespace App\Services\Mfa;

use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MfaService
{
    private const CODIGOS_RECUPERACION = 8;

    public function __construct(private readonly TotpService $totp) {}

    /**
     * Genera un secreto pendiente de confirmar. No activa MFA hasta verificar un código.
     */
    public function iniciarActivacion(Usuario $usuario): string
    {
        $secreto = $this->totp->generarSecreto();

        $usuario->forceFill([
            'mfa_secret' => $secreto,
            'mfa_confirmado_en' => null,
            'mfa_recovery_codes' => null,
        ])->save();

        return $secreto;
    }

    /**
     * Confirma la activación con un código válido y devuelve los códigos de recuperación en claro (una sola vez).
     *
     * @return list<string>|null
     */
    public function confirmarActivacion(Usuario $usuario, string $codigo): ?array
    {
        if ($usuario->mfa_secret === null || $usuario->tieneMfaActivo()) {
            return null;
        }

        if ($this->totp->verificar($usuario->mfa_secret, $codigo) === null) {
            return null;
        }

        $codigos = $this->generarCodigosRecuperacion();

        $usuario->forceFill([
            'mfa_confirmado_en' => now(),
            'mfa_recovery_codes' => array_map(fn (string $codigo) => $this->huella($codigo), $codigos),
        ])->save();

        return $codigos;
    }

    public function desactivar(Usuario $usuario): void
    {
        $usuario->forceFill([
            'mfa_secret' => null,
            'mfa_confirmado_en' => null,
            'mfa_recovery_codes' => null,
        ])->save();
    }

    /**
     * Valida un código TOTP o un código de recuperación de un solo uso.
     */
    public function verificarDesafio(Usuario $usuario, string $codigo): bool
    {
        if (! $usuario->tieneMfaActivo()) {
            return false;
        }

        $codigo = trim($codigo);

        if (preg_match('/^\d{3}\s?\d{3}$/', $codigo)) {
            return $this->verificarTotp($usuario, $codigo);
        }

        return $this->consumirCodigoRecuperacion($usuario, $codigo);
    }

    /**
     * @return list<string>
     */
    public function regenerarCodigosRecuperacion(Usuario $usuario): array
    {
        $codigos = $this->generarCodigosRecuperacion();

        $usuario->forceFill([
            'mfa_recovery_codes' => array_map(fn (string $codigo) => $this->huella($codigo), $codigos),
        ])->save();

        return $codigos;
    }

    public function codigosRecuperacionRestantes(Usuario $usuario): int
    {
        return count($usuario->mfa_recovery_codes ?? []);
    }

    public function uriAprovisionamiento(Usuario $usuario): string
    {
        return $this->totp->uriAprovisionamiento(
            (string) $usuario->mfa_secret,
            $usuario->username,
            config('app.name', 'CaféSync'),
        );
    }

    private function verificarTotp(Usuario $usuario, string $codigo): bool
    {
        $contador = $this->totp->verificar((string) $usuario->mfa_secret, $codigo);

        if ($contador === null) {
            return false;
        }

        // Un mismo código no puede reutilizarse dentro de su ventana de validez.
        return Cache::add("mfa:usado:{$usuario->id_usuario}:{$contador}", true, now()->addMinutes(2));
    }

    private function consumirCodigoRecuperacion(Usuario $usuario, string $codigo): bool
    {
        $huella = $this->huella($codigo);
        $guardados = $usuario->mfa_recovery_codes ?? [];

        foreach ($guardados as $indice => $guardado) {
            if (hash_equals($guardado, $huella)) {
                unset($guardados[$indice]);
                $usuario->forceFill(['mfa_recovery_codes' => array_values($guardados)])->save();

                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function generarCodigosRecuperacion(): array
    {
        return array_map(
            fn () => Str::upper(Str::random(5)).'-'.Str::upper(Str::random(5)),
            range(1, self::CODIGOS_RECUPERACION)
        );
    }

    private function huella(string $codigo): string
    {
        return hash('sha256', Str::upper(trim($codigo)));
    }
}
