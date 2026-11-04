<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id('id_orden');
            $table->string('folio', 30)->unique();
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario');
            $table->string('canal_origen', 20);
            $table->string('estado', 30);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('metodo_pago_registrado', 25)->nullable();
            $table->text('nota')->nullable();
            $table->dateTime('cerrada_en')->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
            $table->index('canal_origen');
        });

        Schema::create('detalle_ordenes', function (Blueprint $table) {
            $table->id('id_detalle_orden');
            $table->foreignId('id_orden')->constrained('ordenes', 'id_orden')->cascadeOnDelete();
            $table->foreignId('id_producto')->constrained('productos', 'id_producto');
            $table->foreignId('id_estacion')->constrained('estaciones', 'id_estacion');
            $table->foreignId('id_receta_aplicada')->nullable()->constrained('recetas', 'id_receta');
            $table->string('nombre_producto_snapshot', 120);
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->string('estado_preparacion', 25);
            $table->timestamps();

            $table->index(['id_estacion', 'estado_preparacion']);
        });

        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->foreignId('id_ingrediente')->constrained('ingredientes', 'id_ingrediente');
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario');
            $table->foreignId('id_detalle_orden')->nullable()->constrained('detalle_ordenes', 'id_detalle_orden');
            $table->string('tipo', 20);
            $table->boolean('incrementa');
            $table->decimal('cantidad', 12, 3);
            $table->decimal('stock_resultante', 12, 3);
            $table->string('motivo', 255)->nullable();
            $table->dateTime('fecha_movimiento');
            $table->timestamp('created_at')->nullable();

            $table->unique(['id_detalle_orden', 'id_ingrediente', 'tipo'], 'movimientos_consumo_unico');
            $table->index(['id_ingrediente', 'fecha_movimiento']);
        });

        Schema::create('historial_estados_orden', function (Blueprint $table) {
            $table->id('id_historial');
            $table->foreignId('id_orden')->constrained('ordenes', 'id_orden')->cascadeOnDelete();
            $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario');
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30);
            $table->text('observacion')->nullable();
            $table->dateTime('fecha_cambio');

            $table->index(['id_orden', 'fecha_cambio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estados_orden');
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('detalle_ordenes');
        Schema::dropIfExists('ordenes');
    }
};
