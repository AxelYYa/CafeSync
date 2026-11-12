<?php

use App\Enums\TipoAsistencia;
use App\Models\Asistencia;
use App\Models\Usuario;

beforeEach(function () {
    $this->gerente = Usuario::factory()->gerente()->create();
    $this->brenda = Usuario::factory()->create(['nombre' => 'Brenda']);
    $this->carlos = Usuario::factory()->create(['nombre' => 'Carlos']);
});

function marca(Usuario $usuario, TipoAsistencia $tipo, string $fechaHora): void
{
    Asistencia::create(['id_usuario' => $usuario->id_usuario, 'tipo' => $tipo, 'metodo' => 'PIN', 'fecha_hora' => $fechaHora]);
}

it('pairs entries with exits and adds up the worked time of each employee', function () {
    marca($this->brenda, TipoAsistencia::Entrada, '2026-11-09 07:00:00');
    marca($this->brenda, TipoAsistencia::Salida, '2026-11-09 15:30:00');
    marca($this->brenda, TipoAsistencia::Entrada, '2026-11-10 07:05:00');
    marca($this->brenda, TipoAsistencia::Salida, '2026-11-10 11:05:00');
    marca($this->carlos, TipoAsistencia::Entrada, '2026-11-10 08:00:00');

    $response = $this->actingAs($this->gerente)->get(route('admin.asistencias.index', ['desde' => '2026-11-09', 'hasta' => '2026-11-10']));

    $response->assertOk()
        ->assertSeeInOrder(['Brenda', '12 h 30 min', '2 jornada(s)'])
        ->assertSeeInOrder(['Carlos', '0 h 00 min', '1 jornada(s)', '1 sin salida'])
        ->assertSee('8 h 30 min');
});

it('filters the report by employee', function () {
    marca($this->brenda, TipoAsistencia::Entrada, '2026-11-10 07:00:00');
    marca($this->carlos, TipoAsistencia::Entrada, '2026-11-10 08:00:00');

    $this->actingAs($this->gerente)->get(route('admin.asistencias.index', ['desde' => '2026-11-10', 'hasta' => '2026-11-10', 'id_usuario' => $this->carlos->id_usuario]))
        ->assertOk()
        ->assertSee('10/11/2026 08:00:00')
        ->assertDontSee('10/11/2026 07:00:00');
});

it('rejects a period that ends before it starts', function () {
    $this->actingAs($this->gerente)->get(route('admin.asistencias.index', ['desde' => '2026-11-10', 'hasta' => '2026-11-01']))
        ->assertSessionHasErrors('hasta');
});
