<?php

use App\Models\Estacion;
use App\Models\Usuario;
use App\Services\Sesiones\SesionService;
use Illuminate\Session\Middleware\StartSession;

beforeEach(function () {
    $this->usuario = Usuario::factory()->create();
});

function registrarSesion(string $id, Usuario $usuario, string $agente = 'Mozilla/5.0 (Windows NT 10.0) Chrome/130.0'): void
{
    DB::table('sessions')->insert([
        'id' => $id, 'user_id' => $usuario->id_usuario, 'ip_address' => '192.168.1.20',
        'user_agent' => $agente, 'payload' => '', 'last_activity' => now()->timestamp,
    ]);
}

/**
 * Descarta la sesión y el usuario que el contenedor conserva entre peticiones de la misma prueba,
 * para que la siguiente petición dependa solo de la cookie, como un navegador real.
 */
function olvidarEstadoDeSesion(): void
{
    app()->forgetInstance('session');
    app()->forgetInstance('session.store');
    app()->forgetInstance(StartSession::class);
    app('auth')->forgetGuards();
}

it('closes a session of the same user from another device', function () {
    registrarSesion('tableta-de-cocina', $this->usuario);

    $response = $this->actingAs($this->usuario)->delete(route('sesiones.destroy', 'tableta-de-cocina'));

    $response->assertSessionHas('estado', 'La sesión se cerró; ese dispositivo debe iniciar sesión de nuevo.');
    $this->assertDatabaseMissing('sessions', ['id' => 'tableta-de-cocina']);
});

it('does not close a session that belongs to another user', function () {
    registrarSesion('sesion-ajena', Usuario::factory()->create());

    $this->actingAs($this->usuario)->delete(route('sesiones.destroy', 'sesion-ajena'))
        ->assertSessionHasErrors(['sesiones' => 'La sesión no existe o ya había sido cerrada.']);

    $this->assertDatabaseHas('sessions', ['id' => 'sesion-ajena']);
});

it('closes every other session after confirming the password', function () {
    registrarSesion('celular', $this->usuario);
    registrarSesion('caja', $this->usuario);

    $this->actingAs($this->usuario)->delete(route('sesiones.destroy-otras'), ['password_actual' => 'password'])
        ->assertSessionHas('estado', 'Se cerraron 2 sesión(es) en otros dispositivos.');

    expect(DB::table('sessions')->where('user_id', $this->usuario->id_usuario)->count())->toBe(0);
});

it('requires the current password to close the other sessions', function () {
    registrarSesion('celular', $this->usuario);

    $this->actingAs($this->usuario)->delete(route('sesiones.destroy-otras'), ['password_actual' => 'incorrecta'])
        ->assertSessionHasErrors('password_actual');

    $this->assertDatabaseHas('sessions', ['id' => 'celular']);
});

it('identifies the device, browser and system of each session', function () {
    config(['session.driver' => 'database']);
    registrarSesion('celular', $this->usuario, 'Mozilla/5.0 (Linux; Android 14) Chrome/130.0 Mobile Safari/537.36');

    $sesiones = app(SesionService::class)->listar($this->usuario, 'otra');

    expect($sesiones[0])->toMatchArray([
        'id' => 'celular', 'ip' => '192.168.1.20', 'dispositivo' => 'Móvil o tableta',
        'navegador' => 'Chrome', 'sistema' => 'Android', 'actual' => false,
    ]);
});

it('denies access to a device whose session was closed remotely', function () {
    config(['session.driver' => 'database']);
    $estacion = Estacion::factory()->create();
    $sinCambios = route('kds.datos', [$estacion, 'version' => md5('[]')]);

    $cookie = $this->post(route('login.store'), ['login' => $this->usuario->username, 'password' => 'password'])
        ->getCookie(config('session.cookie'))->getValue();
    olvidarEstadoDeSesion();
    $antes = $this->withCredentials()->withCookie(config('session.cookie'), $cookie)->getJson($sinCambios);

    app(SesionService::class)->cerrar($this->usuario, $cookie);
    olvidarEstadoDeSesion();
    $despues = $this->withCredentials()->withCookie(config('session.cookie'), $cookie)->getJson($sinCambios);

    $antes->assertOk();
    $despues->assertUnauthorized();
});
