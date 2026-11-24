<?php

use App\Models\Usuario;
use App\Services\PinService;

beforeEach(function () {
    $this->usuario = Usuario::factory()->create();
});

it('lists the active sessions of the user', function () {
    config(['session.driver' => 'database']);
    DB::table('sessions')->insert([
        'id' => 'celular', 'user_id' => $this->usuario->id_usuario, 'ip_address' => '10.0.0.8',
        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Safari/604.1', 'payload' => '', 'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($this->usuario)->get(route('cuenta.seguridad'))
        ->assertOk()
        ->assertSee('Sesiones activas')
        ->assertSee('Safari en iOS')
        ->assertSee('IP 10.0.0.8');
});

it('shows the setup key after starting two-step verification', function () {
    $this->actingAs($this->usuario)->post(route('mfa.iniciar'))->assertRedirect(route('cuenta.seguridad').'#mfa');

    $secreto = $this->usuario->fresh()->mfa_secret;

    $this->get(route('cuenta.seguridad'))
        ->assertOk()
        ->assertSee(trim(chunk_split($secreto, 4, ' ')))
        ->assertSee('Activar');
});

it('stores a new PIN only as a hash', function () {
    $this->actingAs($this->usuario)->put(route('cuenta.pin'), ['pin' => '5831', 'pin_confirmation' => '5831', 'password_actual' => 'password'])
        ->assertSessionHas('estado', 'PIN actualizado.');

    $guardado = $this->usuario->fresh();
    expect($guardado->pin_hash)->not->toBe('5831');
    expect(app(PinService::class)->resolver('5831')?->id_usuario)->toBe($this->usuario->id_usuario);
});

it('rejects a PIN that another employee already uses', function () {
    Usuario::factory()->conPin('5831')->create();

    $this->actingAs($this->usuario)->put(route('cuenta.pin'), ['pin' => '5831', 'pin_confirmation' => '5831', 'password_actual' => 'password'])
        ->assertSessionHasErrorsIn('pin', ['pin' => 'Ese PIN no está disponible. Elige otro.']);

    expect($this->usuario->fresh()->pin_hash)->toBeNull();
});

it('stores the secret answer as a hash', function () {
    $this->actingAs($this->usuario)->put(route('cuenta.recuperacion'), [
        'password_actual' => 'password', 'telefono' => '+528711234567',
        'pregunta_secreta' => '¿Ciudad donde naciste?', 'respuesta_secreta' => 'Torreón',
    ])->assertSessionHas('estado', 'Datos de recuperación actualizados.');

    $guardado = $this->usuario->fresh();
    expect($guardado->telefono)->toBe('+528711234567');
    expect($guardado->respuesta_secreta_hash)->not->toContain('Torre');
    expect(Hash::check('torreón', $guardado->respuesta_secreta_hash))->toBeTrue();
});

it('rejects a phone number without international format', function () {
    $this->actingAs($this->usuario)->put(route('cuenta.recuperacion'), ['password_actual' => 'password', 'telefono' => '8711234567'])
        ->assertSessionHasErrorsIn('recuperacion', ['telefono' => 'Escribe el teléfono en formato internacional, por ejemplo +528711234567.']);
});

it('closes the other sessions after changing the password', function () {
    DB::table('sessions')->insert(['id' => 'otra', 'user_id' => $this->usuario->id_usuario, 'payload' => '', 'last_activity' => now()->timestamp]);

    $this->actingAs($this->usuario)->put(route('cuenta.password'), [
        'password_actual' => 'password', 'password' => 'CafeNueva2026', 'password_confirmation' => 'CafeNueva2026',
    ])->assertSessionHasNoErrors();

    expect(Hash::check('CafeNueva2026', $this->usuario->fresh()->password))->toBeTrue();
    $this->assertDatabaseMissing('sessions', ['id' => 'otra']);
});

it('rejects a weak new password', function () {
    $this->actingAs($this->usuario)->put(route('cuenta.password'), [
        'password_actual' => 'password', 'password' => 'corta', 'password_confirmation' => 'corta',
    ])->assertSessionHasErrorsIn('password', 'password');

    expect(Hash::check('password', $this->usuario->fresh()->password))->toBeTrue();
});

it('shows a form error only in the form that caused it', function () {
    $this->actingAs($this->usuario)->put(route('cuenta.pin'), ['pin' => '5831', 'pin_confirmation' => '5831', 'password_actual' => 'incorrecta']);

    $pagina = $this->get(route('cuenta.seguridad'))->assertOk()->getContent();

    expect(substr_count($pagina, 'La contraseña actual no es correcta.'))->toBe(1);
});
