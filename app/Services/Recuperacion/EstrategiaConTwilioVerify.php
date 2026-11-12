<?php

namespace App\Services\Recuperacion;

use App\Models\RecuperacionPassword;
use App\Models\Usuario;

/**
 * Base de los canales telefónicos. A diferencia del correo, el código lo genera y valida Twilio Verify;
 * CaféSync conserva la solicitud para limitar intentos, vencimiento y uso del token de restablecimiento.
 */
abstract class EstrategiaConTwilioVerify implements EstrategiaRecuperacion
{
    public function __construct(protected readonly ProveedorTelefonia $proveedor) {}

    /**
     * Canal de Twilio Verify: `sms` o `call`.
     */
    abstract protected function canalTwilio(): string;

    public function disponible(): bool
    {
        return $this->proveedor->configurado();
    }

    public function aplicaA(Usuario $usuario): bool
    {
        return filled($usuario->telefono);
    }

    public function iniciar(Usuario $usuario, RecuperacionPassword $solicitud): void
    {
        $this->proveedor->enviarCodigo($usuario->telefono, $this->canalTwilio());
    }

    public function verificar(Usuario $usuario, RecuperacionPassword $solicitud, string $entrada): bool
    {
        $entrada = preg_replace('/\s+/', '', $entrada) ?? '';

        return preg_match('/^\d{4,10}$/', $entrada) === 1
            && $this->proveedor->verificarCodigo($usuario->telefono, $entrada);
    }
}
