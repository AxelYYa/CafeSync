<?php

namespace App\Services\Recuperacion;

use App\Enums\CanalRecuperacion;
use App\Models\Usuario;

class RecuperacionPorSms extends EstrategiaConCodigo
{
    public function __construct(private readonly ProveedorTelefonia $proveedor) {}

    public function canal(): CanalRecuperacion
    {
        return CanalRecuperacion::Sms;
    }

    public function disponible(): bool
    {
        return $this->proveedor->configurado();
    }

    public function aplicaA(Usuario $usuario): bool
    {
        return filled($usuario->telefono);
    }

    protected function enviarCodigo(Usuario $usuario, string $codigo): void
    {
        $this->proveedor->enviarSms(
            $usuario->telefono,
            "CaféSync: tu código de recuperación es {$codigo}. Vence en 10 minutos. No lo compartas."
        );
    }
}
