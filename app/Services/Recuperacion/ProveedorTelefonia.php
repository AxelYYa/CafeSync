<?php

namespace App\Services\Recuperacion;

use RuntimeException;
use Twilio\Exceptions\RestException;
use Twilio\Exceptions\TwilioException;
use Twilio\Http\Client as ClienteHttp;
use Twilio\Rest\Client;
use Twilio\Rest\Verify\V2\ServiceContext;

/**
 * Adaptador del SDK oficial de Twilio para el servicio Verify (SMS y llamada de voz).
 * Twilio genera el código, lo entrega y lo valida; CaféSync no conoce ni guarda el código.
 * Sin TWILIO_ACCOUNT_SID, TWILIO_API_KEY, TWILIO_API_SECRET y TWILIO_VERIFY_SERVICE_SID el canal se declara no disponible.
 */
class ProveedorTelefonia
{
    public const CANAL_SMS = 'sms';

    public const CANAL_LLAMADA = 'call';

    /**
     * @param  ClienteHttp|null  $http  Cliente HTTP del SDK; en pruebas se sustituye por uno simulado para no generar cargos.
     */
    public function __construct(private readonly ?ClienteHttp $http = null) {}

    public function configurado(): bool
    {
        return filled(config('services.twilio.account_sid'))
            && filled(config('services.twilio.api_key'))
            && filled(config('services.twilio.api_secret'))
            && filled(config('services.twilio.verify_service_sid'));
    }

    /**
     * Pide a Twilio Verify que envíe un código al teléfono por el canal indicado (`sms` o `call`).
     *
     * @throws RuntimeException|TwilioException
     */
    public function enviarCodigo(string $destino, string $canal): void
    {
        $this->servicio()->verifications->create($destino, $canal, ['locale' => config('services.twilio.locale')]);
    }

    /**
     * Comprueba el código con Twilio Verify. Un 404 significa que la verificación venció, ya se aprobó
     * o agotó sus intentos; se trata igual que un código incorrecto.
     *
     * @throws RuntimeException|TwilioException
     */
    public function verificarCodigo(string $destino, string $codigo): bool
    {
        try {
            return $this->servicio()->verificationChecks->create(['to' => $destino, 'code' => $codigo])->status === 'approved';
        } catch (RestException $error) {
            if ($error->getStatusCode() === 404) {
                return false;
            }

            throw $error;
        }
    }

    /**
     * @throws RuntimeException
     */
    private function servicio(): ServiceContext
    {
        if (! $this->configurado()) {
            throw new RuntimeException('El proveedor de telefonía no está configurado.');
        }

        $cliente = new Client(
            config('services.twilio.api_key'),
            config('services.twilio.api_secret'),
            config('services.twilio.account_sid'),
            null,
            $this->http,
        );

        return $cliente->verify->v2->services(config('services.twilio.verify_service_sid'));
    }
}
