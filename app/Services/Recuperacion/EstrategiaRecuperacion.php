<?php

namespace App\Services\Recuperacion;

use App\Enums\CanalRecuperacion;
use App\Models\RecuperacionPassword;
use App\Models\Usuario;

/**
 * Estrategia de verificación de identidad para recuperar la contraseña.
 * Cada canal (correo, SMS, llamada, pregunta secreta) implementa su propia forma de comprobar al usuario.
 */
interface EstrategiaRecuperacion
{
    public function canal(): CanalRecuperacion;

    /**
     * Indica si el canal está configurado en el servidor para funcionar de verdad.
     */
    public function disponible(): bool;

    /**
     * Indica si el usuario tiene los datos necesarios (correo, teléfono o pregunta) para este canal.
     */
    public function aplicaA(Usuario $usuario): bool;

    /**
     * Prepara la verificación: envía el código o deja lista la pregunta.
     */
    public function iniciar(Usuario $usuario, RecuperacionPassword $solicitud): void;

    public function verificar(Usuario $usuario, RecuperacionPassword $solicitud, string $entrada): bool;
}
