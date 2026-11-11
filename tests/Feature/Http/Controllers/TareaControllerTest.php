<?php

use App\Enums\EstadoTarea;
use App\Models\AsignacionTarea;
use App\Models\Usuario;

beforeEach(function () {
    $this->empleado = Usuario::factory()->empleado()->create();
});

it('marks the employee task as completed and records who and when', function () {
    $tarea = AsignacionTarea::factory()->create(['id_usuario_asignado' => $this->empleado->id_usuario]);
    $this->travelTo('2026-11-10 13:05:00');

    $this->actingAs($this->empleado)->post(route('tareas.completar', $tarea))
        ->assertSessionHas('estado', 'Tarea marcada como completada.');

    expect($tarea->fresh())
        ->estado->toBe(EstadoTarea::Completada)
        ->id_usuario_completo->toBe($this->empleado->id_usuario);
    expect($tarea->fresh()->completada_en->toDateTimeString())->toBe('2026-11-10 13:05:00');
});

it('lets any employee complete a general task of the shift', function () {
    $tarea = AsignacionTarea::factory()->create(['id_usuario_asignado' => null]);

    $this->actingAs($this->empleado)->post(route('tareas.completar', $tarea));

    expect($tarea->fresh()->estado)->toBe(EstadoTarea::Completada);
});

it('forbids an employee from completing a task assigned to someone else', function () {
    $tarea = AsignacionTarea::factory()->create(['id_usuario_asignado' => Usuario::factory()->empleado()->create()->id_usuario]);

    $this->actingAs($this->empleado)->post(route('tareas.completar', $tarea))->assertForbidden();

    expect($tarea->fresh()->estado)->toBe(EstadoTarea::Pendiente);
});

it('keeps the first completion when the task is marked again', function () {
    $tarea = AsignacionTarea::factory()->create(['id_usuario_asignado' => null]);
    $otro = Usuario::factory()->empleado()->create();
    $this->actingAs($this->empleado)->post(route('tareas.completar', $tarea));

    $this->actingAs($otro)->post(route('tareas.completar', $tarea));

    expect($tarea->fresh()->id_usuario_completo)->toBe($this->empleado->id_usuario);
});

it('renders the checklist without the complete button for tasks of other people', function () {
    AsignacionTarea::factory()->create(['id_usuario_asignado' => $this->empleado->id_usuario]);
    $ajena = AsignacionTarea::factory()->create(['id_usuario_asignado' => Usuario::factory()->empleado()->create()->id_usuario]);

    $this->actingAs($this->empleado)->get(route('tareas.index'))
        ->assertOk()
        ->assertSee('0 de 1 completadas')
        ->assertDontSee(route('tareas.completar', $ajena));
});
