<?php

use App\Http\Controllers\Api\KdsApiController;
use Illuminate\Support\Facades\Route;

/*
| API para pantallas KDS autenticadas con token personal (Laravel Sanctum).
| Es un mecanismo distinto de la sesión web: el token se emite a un dispositivo,
| lleva habilidades limitadas (kds:read, kds:update) y no abre el panel administrativo.
*/
Route::prefix('v1')->middleware(['auth:sanctum', 'activo', 'throttle:120,1'])->group(function () {
    Route::get('kds/estaciones/{estacion}/ordenes', [KdsApiController::class, 'ordenes'])
        ->middleware('abilities:kds:read')
        ->name('api.kds.ordenes');

    Route::patch('kds/detalles/{detalle}/estado', [KdsApiController::class, 'avanzar'])
        ->middleware('abilities:kds:update')
        ->name('api.kds.estado');
});
