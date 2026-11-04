<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estaciones', function (Blueprint $table) {
            $table->id('id_estacion');
            $table->string('nombre', 80)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto');
            $table->foreignId('id_estacion')->constrained('estaciones', 'id_estacion');
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['activo', 'nombre']);
        });

        Schema::create('ingredientes', function (Blueprint $table) {
            $table->id('id_ingrediente');
            $table->string('nombre', 120)->unique();
            $table->string('unidad_medida', 15);
            $table->decimal('stock_actual', 12, 3)->default(0);
            $table->decimal('stock_minimo', 12, 3)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('recetas', function (Blueprint $table) {
            $table->id('id_receta');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto');
            $table->unsignedSmallInteger('version');
            $table->text('instrucciones')->nullable();
            $table->boolean('activa')->default(true);
            $table->foreignId('id_usuario_creador')->nullable()->constrained('usuarios', 'id_usuario');
            $table->timestamps();

            $table->unique(['id_producto', 'version']);
        });

        Schema::create('detalle_recetas', function (Blueprint $table) {
            $table->id('id_detalle_receta');
            $table->foreignId('id_receta')->constrained('recetas', 'id_receta')->cascadeOnDelete();
            $table->foreignId('id_ingrediente')->constrained('ingredientes', 'id_ingrediente');
            $table->decimal('cantidad', 12, 3);
            $table->timestamps();

            $table->unique(['id_receta', 'id_ingrediente']);
        });

        Schema::create('turnos', function (Blueprint $table) {
            $table->id('id_turno');
            $table->string('nombre', 60)->unique();
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos');
        Schema::dropIfExists('detalle_recetas');
        Schema::dropIfExists('recetas');
        Schema::dropIfExists('ingredientes');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('estaciones');
    }
};
