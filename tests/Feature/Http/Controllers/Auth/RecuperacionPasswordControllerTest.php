<?php

use App\Mail\CodigoRecuperacionMail;
use App\Models\RecuperacionPassword;
use App\Models\Usuario;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\Fakes\ClienteHttpTwilioSimulado;
use Twilio\Http\Client as ClienteHttpTwilio;

const NUEVA_CONTRASENA = 'NuevaClave2026';

beforeEach(function () {
    $this->usuario = Usuario::factory()->conPreguntaSecreta('¿Nombre de tu primera mascota?', 'Firulais')->create([
        'username' => 'brenda',
        'correo' => 'brenda@cafesync.test',
        'telefono' => '+528711234567',
    ]);
});

function solicitarRecuperacion(string $identificador, string $canal): TestResponse
{
    return test()->post(route('recuperacion.enviar'), ['identificador' => $identificador, 'canal' => $canal]);
}

function codigoEnviadoPorCorreo(): string
{
    $codigo = null;
    Mail::assertSent(CodigoRecuperacionMail::class, function (CodigoRecuperacionMail $correo) use (&$codigo) {
        $codigo = $correo->codigo;

        return true;
    });

    return $codigo;
}

it('resets the password after answering the secret question', function () {
    solicitarRecuperacion('brenda', 'pregunta')->assertRedirect(route('recuperacion.verificar'));

    $this->post(route('recuperacion.comprobar'), ['entrada' => '  FIRULAIS '])->assertRedirect(route('recuperacion.nueva'));
    $response = $this->post(route('recuperacion.restablecer'), ['password' => NUEVA_CONTRASENA, 'password_confirmation' => NUEVA_CONTRASENA]);

    $response->assertRedirect(route('login'));
    expect(Hash::check(NUEVA_CONTRASENA, $this->usuario->fresh()->password))->toBeTrue();
});

it('rejects a wrong secret answer', function () {
    solicitarRecuperacion('brenda', 'pregunta');

    $this->post(route('recuperacion.comprobar'), ['entrada' => 'Pelusa'])->assertSessionHasErrors('entrada');

    $this->get(route('recuperacion.nueva'))->assertRedirect(route('recuperacion.solicitar'));
});

it('emails a one-time code that leads to the password reset', function () {
    Mail::fake();

    solicitarRecuperacion('brenda@cafesync.test', 'correo');
    $codigo = codigoEnviadoPorCorreo();
    $this->post(route('recuperacion.comprobar'), ['entrada' => $codigo])->assertRedirect(route('recuperacion.nueva'));
    $this->post(route('recuperacion.restablecer'), ['password' => NUEVA_CONTRASENA, 'password_confirmation' => NUEVA_CONTRASENA]);

    expect($codigo)->toMatch('/^\d{6}$/');
    expect(Hash::check(NUEVA_CONTRASENA, $this->usuario->fresh()->password))->toBeTrue();
    expect(RecuperacionPassword::first()->codigo_hash)->not->toBe($codigo);
});

it('rejects the emailed code once it expires', function () {
    Mail::fake();
    solicitarRecuperacion('brenda', 'correo');
    $codigo = codigoEnviadoPorCorreo();
    $this->travel(11)->minutes();

    $this->post(route('recuperacion.comprobar'), ['entrada' => $codigo])->assertSessionHasErrors('entrada');
});

it('invalidates the code after five wrong attempts', function () {
    Mail::fake();
    solicitarRecuperacion('brenda', 'correo');
    $codigo = codigoEnviadoPorCorreo();
    $incorrecto = $codigo === '000000' ? '111111' : '000000';

    foreach (range(1, 5) as $intento) {
        $this->post(route('recuperacion.comprobar'), ['entrada' => $incorrecto]);
    }

    $this->post(route('recuperacion.comprobar'), ['entrada' => $codigo])->assertSessionHasErrors('entrada');
});

it('responds the same way when the account does not exist', function () {
    Mail::fake();

    $response = solicitarRecuperacion('nadie@cafesync.test', 'correo');

    $response->assertRedirect(route('recuperacion.verificar'));
    Mail::assertNothingSent();
    expect(RecuperacionPassword::count())->toBe(0);
});

it('closes every open session after the reset', function () {
    DB::table('sessions')->insert([
        'id' => 'sesion-de-otro-dispositivo', 'user_id' => $this->usuario->id_usuario,
        'payload' => '', 'last_activity' => now()->timestamp,
    ]);
    solicitarRecuperacion('brenda', 'pregunta');
    $this->post(route('recuperacion.comprobar'), ['entrada' => 'Firulais']);

    $this->post(route('recuperacion.restablecer'), ['password' => NUEVA_CONTRASENA, 'password_confirmation' => NUEVA_CONTRASENA]);

    $this->assertDatabaseMissing('sessions', ['id' => 'sesion-de-otro-dispositivo']);
});

it('reports SMS as unavailable when no telephony provider is configured', function () {
    config(['services.twilio.api_secret' => null]);

    solicitarRecuperacion('brenda', 'sms')->assertSessionHasErrors('canal');

    expect(RecuperacionPassword::count())->toBe(0);
});

/**
 * Configura credenciales ficticias y sustituye la conexión HTTP del SDK de Twilio: nada sale a la red.
 */
function twilioSimulado(): ClienteHttpTwilioSimulado
{
    config(['services.twilio' => [
        'account_sid' => 'ACprueba', 'api_key' => 'SKprueba', 'api_secret' => 'secreto-de-prueba',
        'verify_service_sid' => 'VAprueba', 'locale' => 'es',
    ]]);

    $simulado = new ClienteHttpTwilioSimulado;
    app()->instance(ClienteHttpTwilio::class, $simulado);

    return $simulado;
}

it('sends a Twilio Verify code by SMS and resets the password once Twilio approves it', function () {
    $twilio = twilioSimulado()
        ->responder('/Services/VAprueba/Verifications', 201, ['sid' => 'VE1', 'status' => 'pending', 'channel' => 'sms'])
        ->responder('/Services/VAprueba/VerificationCheck', 200, ['sid' => 'VE1', 'status' => 'approved', 'valid' => true]);

    solicitarRecuperacion('brenda', 'sms')->assertRedirect(route('recuperacion.verificar'));
    $this->post(route('recuperacion.comprobar'), ['entrada' => '482 913'])->assertRedirect(route('recuperacion.nueva'));
    $this->post(route('recuperacion.restablecer'), ['password' => NUEVA_CONTRASENA, 'password_confirmation' => NUEVA_CONTRASENA]);

    expect($twilio->peticiones)->toHaveCount(2);
    expect($twilio->peticiones[0])
        ->method->toBe('POST')
        ->user->toBe('SKprueba')
        ->data->toMatchArray(['To' => '+528711234567', 'Channel' => 'sms', 'Locale' => 'es']);
    expect($twilio->peticiones[1]['data'])->toMatchArray(['To' => '+528711234567', 'Code' => '482913']);
    expect(Hash::check(NUEVA_CONTRASENA, $this->usuario->fresh()->password))->toBeTrue();
    expect(RecuperacionPassword::sole()->codigo_hash)->toBeNull();
});

it('asks Twilio Verify for a voice call', function () {
    $twilio = twilioSimulado()->responder('/Services/VAprueba/Verifications', 201, ['sid' => 'VE2', 'status' => 'pending', 'channel' => 'call']);

    solicitarRecuperacion('brenda', 'llamada')->assertRedirect(route('recuperacion.verificar'));

    expect($twilio->peticiones[0]['data'])->toMatchArray(['To' => '+528711234567', 'Channel' => 'call']);
});

it('rejects a code that Twilio Verify does not approve', function (int $estado, array $cuerpo) {
    twilioSimulado()
        ->responder('/Services/VAprueba/Verifications', 201, ['sid' => 'VE1', 'status' => 'pending'])
        ->responder('/Services/VAprueba/VerificationCheck', $estado, $cuerpo);
    solicitarRecuperacion('brenda', 'sms');

    $this->post(route('recuperacion.comprobar'), ['entrada' => '000000'])->assertSessionHasErrors('entrada');

    $this->get(route('recuperacion.nueva'))->assertRedirect(route('recuperacion.solicitar'));
})->with([
    'código incorrecto' => [200, ['sid' => 'VE1', 'status' => 'pending', 'valid' => false]],
    'verificación vencida o inexistente' => [404, ['code' => 20404, 'message' => 'The requested resource was not found', 'status' => 404]],
]);

it('does not ask Twilio to check a malformed code', function () {
    $twilio = twilioSimulado()->responder('/Services/VAprueba/Verifications', 201, ['sid' => 'VE1', 'status' => 'pending']);
    solicitarRecuperacion('brenda', 'sms');

    $this->post(route('recuperacion.comprobar'), ['entrada' => 'abc'])->assertSessionHasErrors('entrada');

    expect($twilio->peticiones)->toHaveCount(1);
});

it('discards the request when Twilio fails, without revealing it', function () {
    twilioSimulado()->responder('/Services/VAprueba/Verifications', 500, ['code' => 20500, 'message' => 'Internal Server Error', 'status' => 500]);

    solicitarRecuperacion('brenda', 'sms')->assertRedirect(route('recuperacion.verificar'));

    expect(RecuperacionPassword::sole()->usado_en)->not->toBeNull();
});

it('does not call Twilio for an account without a phone number', function () {
    $twilio = twilioSimulado();
    $this->usuario->update(['telefono' => null]);

    solicitarRecuperacion('brenda', 'sms')->assertRedirect(route('recuperacion.verificar'));

    expect($twilio->peticiones)->toBe([]);
    expect(RecuperacionPassword::count())->toBe(0);
});

it('marks the channels without a configured provider as unavailable', function () {
    config(['services.twilio.api_secret' => null]);

    $this->get(route('recuperacion.solicitar'))
        ->assertOk()
        ->assertSee('Pregunta secreta')
        ->assertSee('No configurado en este servidor');
});

it('shows the secret question of the account in the verification step', function () {
    solicitarRecuperacion('brenda', 'pregunta');

    $this->get(route('recuperacion.verificar'))->assertOk()->assertSee('¿Nombre de tu primera mascota?');
});
