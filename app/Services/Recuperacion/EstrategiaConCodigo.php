<?php

namespace App\Services\Recuperacion;

use App\Models\RecuperacionPassword;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

/**
 * Base de los canales que envían un código numérico de un solo uso.
 */
abstract class EstrategiaConCodigo implements EstrategiaRecuperacion
{
    abstract protected function enviarCodigo(Usuario $usuario, string $codigo): void;

    public function iniciar(Usuario $usuario, RecuperacionPassword $solicitud): void
    {
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $solicitud->forceFill(['codigo_hash' => Hash::make($codigo)])->save();

        $this->enviarCodigo($usuario, $codigo);
    }

    public function verificar(Usuario $usuario, RecuperacionPassword $solicitud, string $entrada): bool
    {
        $entrada = preg_replace('/\s+/', '', $entrada) ?? '';

        return $solicitud->codigo_hash !== null
            && preg_match('/^\d{6}$/', $entrada) === 1
            && Hash::check($entrada, $solicitud->codigo_hash);
    }
}
