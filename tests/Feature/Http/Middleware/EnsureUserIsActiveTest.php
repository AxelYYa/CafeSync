<?php

use App\Models\Usuario;

it('signs out an account that was deactivated during its session', function () {
    $usuario = Usuario::factory()->create();
    $this->actingAs($usuario);
    $usuario->update(['activo' => false]);

    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'))
        ->assertSessionHasErrors(['login' => 'Tu cuenta está desactivada. Contacta al gerente.']);
    $this->assertGuest();
});

it('returns 403 to an API request from a deactivated account', function () {
    $usuario = Usuario::factory()->inactivo()->create();

    $this->actingAs($usuario)->getJson(route('kds.index'))->assertForbidden();
});
