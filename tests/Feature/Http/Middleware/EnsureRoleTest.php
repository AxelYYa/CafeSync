<?php

use App\Models\Usuario;

/*
| CP-02 / RF-12 / RNF-02: las rutas administrativas se protegen en el servidor, no solo ocultando el menú.
*/

dataset('pantallas administrativas', [
    'usuarios' => ['admin.usuarios.index'],
    'alta de usuario' => ['admin.usuarios.create'],
    'asistencias' => ['admin.asistencias.index'],
    'tokens' => ['admin.tokens.index'],
    'estaciones' => ['admin.estaciones.index'],
    'alta de estación' => ['admin.estaciones.create'],
]);

it('lets the manager open the administrative screens', function (string $ruta) {
    $this->actingAs(Usuario::factory()->gerente()->create())->get(route($ruta))->assertOk();
})->with('pantallas administrativas');

it('returns 403 to an employee who opens the address directly', function (string $ruta) {
    $this->actingAs(Usuario::factory()->empleado()->create())->get(route($ruta))
        ->assertForbidden()
        ->assertSee('No tienes permiso');
})->with('pantallas administrativas');

it('sends guests to the login', function (string $ruta) {
    $this->get(route($ruta))->assertRedirect(route('login'));
})->with('pantallas administrativas');

it('blocks an employee who posts an administrative form directly', function () {
    $empleado = Usuario::factory()->empleado()->create();

    $this->actingAs($empleado)->post(route('admin.usuarios.store'), [
        'nombre' => 'Intruso', 'username' => 'intruso', 'correo' => 'intruso@cafesync.test',
        'id_rol' => $empleado->id_rol, 'password' => 'Intruso2026x', 'password_confirmation' => 'Intruso2026x',
    ])->assertForbidden();
    $this->actingAs($empleado)->post(route('admin.tokens.store'), ['nombre' => 'X', 'id_usuario' => $empleado->id_usuario, 'habilidades' => ['kds:read']])
        ->assertForbidden();

    expect(Usuario::where('username', 'intruso')->exists())->toBeFalse();
    expect($empleado->tokens()->count())->toBe(0);
});

it('hides the administration menu from employees', function () {
    $this->actingAs(Usuario::factory()->empleado()->create())->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(route('admin.usuarios.index'));
});
