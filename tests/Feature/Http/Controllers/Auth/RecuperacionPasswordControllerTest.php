<?php

use App\Mail\CodigoRecuperacionMail;
use App\Models\RecuperacionPassword;
use App\Models\Usuario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

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
    solicitarRecuperacion('brenda', 'sms')->assertSessionHasErrors('canal');

    expect(RecuperacionPassword::count())->toBe(0);
});

it('sends the code by SMS through Twilio when it is configured', function () {
    config(['services.twilio' => ['sid' => 'AC123', 'token' => 'secreto', 'from' => '+15005550006']]);
    Http::preventStrayRequests();
    Http::fake(['https://api.twilio.com/2010-04-01/Accounts/AC123/Messages.json' => Http::response(['sid' => 'SM1'], 201)]);

    solicitarRecuperacion('brenda', 'sms');

    Http::assertSent(fn ($peticion) => $peticion['To'] === '+528711234567'
        && preg_match('/código de recuperación es \d{6}/', $peticion['Body']) === 1);
});

it('places a voice call with the code through Twilio when it is configured', function () {
    config(['services.twilio' => ['sid' => 'AC123', 'token' => 'secreto', 'from' => '+15005550006']]);
    Http::preventStrayRequests();
    Http::fake(['https://api.twilio.com/2010-04-01/Accounts/AC123/Calls.json' => Http::response(['sid' => 'CA1'], 201)]);

    solicitarRecuperacion('brenda', 'llamada');

    Http::assertSent(fn ($peticion) => $peticion['To'] === '+528711234567' && str_contains($peticion['Twiml'], '<Say language="es-MX">'));
});

it('discards the request when the provider fails, without revealing it', function () {
    config(['services.twilio' => ['sid' => 'AC123', 'token' => 'secreto', 'from' => '+15005550006']]);
    Http::preventStrayRequests();
    Http::fake(['https://api.twilio.com/2010-04-01/Accounts/AC123/Messages.json' => Http::response([], 500)]);

    solicitarRecuperacion('brenda', 'sms')->assertRedirect(route('recuperacion.verificar'));

    expect(RecuperacionPassword::first()->usado_en)->not->toBeNull();
});

it('marks the channels without a configured provider as unavailable', function () {
    $this->get(route('recuperacion.solicitar'))
        ->assertOk()
        ->assertSee('Pregunta secreta')
        ->assertSee('No configurado en este servidor');
});

it('shows the secret question of the account in the verification step', function () {
    solicitarRecuperacion('brenda', 'pregunta');

    $this->get(route('recuperacion.verificar'))->assertOk()->assertSee('¿Nombre de tu primera mascota?');
});
