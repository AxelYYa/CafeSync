<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id('id_asistencia');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario');
            $table->foreignId('id_turno')->nullable()->constrained('turnos', 'id_turno');
            $table->string('tipo', 15);
            $table->string('metodo', 15)->default('PIN');
            $table->dateTime('fecha_hora');
            $table->string('nota', 255)->nullable();

            $table->index(['id_usuario', 'fecha_hora']);
        });

        Schema::create('plantillas_tareas', function (Blueprint $table) {
            $table->id('id_plantilla');
            $table->foreignId('id_usuario_creador')->constrained('usuarios', 'id_usuario');
            $table->string('titulo', 120);
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('asignaciones_tareas', function (Blueprint $table) {
            $table->id('id_asignacion');
            $table->foreignId('id_plantilla')->constrained('plantillas_tareas', 'id_plantilla');
            $table->foreignId('id_turno')->nullable()->constrained('turnos', 'id_turno');
            $table->foreignId('id_estacion')->nullable()->constrained('estaciones', 'id_estacion');
            $table->foreignId('id_usuario_asignado')->nullable()->constrained('usuarios', 'id_usuario');
            $table->foreignId('id_usuario_asigno')->constrained('usuarios', 'id_usuario');
            $table->foreignId('id_usuario_completo')->nullable()->constrained('usuarios', 'id_usuario');
            $table->string('estado', 20)->default('pendiente');
            $table->dateTime('fecha_limite')->nullable();
            $table->dateTime('completada_en')->nullable();
            $table->timestamps();

            $table->index(['estado', 'fecha_limite']);
        });

        Schema::create('recuperaciones_password', function (Blueprint $table) {
            $table->id('id_recuperacion');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario')->cascadeOnDelete();
            $table->string('canal', 20);
            $table->string('codigo_hash', 255)->nullable();
            $table->string('token_hash', 64)->nullable()->unique();
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->dateTime('expira_en');
            $table->dateTime('verificado_en')->nullable();
            $table->dateTime('usado_en')->nullable();
            $table->timestamps();

            $table->index(['id_usuario', 'canal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recuperaciones_password');
        Schema::dropIfExists('asignaciones_tareas');
        Schema::dropIfExists('plantillas_tareas');
        Schema::dropIfExists('asistencias');
    }
};
