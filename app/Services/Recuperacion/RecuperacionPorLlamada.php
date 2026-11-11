<?php

namespace App\Services\Recuperacion;

use App\Enums\CanalRecuperacion;
use App\Models\Usuario;

class RecuperacionPorLlamada extends EstrategiaConCodigo
{
    public function __construct(private readonly ProveedorTelefonia $proveedor) {}

    public function canal(): CanalRecuperacion
    {
        return CanalRecuperacion::Llamada;
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
        $this->proveedor->llamarConCodigo($usuario->telefono, $codigo);
    }
}
