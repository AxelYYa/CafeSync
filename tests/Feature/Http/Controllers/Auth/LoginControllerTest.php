<?php

use App\Models\Usuario;
use App\Services\Mfa\TotpService;

beforeEach(function () {
    $this->usuario = Usuario::factory()->create(['username' => 'brenda', 'correo' => 'brenda@cafesync.test']);
});

describe('login', function () {
    it('signs in with the username or the email', function (string $identificador) {
        $response = $this->post(route('login.store'), ['login' => $identificador, 'password' => 'password']);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->usuario);
    })->with(['usuario' => 'brenda', 'correo' => 'BRENDA@cafesync.test']);

    it('rejects a wrong password with a generic message', function () {
        $response = $this->post(route('login.store'), ['login' => 'brenda', 'password' => 'incorrecta']);

        $response->assertSessionHasErrors(['login' => 'Usuario o contraseña incorrectos.']);
        $this->assertGuest();
    });

    it('gives the same message for an account that does not exist', function () {
        $this->post(route('login.store'), ['login' => 'nadie', 'password' => 'password'])
            ->assertSessionHasErrors(['login' => 'Usuario o contraseña incorrectos.']);

        $this->assertGuest();
    });

    it('locks the account after five failed attempts even with the right password', function () {
        foreach (range(1, 5) as $intento) {
            $this->post(route('login.store'), ['login' => 'brenda', 'password' => 'incorrecta']);
        }

        $response = $this->post(route('login.store'), ['login' => 'brenda', 'password' => 'password']);

        expect(session('errors')->first('login'))->toStartWith('Demasiados intentos.');
        $this->assertGuest();
    });

    it('rejects an inactive account', function () {
        $this->usuario->update(['activo' => false]);

        $this->post(route('login.store'), ['login' => 'brenda', 'password' => 'password'])
            ->assertSessionHasErrors(['login' => 'Tu cuenta está desactivada. Contacta al gerente.']);

        $this->assertGuest();
    });

    it('requires both fields', function () {
        $this->post(route('login.store'), [])->assertSessionHasErrors(['login', 'password']);
    });
});

describe('mfa', function () {
    beforeEach(function () {
        $this->totp = app(TotpService::class);
        $this->secreto = $this->totp->generarSecreto();
        $this->usuario->forceFill(['mfa_secret' => $this->secreto, 'mfa_confirmado_en' => now(), 'mfa_recovery_codes' => []])->save();
    });

    it('asks for the second factor before opening the session', function () {
        $response = $this->post(route('login.store'), ['login' => 'brenda', 'password' => 'password']);

        $response->assertRedirect(route('mfa.desafio'));
        $this->assertGuest();
    });

    it('opens the session with a valid authenticator code', function () {
        $this->post(route('login.store'), ['login' => 'brenda', 'password' => 'password']);

        $response = $this->post(route('mfa.verificar'), ['codigo' => $this->totp->codigo($this->secreto)]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->usuario);
    });

    it('rejects a wrong authenticator code', function () {
        $this->post(route('login.store'), ['login' => 'brenda', 'password' => 'password']);

        $this->post(route('mfa.verificar'), ['codigo' => '000000'])
            ->assertSessionHasErrors(['codigo' => 'El código no es válido o ya fue utilizado.']);

        $this->assertGuest();
    });

    it('sends the user back to the login when the challenge expired', function () {
        $this->post(route('login.store'), ['login' => 'brenda', 'password' => 'password']);
        $this->travel(6)->minutes();

        $this->post(route('mfa.verificar'), ['codigo' => $this->totp->codigo($this->secreto)])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    });

    it('does not accept the code without a password step first', function () {
        $this->post(route('mfa.verificar'), ['codigo' => $this->totp->codigo($this->secreto)])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    });
});

it('signs out and invalidates the session', function () {
    $this->actingAs($this->usuario);

    $response = $this->post(route('logout'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

it('renders the login form', function () {
    $this->get(route('login'))->assertOk()->assertSee('Usuario o correo');
});
