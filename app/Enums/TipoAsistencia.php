<?php

namespace App\Enums;

enum TipoAsistencia: string
{
    case Entrada = 'entrada';
    case Salida = 'salida';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Entrada => 'Entrada',
            self::Salida => 'Salida',
        };
    }
}
