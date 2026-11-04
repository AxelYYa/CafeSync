<?php

namespace App\Enums;

enum NombreRol: string
{
    case Gerente = 'gerente';
    case Empleado = 'empleado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Gerente => 'Gerente',
            self::Empleado => 'Empleado',
        };
    }
}
