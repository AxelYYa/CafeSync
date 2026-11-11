<?php

use App\Models\Usuario;
use App\Services\Mfa\MfaService;
use App\Services\Mfa\TotpService;

beforeEach(function () {
    $this->usuario = Usuario::factory()->create();
    $this->mfa = app(MfaService::class);
    $this->totp = app(TotpService::class);
});

it('activates only after the first code is confirmed', function () {
    $secreto = $this->mfa->iniciarActivacion($this->usuario);
    $pendiente = $this->usuario->fresh()->tieneMfaActivo();

    $codigos = $this->mfa->confirmarActivacion($this->usuario, $this->totp->codigo($secreto));

    expect($pendiente)->toBeFalse();
    expect($codigos)->toHaveCount(8);
    expect($this->usuario->fresh()->tieneMfaActivo())->toBeTrue();
});

it('does not store the secret or the recovery codes in plain text', function () {
    $secreto = $this->mfa->iniciarActivacion($this->usuario);
    $codigos = $this->mfa->confirmarActivacion($this->usuario, $this->totp->codigo($secreto));

    $fila = DB::table('usuarios')->where('id_usuario', $this->usuario->id_usuario)->first();

    expect($fila->mfa_secret)->not->toContain($secreto);
    expect($fila->mfa_recovery_codes)->not->toContain($codigos[0]);
});

it('rejects a wrong confirmation code', function () {
    $this->mfa->iniciarActivacion($this->usuario);

    expect($this->mfa->confirmarActivacion($this->usuario, '000000'))->toBeNull();
    expect($this->usuario->fresh()->tieneMfaActivo())->toBeFalse();
});

it('does not accept the same authenticator code twice', function () {
    $secreto = $this->mfa->iniciarActivacion($this->usuario);
    $this->mfa->confirmarActivacion($this->usuario, $this->totp->codigo($secreto));
    $codigo = $this->totp->codigo($secreto);

    $primero = $this->mfa->verificarDesafio($this->usuario, $codigo);
    $segundo = $this->mfa->verificarDesafio($this->usuario, $codigo);

    expect($primero)->toBeTrue()->and($segundo)->toBeFalse();
});

it('accepts each recovery code only once', function () {
    $secreto = $this->mfa->iniciarActivacion($this->usuario);
    $codigos = $this->mfa->confirmarActivacion($this->usuario, $this->totp->codigo($secreto));

    $primero = $this->mfa->verificarDesafio($this->usuario, strtolower($codigos[0]));
    $segundo = $this->mfa->verificarDesafio($this->usuario->fresh(), $codigos[0]);

    expect($primero)->toBeTrue()->and($segundo)->toBeFalse();
    expect($this->mfa->codigosRecuperacionRestantes($this->usuario->fresh()))->toBe(7);
});
