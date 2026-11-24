<?php

use App\Services\Mfa\TotpService;

// Vectores de prueba del RFC 6238 (SHA-1) truncados a 6 dígitos; secreto ASCII "12345678901234567890".
const SECRETO_RFC_6238 = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

it('generates the RFC 6238 reference codes', function (int $marcaTiempo, string $esperado) {
    expect((new TotpService)->codigo(SECRETO_RFC_6238, $marcaTiempo))->toBe($esperado);
})->with([
    [59, '287082'],
    [1111111109, '081804'],
    [1234567890, '005924'],
    [2000000000, '279037'],
]);

it('accepts a code from the previous period to tolerate clock drift', function () {
    expect((new TotpService)->verificar(SECRETO_RFC_6238, '287082', marcaTiempo: 89))->toBe(1);
});

it('rejects a code outside the tolerance window', function () {
    expect((new TotpService)->verificar(SECRETO_RFC_6238, '287082', marcaTiempo: 150))->toBeNull();
});

it('rejects a code with the wrong length', function () {
    expect((new TotpService)->verificar(SECRETO_RFC_6238, '28708', marcaTiempo: 59))->toBeNull();
});
