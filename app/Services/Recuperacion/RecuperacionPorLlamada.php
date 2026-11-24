<?php

namespace App\Services\Recuperacion;

use App\Enums\CanalRecuperacion;

class RecuperacionPorLlamada extends EstrategiaConTwilioVerify
{
    public function canal(): CanalRecuperacion
    {
        return CanalRecuperacion::Llamada;
    }

    protected function canalTwilio(): string
    {
        return ProveedorTelefonia::CANAL_LLAMADA;
    }
}
