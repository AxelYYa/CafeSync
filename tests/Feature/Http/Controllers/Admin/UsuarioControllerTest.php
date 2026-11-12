<?php

use App\Enums\NombreRol;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\PinService;

beforeEach(function () {
    $this->gerente = Usuario::factory()->gerente()->create(['nombre' => 'Gabriela Gerente']);
    $this->rolEmpleado = Rol::where('nombre', NombreRol::Empleado->value)->value('id_rol')
        ?? Rol::create(['nombre' => NombreRol::Empleado->value])->id_rol;
});

function datosUsuario(array $cambios = []): array
{
    return [
        'nombre' => 'Brenda Barista',
        'username' => 'brenda',
        'correo' => 'Brenda@CafeSync.test',
        'telefono' => '+528711234567',
        'id_rol' => test()->rolEmpleado,
        'activo' => '1',
        'password' => 'Inicial2026x',
        'password_confirmation' => 'Inicial2026x',
        'pin' => '4821',
        'pin_confirmation' => '4821',
        ...$cambios,
    ];
}

describe('index', function () {
    it('lists the accounts and filters them by text', function () {
        Usuario::factory()->create(['nombre' => 'Carlos Cajero']);

        $this->actingAs($this->gerente)->get(route('admin.usuarios.index', ['buscar' => 'Carlos']))
            ->assertOk()
            ->assertSee('Carlos Cajero')
            ->assertDontSee($this->gerente->correo);
    });
});

describe('store', function () {
    it('creates an employee who can sign in and clock in with the PIN', function () {
        $response = $this->actingAs($this->gerente)->post(route('admin.usuarios.store'), datosUsuario());

        $response->assertRedirect(route('admin.usuarios.index'))->assertSessionHas('estado', 'Cuenta de Brenda Barista creada.');
        $usuario = Usuario::where('username', 'brenda')->sole();
        expect($usuario)
            ->correo->toBe('brenda@cafesync.test')
            ->id_rol->toBe($this->rolEmpleado)
            ->activo->toBeTrue();
        expect($usuario->password)->not->toBe('Inicial2026x');
        expect(Hash::check('Inicial2026x', $usuario->password))->toBeTrue();
        expect(app(PinService::class)->resolver('4821')?->id_usuario)->toBe($usuario->id_usuario);

        auth()->logout();
        $this->post(route('login.store'), ['login' => 'brenda', 'password' => 'Inicial2026x'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);
    });

    it('requires the main fields', function () {
        $this->actingAs($this->gerente)->post(route('admin.usuarios.store'), [])
            ->assertSessionHasErrors(['nombre', 'username', 'correo', 'id_rol', 'password']);

        expect(Usuario::count())->toBe(1);
    });

    it('rejects a username or an email that already exists, ignoring case in the email', function () {
        Usuario::factory()->create(['username' => 'brenda', 'correo' => 'brenda@cafesync.test']);

        $this->actingAs($this->gerente)->post(route('admin.usuarios.store'), datosUsuario())
            ->assertSessionHasErrors(['username', 'correo']);

        expect(Usuario::count())->toBe(2);
    });

    it('rejects a weak initial password', function () {
        $this->actingAs($this->gerente)->post(route('admin.usuarios.store'), datosUsuario(['password' => 'corta', 'password_confirmation' => 'corta']))
            ->assertSessionHasErrors('password');

        expect(Usuario::where('username', 'brenda')->exists())->toBeFalse();
    });

    it('rejects a PIN already used by another employee', function () {
        Usuario::factory()->conPin('4821')->create();

        $this->actingAs($this->gerente)->post(route('admin.usuarios.store'), datosUsuario())
            ->assertSessionHasErrors(['pin' => 'Ese PIN ya lo usa otro empleado. Elige otro.']);

        expect(Usuario::where('username', 'brenda')->exists())->toBeFalse();
    });
});

describe('update', function () {
    beforeEach(function () {
        $this->empleado = Usuario::factory()->empleado()->create(['username' => 'carlos', 'correo' => 'carlos@cafesync.test']);
    });

    it('keeps the current password when the field is left empty', function () {
        $this->actingAs($this->gerente)->put(route('admin.usuarios.update', $this->empleado), datosUsuario([
            'username' => 'carlos', 'correo' => 'carlos@cafesync.test', 'nombre' => 'Carlos C.',
            'password' => '', 'password_confirmation' => '', 'pin' => '', 'pin_confirmation' => '',
        ]))->assertSessionHasNoErrors();

        expect($this->empleado->fresh()->nombre)->toBe('Carlos C.');
        expect(Hash::check('password', $this->empleado->fresh()->password))->toBeTrue();
    });

    it('closes the sessions and revokes the tokens of a deactivated account', function () {
        DB::table('sessions')->insert(['id' => 'caja', 'user_id' => $this->empleado->id_usuario, 'payload' => '', 'last_activity' => now()->timestamp]);
        $this->empleado->createToken('Tableta', ['kds:read']);

        $this->actingAs($this->gerente)->put(route('admin.usuarios.update', $this->empleado), datosUsuario([
            'username' => 'carlos', 'correo' => 'carlos@cafesync.test', 'activo' => '0',
            'password' => '', 'password_confirmation' => '', 'pin' => '', 'pin_confirmation' => '',
        ]))->assertSessionHasNoErrors();

        expect($this->empleado->fresh()->activo)->toBeFalse();
        $this->assertDatabaseMissing('sessions', ['id' => 'caja']);
        expect($this->empleado->tokens()->count())->toBe(0);
    });

    it('promotes an employee to manager', function () {
        $rolGerente = Rol::where('nombre', NombreRol::Gerente->value)->value('id_rol');

        $this->actingAs($this->gerente)->put(route('admin.usuarios.update', $this->empleado), datosUsuario([
            'username' => 'carlos', 'correo' => 'carlos@cafesync.test', 'id_rol' => $rolGerente,
            'password' => '', 'password_confirmation' => '', 'pin' => '', 'pin_confirmation' => '',
        ]));

        expect($this->empleado->fresh()->esGerente())->toBeTrue();
    });

    it('does not let the manager deactivate or demote their own account', function () {
        $this->actingAs($this->gerente)->put(route('admin.usuarios.update', $this->gerente), datosUsuario([
            'username' => $this->gerente->username, 'correo' => $this->gerente->correo, 'activo' => '0',
            'password' => '', 'password_confirmation' => '', 'pin' => '', 'pin_confirmation' => '',
        ]))->assertSessionHasErrors(['usuario' => 'No puedes quitarte el rol de gerente ni desactivar tu propia cuenta.']);

        expect($this->gerente->fresh())->activo->toBeTrue()->and($this->gerente->fresh()->esGerente())->toBeTrue();
    });

    it('renders the edit form without exposing the stored hashes', function () {
        $this->empleado->forceFill(['pin_hash' => Hash::make('1234')])->save();

        $this->actingAs($this->gerente)->get(route('admin.usuarios.edit', $this->empleado))
            ->assertOk()
            ->assertSee('carlos@cafesync.test')
            ->assertDontSee($this->empleado->fresh()->pin_hash)
            ->assertDontSee($this->empleado->fresh()->password);
    });
});
