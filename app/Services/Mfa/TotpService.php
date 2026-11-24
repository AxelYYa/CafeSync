<?php

namespace App\Services\Mfa;

use InvalidArgumentException;

/**
 * Contraseña de un solo uso basada en tiempo (RFC 6238, HMAC-SHA1, 6 dígitos, 30 s).
 */
class TotpService
{
    private const ALFABETO = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function __construct(
        private readonly int $digitos = 6,
        private readonly int $periodo = 30,
    ) {}

    public function generarSecreto(int $bytes = 20): string
    {
        return $this->base32Codificar(random_bytes($bytes));
    }

    public function codigo(string $secreto, ?int $marcaTiempo = null): string
    {
        return $this->codigoParaContador($secreto, $this->contador($marcaTiempo));
    }

    /**
     * Verifica el código aceptando una ventana de ±$ventana periodos por desfase de reloj.
     * Devuelve el contador que coincidió o null si ningún código es válido.
     */
    public function verificar(string $secreto, string $codigo, int $ventana = 1, ?int $marcaTiempo = null): ?int
    {
        $codigo = preg_replace('/\s+/', '', $codigo) ?? '';

        if (! preg_match('/^\d{'.$this->digitos.'}$/', $codigo)) {
            return null;
        }

        $actual = $this->contador($marcaTiempo);

        for ($desplazamiento = -$ventana; $desplazamiento <= $ventana; $desplazamiento++) {
            $contador = $actual + $desplazamiento;

            if (hash_equals($this->codigoParaContador($secreto, $contador), $codigo)) {
                return $contador;
            }
        }

        return null;
    }

    public function uriAprovisionamiento(string $secreto, string $cuenta, string $emisor): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d',
            rawurlencode($emisor),
            rawurlencode($cuenta),
            $secreto,
            rawurlencode($emisor),
            $this->digitos,
            $this->periodo,
        );
    }

    private function contador(?int $marcaTiempo): int
    {
        return intdiv($marcaTiempo ?? time(), $this->periodo);
    }

    private function codigoParaContador(string $secreto, int $contador): string
    {
        $clave = $this->base32Decodificar($secreto);
        $mensaje = pack('J', $contador);
        $hash = hash_hmac('sha1', $mensaje, $clave, true);

        $desplazamiento = ord($hash[19]) & 0x0F;
        $binario = ((ord($hash[$desplazamiento]) & 0x7F) << 24)
            | ((ord($hash[$desplazamiento + 1]) & 0xFF) << 16)
            | ((ord($hash[$desplazamiento + 2]) & 0xFF) << 8)
            | (ord($hash[$desplazamiento + 3]) & 0xFF);

        return str_pad((string) ($binario % (10 ** $this->digitos)), $this->digitos, '0', STR_PAD_LEFT);
    }

    private function base32Codificar(string $datos): string
    {
        $bits = '';
        foreach (str_split($datos) as $caracter) {
            $bits .= str_pad(decbin(ord($caracter)), 8, '0', STR_PAD_LEFT);
        }

        $salida = '';
        foreach (str_split($bits, 5) as $grupo) {
            $salida .= self::ALFABETO[bindec(str_pad($grupo, 5, '0'))];
        }

        return $salida;
    }

    private function base32Decodificar(string $texto): string
    {
        $texto = strtoupper(rtrim($texto, '='));
        $bits = '';

        foreach (str_split($texto) as $caracter) {
            $posicion = strpos(self::ALFABETO, $caracter);

            if ($posicion === false) {
                throw new InvalidArgumentException('Secreto TOTP inválido.');
            }

            $bits .= str_pad(decbin($posicion), 5, '0', STR_PAD_LEFT);
        }

        $salida = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $salida .= chr((int) bindec($byte));
            }
        }

        return $salida;
    }
}
