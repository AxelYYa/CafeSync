<?php

namespace App\Enums;

enum EstadoPreparacion: string
{
    case Pendiente = 'pendiente';
    case EnPreparacion = 'en_preparacion';
    case Lista = 'lista';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnPreparacion => 'En preparación',
            self::Lista => 'Lista',
        };
    }

    /**
     * Siguiente estado permitido en el KDS; las transiciones sólo avanzan.
     */
    public function siguiente(): ?self
    {
        return match ($this) {
            self::Pendiente => self::EnPreparacion,
            self::EnPreparacion => self::Lista,
            self::Lista => null,
        };
    }

    public function puedePasarA(self $destino): bool
    {
        return $this->siguiente() === $destino;
    }
}
