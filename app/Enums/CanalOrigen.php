<?php

namespace App\Enums;

enum CanalOrigen: string
{
    case Mostrador = 'mostrador';
    case DriveThru = 'drive_thru';
    case Delivery = 'delivery';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Mostrador => 'Mostrador',
            self::DriveThru => 'Drive-Thru',
            self::Delivery => 'Delivery',
        };
    }

    public function icono(): string
    {
        return match ($this) {
            self::Mostrador => 'store',
            self::DriveThru => 'car',
            self::Delivery => 'bike',
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
