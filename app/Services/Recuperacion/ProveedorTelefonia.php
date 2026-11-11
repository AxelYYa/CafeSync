<?php

namespace App\Services\Recuperacion;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cliente mínimo de la API REST de Twilio para SMS y llamadas de voz.
 * Requiere TWILIO_SID, TWILIO_TOKEN y TWILIO_FROM; sin ellos el canal se declara no disponible.
 */
class ProveedorTelefonia
{
    public function configurado(): bool
    {
        return filled(config('services.twilio.sid'))
            && filled(config('services.twilio.token'))
            && filled(config('services.twilio.from'));
    }

    /**
     * @throws RuntimeException|RequestException
     */
    public function enviarSms(string $destino, string $mensaje): void
    {
        $this->solicitar('Messages', [
            'To' => $destino,
            'From' => config('services.twilio.from'),
            'Body' => $mensaje,
        ]);
    }

    /**
     * @throws RuntimeException|RequestException
     */
    public function llamarConCodigo(string $destino, string $codigo): void
    {
        $digitos = implode(', ', str_split($codigo));
        $mensaje = "Hola. Tu código de recuperación de CaféSync es: {$digitos}. Repito: {$digitos}.";
        $twiml = '<Response><Say language="es-MX">'.e($mensaje).'</Say></Response>';

        $this->solicitar('Calls', [
            'To' => $destino,
            'From' => config('services.twilio.from'),
            'Twiml' => $twiml,
        ]);
    }

    /**
     * @param  array<string, string>  $datos
     */
    private function solicitar(string $recurso, array $datos): void
    {
        if (! $this->configurado()) {
            throw new RuntimeException('El proveedor de telefonía no está configurado.');
        }

        $sid = config('services.twilio.sid');

        Http::asForm()
            ->withBasicAuth($sid, config('services.twilio.token'))
            ->timeout(10)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/{$recurso}.json", $datos)
            ->throw();
    }
}
