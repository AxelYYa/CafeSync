<?php

use App\Services\Recuperacion\RecuperacionPorPregunta;
use App\Services\Sesiones\SesionService;

it('normalizes the secret answer so case and extra spaces do not matter', function () {
    expect(RecuperacionPorPregunta::normalizar('  FIRULAIS   el   Perro '))->toBe('firulais el perro')
        ->and(RecuperacionPorPregunta::normalizar('Torreón'))->toBe('torreón');
});

it('describes the device of a session from its user agent', function (string $agente, array $esperado) {
    expect((new SesionService)->interpretarAgente($agente))->toBe($esperado);
})->with([
    'Chrome en Windows' => [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130.0 Safari/537.36',
        ['dispositivo' => 'Computadora', 'navegador' => 'Chrome', 'sistema' => 'Windows'],
    ],
    'Edge en Windows' => [
        'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 Chrome/130.0 Safari/537.36 Edg/130.0',
        ['dispositivo' => 'Computadora', 'navegador' => 'Edge', 'sistema' => 'Windows'],
    ],
    'Safari en iPhone' => [
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Version/17.0 Mobile/15E148 Safari/604.1',
        ['dispositivo' => 'Móvil o tableta', 'navegador' => 'Safari', 'sistema' => 'iOS'],
    ],
    'Firefox en Android' => [
        'Mozilla/5.0 (Android 14; Mobile; rv:131.0) Gecko/131.0 Firefox/131.0',
        ['dispositivo' => 'Móvil o tableta', 'navegador' => 'Firefox', 'sistema' => 'Android'],
    ],
    'desconocido' => ['curl/8.4.0', ['dispositivo' => 'Computadora', 'navegador' => 'Desconocido', 'sistema' => 'Desconocido']],
]);
