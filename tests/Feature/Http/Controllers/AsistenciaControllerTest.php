<?php

use App\Enums\TipoAsistencia;
use App\Models\Asistencia;
use App\Models\Turno;
use App\Models\Usuario;

beforeEach(function () {
    $this->terminal = Usuario::factory()->create();
    $this->empleado = Usuario::factory()->conPin('4821')->create(['nombre' => 'Brenda']);
});

it('records entry and exit for the PIN owner with the server time', function () {
    $turno = Turno::factory()->create(['hora_inicio' => '07:00:00', 'hora_fin' => '15:00:00']);
    $this->travelTo('2026-11-10 08:15:00');

    $this->actingAs($this->terminal)->post(route('asistencia.store'), [
        'pin' => '4821', 'tipo' => 'entrada', 'fecha_hora' => '2020-01-01 00:00:00',
    ])->assertRedirect(route('asistencia.index'))
        ->assertSessionHas('estado', 'Entrada registrada para Brenda a las 08:15:00.');
    $this->travelTo('2026-11-10 14:50:00');
    $this->post(route('asistencia.store'), ['pin' => '4821', 'tipo' => 'salida']);

    $marcas = Asistencia::orderBy('id_asistencia')->get();
    expect($marcas)->toHaveCount(2);
    expect($marcas[0])
        ->id_usuario->toBe($this->empleado->id_usuario)
        ->tipo->toBe(TipoAsistencia::Entrada)
        ->id_turno->toBe($turno->id_turno);
    expect($marcas[0]->fecha_hora->toDateTimeString())->toBe('2026-11-10 08:15:00');
    expect($marcas[1]->tipo)->toBe(TipoAsistencia::Salida);
    expect($marcas[1]->fecha_hora->toDateTimeString())->toBe('2026-11-10 14:50:00');
});

it('rejects an unknown PIN without creating a record', function () {
    $response = $this->actingAs($this->terminal)->post(route('asistencia.store'), ['pin' => '0000', 'tipo' => 'entrada']);

    $response->assertSessionHasErrors(['pin' => 'PIN no válido.']);
    expect(Asistencia::count())->toBe(0);
});

it('rejects the PIN of an inactive employee', function () {
    Usuario::factory()->conPin('7777')->inactivo()->create();

    $this->actingAs($this->terminal)->post(route('asistencia.store'), ['pin' => '7777', 'tipo' => 'entrada'])
        ->assertSessionHasErrors(['pin' => 'PIN no válido.']);

    expect(Asistencia::count())->toBe(0);
});

it('rejects an exit without an open entry', function () {
    $this->actingAs($this->terminal)->post(route('asistencia.store'), ['pin' => '4821', 'tipo' => 'salida'])
        ->assertSessionHasErrors(['pin' => 'No hay una entrada abierta. Registra primero tu entrada.']);

    expect(Asistencia::count())->toBe(0);
});

it('blocks PIN attempts after five failures', function () {
    $this->actingAs($this->terminal);
    foreach (range(1, 5) as $intento) {
        $this->post(route('asistencia.store'), ['pin' => '0000', 'tipo' => 'entrada']);
    }

    $response = $this->post(route('asistencia.store'), ['pin' => '4821', 'tipo' => 'entrada']);

    $response->assertSessionHasErrors('pin');
    expect(session('errors')->first('pin'))->toStartWith('Demasiados PIN incorrectos.');
    expect(Asistencia::count())->toBe(0);
});

it('validates the PIN format', function () {
    $this->actingAs($this->terminal)->post(route('asistencia.store'), ['pin' => '12ab', 'tipo' => 'descanso'])
        ->assertSessionHasErrors(['pin', 'tipo']);

    expect(Asistencia::count())->toBe(0);
});

it('redirects guests to the login', function () {
    $this->post(route('asistencia.store'), ['pin' => '4821', 'tipo' => 'entrada'])
        ->assertRedirect(route('login'));

    expect(Asistencia::count())->toBe(0);
});

it('renders the clock with the records of the day', function () {
    $this->actingAs($this->terminal)->post(route('asistencia.store'), ['pin' => '4821', 'tipo' => 'entrada']);

    $this->get(route('asistencia.index'))
        ->assertOk()
        ->assertSee('Marcas de hoy')
        ->assertSee('Brenda');
});
