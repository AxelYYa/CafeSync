<?php

namespace Tests\Fakes;

use RuntimeException;
use Twilio\AuthStrategy\AuthStrategy;
use Twilio\Http\Client;
use Twilio\Http\Response;

/**
 * Sustituye la conexión HTTP del SDK de Twilio en las pruebas: el SDK arma la petición real,
 * pero nada sale a la red y no se generan cargos. Registra cada petición y responde con lo programado.
 */
class ClienteHttpTwilioSimulado implements Client
{
    /**
     * @var list<array{method: string, url: string, data: array<string, mixed>, user: ?string}>
     */
    public array $peticiones = [];

    /**
     * @var array<string, list<Response>>
     */
    private array $respuestas = [];

    /**
     * Programa la respuesta para la siguiente petición cuya URL termine con $sufijo.
     *
     * @param  array<string, mixed>  $cuerpo
     */
    public function responder(string $sufijo, int $estado, array $cuerpo): self
    {
        $this->respuestas[$sufijo][] = new Response($estado, json_encode($cuerpo, JSON_THROW_ON_ERROR));

        return $this;
    }

    public function request(
        string $method,
        string $url,
        array $params = [],
        array $data = [],
        array $headers = [],
        ?string $user = null,
        ?string $password = null,
        ?int $timeout = null,
        ?AuthStrategy $authStrategy = null,
    ): Response {
        $this->peticiones[] = ['method' => $method, 'url' => $url, 'data' => $data, 'user' => $user];

        foreach ($this->respuestas as $sufijo => $cola) {
            if (str_ends_with($url, $sufijo) && $cola !== []) {
                return array_shift($this->respuestas[$sufijo]);
            }
        }

        throw new RuntimeException("Petición a Twilio no programada en la prueba: {$method} {$url}");
    }
}
