<?php

namespace App\Enums;

enum TipoMovimiento: string
{
    case Entrada = 'entrada';
    case Salida = 'salida';
    case Consumo = 'consumo';
    case Ajuste = 'ajuste';
    case Reversion = 'reversion';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Entrada => 'Entrada',
            self::Salida => 'Salida',
            self::Consumo => 'Consumo por venta',
            self::Ajuste => 'Ajuste',
            self::Reversion => 'Reversión',
        };
    }
}
