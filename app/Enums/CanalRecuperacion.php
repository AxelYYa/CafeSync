<?php

namespace App\Enums;

enum CanalRecuperacion: string
{
    case Correo = 'correo';
    case PreguntaSecreta = 'pregunta';
    case Llamada = 'llamada';
    case Sms = 'sms';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Correo => 'Correo electrónico',
            self::PreguntaSecreta => 'Pregunta secreta',
            self::Llamada => 'Llamada telefónica',
            self::Sms => 'Mensaje SMS',
        };
    }

    /**
     * @return list<string>
     */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
