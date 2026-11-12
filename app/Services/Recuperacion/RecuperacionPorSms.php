<?php

namespace App\Services\Recuperacion;

use App\Enums\CanalRecuperacion;

class RecuperacionPorSms extends EstrategiaConTwilioVerify
{
    public function canal(): CanalRecuperacion
    {
        return CanalRecuperacion::Sms;
    }

    protected function canalTwilio(): string
    {
        return ProveedorTelefonia::CANAL_SMS;
    }
}
