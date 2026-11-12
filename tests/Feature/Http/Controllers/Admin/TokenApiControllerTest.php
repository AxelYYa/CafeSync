<?php

use App\Models\DetalleOrden;
use App\Models\Estacion;
use App\Models\Usuario;
use Laravel\Sanctum\PersonalAccessToken;

beforeEach(function () {
    $this->gerente = Usuario::factory()->gerente()->create();
    $this->pantalla = Usuario::factory()->create(['nombre' => 'Pantalla barra']);
    $this->estacion = Estacion::factory()->create();
});

function crearTokenDesdePanel(array $datos): string
{
    test()->actingAs(test()->gerente)->post(route('admin.tokens.store'), $datos)->assertSessionHasNoErrors();
    $textoPlano = session('token_nuevo');
    auth()->logout();
    app('auth')->forgetGuards();

    return $textoPlano;
}

it('issues a token that authenticates the KDS API with only the chosen permissions', function () {
    $this->freezeTime();

    $textoPlano = crearTokenDesdePanel(['nombre' => 'Tableta barra', 'id_usuario' => $this->pantalla->id_usuario, 'habilidades' => ['kds:read']]);

    $token = PersonalAccessToken::sole();
    expect($token)
        ->name->toBe('Tableta barra')
        ->abilities->toBe(['kds:read'])
        ->tokenable_id->toBe($this->pantalla->id_usuario);
    expect($token->expires_at->toDateTimeString())->toBe(now()->addMinutes(43200)->toDateTimeString());
    expect($token->token)->not->toBe(explode('|', $textoPlano)[1]);
    $this->withToken($textoPlano)->getJson(route('api.kds.ordenes', $this->estacion))->assertOk();
    $this->withToken($textoPlano)->patchJson(route('api.kds.estado', DetalleOrden::factory()->create()), ['estado' => 'en_preparacion'])->assertForbidden();
});

it('stops accepting a token once it is revoked', function () {
    $textoPlano = crearTokenDesdePanel(['nombre' => 'Tableta', 'id_usuario' => $this->pantalla->id_usuario, 'habilidades' => ['kds:read']]);

    $this->actingAs($this->gerente)->delete(route('admin.tokens.destroy', PersonalAccessToken::sole()))
        ->assertSessionHas('estado', 'Token «Tableta» revocado; el dispositivo ya no podrá usarlo.');
    auth()->logout();
    app('auth')->forgetGuards();

    $this->withToken($textoPlano)->getJson(route('api.kds.ordenes', $this->estacion))->assertUnauthorized();
});

it('rejects an expired token', function () {
    $textoPlano = crearTokenDesdePanel(['nombre' => 'Tableta', 'id_usuario' => $this->pantalla->id_usuario, 'habilidades' => ['kds:read']]);
    $this->travel(31)->days();

    $this->withToken($textoPlano)->getJson(route('api.kds.ordenes', $this->estacion))->assertUnauthorized();
});

it('requires at least one known permission and an active account', function () {
    $inactivo = Usuario::factory()->inactivo()->create();

    $this->actingAs($this->gerente)->post(route('admin.tokens.store'), ['nombre' => 'X', 'id_usuario' => $inactivo->id_usuario, 'habilidades' => ['admin:todo']])
        ->assertSessionHasErrors(['id_usuario', 'habilidades.0']);
    $this->post(route('admin.tokens.store'), ['nombre' => 'X', 'id_usuario' => $this->pantalla->id_usuario])
        ->assertSessionHasErrors(['habilidades' => 'Elige al menos un permiso para el token.']);

    expect(PersonalAccessToken::count())->toBe(0);
});

it('shows the secret only on the screen right after creating the token', function () {
    $textoPlano = crearTokenDesdePanel(['nombre' => 'Tableta cocina', 'id_usuario' => $this->pantalla->id_usuario, 'habilidades' => ['kds:read', 'kds:update']]);
    $secreto = explode('|', $textoPlano)[1];

    $primeraVisita = $this->actingAs($this->gerente)->get(route('admin.tokens.index'));
    $segundaVisita = $this->get(route('admin.tokens.index'));

    $primeraVisita->assertOk()->assertSee($secreto);
    $segundaVisita->assertOk()
        ->assertSee('Tableta cocina')
        ->assertSee('Pantalla barra')
        ->assertSee('kds:read, kds:update')
        ->assertDontSee($secreto);
});
