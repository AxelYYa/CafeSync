<?php

namespace App\Enums;

use InvalidArgumentException;

/**
 * Unidades base (g, ml, unidad) y las unidades de captura convertibles a ellas.
 * Las existencias y las recetas siempre se guardan en la unidad base del ingrediente.
 */
enum UnidadMedida: string
{
    case Gramo = 'g';
    case Kilogramo = 'kg';
    case Mililitro = 'ml';
    case Litro = 'l';
    case Unidad = 'unidad';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Gramo => 'gramos (g)',
            self::Kilogramo => 'kilogramos (kg)',
            self::Mililitro => 'mililitros (ml)',
            self::Litro => 'litros (l)',
            self::Unidad => 'unidades',
        };
    }

    public function esBase(): bool
    {
        return in_array($this, self::bases(), true);
    }

    public function base(): self
    {
        return match ($this) {
            self::Gramo, self::Kilogramo => self::Gramo,
            self::Mililitro, self::Litro => self::Mililitro,
            self::Unidad => self::Unidad,
        };
    }

    public function factorABase(): string
    {
        return match ($this) {
            self::Kilogramo, self::Litro => '1000',
            default => '1',
        };
    }

    public function esCompatibleCon(self $otra): bool
    {
        return $this->base() === $otra->base();
    }

    /**
     * Convierte una cantidad capturada en esta unidad a la unidad base de su familia.
     *
     * @throws InvalidArgumentException
     */
    public function convertirABase(string|int|float $cantidad): string
    {
        if (! is_numeric($cantidad)) {
            throw new InvalidArgumentException('La cantidad debe ser numérica.');
        }

        return bcmul((string) $cantidad, $this->factorABase(), 3);
    }

    /**
     * @return list<self>
     */
    public static function bases(): array
    {
        return [self::Gramo, self::Mililitro, self::Unidad];
    }

    /**
     * Unidades de captura que pueden usarse con un ingrediente cuya base es $base.
     *
     * @return list<self>
     */
    public static function compatiblesCon(self $base): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $unidad) => $unidad->esCompatibleCon($base)
        ));
    }

    /**
     * @return list<string>
     */
    public static function valoresBase(): array
    {
        return array_map(fn (self $unidad) => $unidad->value, self::bases());
    }
}
