<?php

use App\Http\Controllers\Admin\AsignacionTareaController;
use App\Http\Controllers\Admin\AsistenciaReporteController;
use App\Http\Controllers\Admin\EstacionController;
use App\Http\Controllers\Admin\IngredienteController;
use App\Http\Controllers\Admin\MovimientoInventarioController;
use App\Http\Controllers\Admin\PlantillaTareaController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\RecetaAdminController;
use App\Http\Controllers\Admin\ReporteVentasController;
use App\Http\Controllers\Admin\TokenApiController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RecuperacionPasswordController;
use App\Http\Controllers\AyudaController;
use App\Http\Controllers\Cuenta\MfaController;
use App\Http\Controllers\Cuenta\SeguridadController;
use App\Http\Controllers\Cuenta\SesionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KdsController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\TareaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/panel');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:30,1')->name('login.store');

    Route::get('/mfa/desafio', [LoginController::class, 'desafioMfa'])->name('mfa.desafio');
    Route::post('/mfa/desafio', [LoginController::class, 'verificarMfa'])->middleware('throttle:30,1')->name('mfa.verificar');

    Route::get('/recuperar', [RecuperacionPasswordController::class, 'create'])->name('recuperacion.solicitar');
    Route::post('/recuperar', [RecuperacionPasswordController::class, 'store'])->name('recuperacion.enviar');
    Route::get('/recuperar/verificar', [RecuperacionPasswordController::class, 'formularioVerificacion'])->name('recuperacion.verificar');
    Route::post('/recuperar/verificar', [RecuperacionPasswordController::class, 'verificar'])->name('recuperacion.comprobar');
    Route::get('/recuperar/nueva-contrasena', [RecuperacionPasswordController::class, 'formularioNuevaContrasena'])->name('recuperacion.nueva');
    Route::post('/recuperar/nueva-contrasena', [RecuperacionPasswordController::class, 'restablecer'])->name('recuperacion.restablecer');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/panel', DashboardController::class)->name('dashboard');

    Route::prefix('cuenta')->group(function () {
        Route::get('/seguridad', [SeguridadController::class, 'index'])->name('cuenta.seguridad');
        Route::put('/password', [SeguridadController::class, 'actualizarPassword'])->name('cuenta.password');
        Route::put('/recuperacion', [SeguridadController::class, 'actualizarRecuperacion'])->name('cuenta.recuperacion');
        Route::put('/pin', [SeguridadController::class, 'actualizarPin'])->name('cuenta.pin');

        Route::post('/mfa', [MfaController::class, 'iniciar'])->name('mfa.iniciar');
        Route::post('/mfa/confirmar', [MfaController::class, 'confirmar'])->name('mfa.confirmar');
        Route::post('/mfa/codigos', [MfaController::class, 'regenerarCodigos'])->name('mfa.codigos');
        Route::delete('/mfa', [MfaController::class, 'desactivar'])->name('mfa.desactivar');

        Route::delete('/sesiones', [SesionController::class, 'destroyOtras'])->name('sesiones.destroy-otras');
        Route::delete('/sesiones/{sesion}', [SesionController::class, 'destroy'])->name('sesiones.destroy');
    });

    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/ordenes', [PosController::class, 'store'])->name('pos.ordenes.store');
    Route::get('/pos/ordenes', [OrdenController::class, 'index'])->name('ordenes.index');
    Route::get('/pos/ordenes/{orden}', [OrdenController::class, 'show'])->name('ordenes.show');
    Route::post('/pos/ordenes/{orden}/entregar', [OrdenController::class, 'entregar'])->name('ordenes.entregar');
    Route::post('/pos/ordenes/{orden}/cerrar', [OrdenController::class, 'cerrar'])->name('ordenes.cerrar');
    Route::post('/pos/ordenes/{orden}/cancelar', [OrdenController::class, 'cancelar'])->name('ordenes.cancelar');
    Route::post('/pos/ordenes/{orden}/revertir', [OrdenController::class, 'revertir'])->middleware('rol:gerente')->name('ordenes.revertir');

    Route::get('/kds', [KdsController::class, 'index'])->name('kds.index');
    Route::get('/kds/{estacion}', [KdsController::class, 'show'])->name('kds.show');
    Route::get('/kds/{estacion}/datos', [KdsController::class, 'datos'])->name('kds.datos');
    Route::patch('/kds/detalles/{detalle}/estado', [KdsController::class, 'avanzar'])->name('kds.estado');

    Route::get('/ayuda/productos/{producto}', [AyudaController::class, 'show'])->name('ayuda.show');

    Route::get('/reloj', [AsistenciaController::class, 'index'])->name('asistencia.index');
    Route::post('/reloj', [AsistenciaController::class, 'store'])->name('asistencia.store');

    Route::get('/tareas', [TareaController::class, 'index'])->name('tareas.index');
    Route::post('/tareas/{asignacion}/completar', [TareaController::class, 'completar'])->name('tareas.completar');

    Route::middleware('rol:gerente')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
        Route::resource('estaciones', EstacionController::class)->except(['show', 'destroy'])->parameters(['estaciones' => 'estacion']);
        Route::resource('productos', ProductoController::class)->except(['show', 'destroy']);
        Route::get('productos/{producto}/receta', [RecetaAdminController::class, 'edit'])->name('recetas.edit');
        Route::put('productos/{producto}/receta', [RecetaAdminController::class, 'update'])->name('recetas.update');

        Route::resource('ingredientes', IngredienteController::class)->except(['show', 'destroy']);
        Route::get('inventario', [MovimientoInventarioController::class, 'index'])->name('inventario.index');
        Route::post('ingredientes/{ingrediente}/movimientos', [MovimientoInventarioController::class, 'store'])->name('inventario.store');

        Route::resource('plantillas-tareas', PlantillaTareaController::class)->except(['show', 'destroy'])->parameters(['plantillas-tareas' => 'plantilla'])->names('plantillas');
        Route::get('asignaciones', [AsignacionTareaController::class, 'index'])->name('asignaciones.index');
        Route::get('asignaciones/crear', [AsignacionTareaController::class, 'create'])->name('asignaciones.create');
        Route::post('asignaciones', [AsignacionTareaController::class, 'store'])->name('asignaciones.store');
        Route::post('asignaciones/{asignacion}/reabrir', [AsignacionTareaController::class, 'reabrir'])->name('asignaciones.reabrir');

        Route::get('asistencias', [AsistenciaReporteController::class, 'index'])->name('asistencias.index');

        Route::get('reportes/ventas', [ReporteVentasController::class, 'index'])->name('reportes.ventas');
        Route::get('reportes/ventas/exportar', [ReporteVentasController::class, 'exportar'])->name('reportes.ventas.exportar');

        Route::get('tokens', [TokenApiController::class, 'index'])->name('tokens.index');
        Route::post('tokens', [TokenApiController::class, 'store'])->name('tokens.store');
        Route::delete('tokens/{token}', [TokenApiController::class, 'destroy'])->name('tokens.destroy');
    });
});
