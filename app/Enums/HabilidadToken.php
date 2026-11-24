<?php

namespace App\Enums;

/**
 * Permisos que puede llevar un token de API de pantalla (Sanctum). Deben coincidir con `routes/api.php`.
 */
enum HabilidadToken: string
{
    case KdsLeer = 'kds:read';
    case KdsActualizar = 'kds:update';

    public function etiqueta(): string
    {
        return match ($this) {
            self::KdsLeer => 'Consultar órdenes de una estación',
            self::KdsActualizar => 'Actualizar estados de preparación',
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
