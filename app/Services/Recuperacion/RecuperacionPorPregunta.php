<?php

namespace App\Services\Recuperacion;

use App\Enums\CanalRecuperacion;
use App\Models\RecuperacionPassword;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class RecuperacionPorPregunta implements EstrategiaRecuperacion
{
    public function canal(): CanalRecuperacion
    {
        return CanalRecuperacion::PreguntaSecreta;
    }

    public function disponible(): bool
    {
        return true;
    }

    public function aplicaA(Usuario $usuario): bool
    {
        return $usuario->tienePreguntaSecreta();
    }

    public function iniciar(Usuario $usuario, RecuperacionPassword $solicitud): void
    {
        // La pregunta ya está en el perfil; no hay nada que enviar.
    }

    public function verificar(Usuario $usuario, RecuperacionPassword $solicitud, string $entrada): bool
    {
        return $usuario->respuesta_secreta_hash !== null
            && Hash::check(self::normalizar($entrada), $usuario->respuesta_secreta_hash);
    }

    public static function normalizar(string $respuesta): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $respuesta) ?? ''));
    }
}
