<?php

namespace App\Enums;

enum EstadoOrden: string
{
    case Capturada = 'capturada';
    case EnPreparacion = 'en_preparacion';
    case Lista = 'lista';
    case Entregada = 'entregada';
    case Cerrada = 'cerrada';
    case Cancelada = 'cancelada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Capturada => 'Capturada',
            self::EnPreparacion => 'En preparación',
            self::Lista => 'Lista para entrega',
            self::Entregada => 'Entregada',
            self::Cerrada => 'Cerrada',
            self::Cancelada => 'Cancelada',
        };
    }

    /**
     * Estados desde los que se puede cerrar la venta.
     *
     * @return list<self>
     */
    public static function cerrables(): array
    {
        return [self::Lista, self::Entregada];
    }

    /**
     * Estados que todavía forman parte del flujo de preparación.
     *
     * @return list<self>
     */
    public static function activos(): array
    {
        return [self::Capturada, self::EnPreparacion, self::Lista, self::Entregada];
    }
}
