<?php

namespace App\Services\Recuperacion;

use App\Enums\CanalRecuperacion;
use App\Mail\CodigoRecuperacionMail;
use App\Models\Usuario;
use Illuminate\Support\Facades\Mail;

class RecuperacionPorCorreo extends EstrategiaConCodigo
{
    public function canal(): CanalRecuperacion
    {
        return CanalRecuperacion::Correo;
    }

    /**
     * Con MAIL_MAILER=log el código sólo se escribe en el log: es válido para desarrollo, no para producción.
     */
    public function disponible(): bool
    {
        $transporte = (string) config('mail.default');

        if (in_array($transporte, ['log', 'array'], true)) {
            return app()->environment(['local', 'testing']);
        }

        return true;
    }

    public function aplicaA(Usuario $usuario): bool
    {
        return filled($usuario->correo);
    }

    protected function enviarCodigo(Usuario $usuario, string $codigo): void
    {
        Mail::to($usuario->correo)->send(new CodigoRecuperacionMail($usuario, $codigo));
    }
}
