<?php

namespace App\Http\Controllers\Cuenta;

use App\Http\Controllers\Controller;
use App\Services\Mfa\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class MfaController extends Controller
{
    public function __construct(private readonly MfaService $mfa) {}

    public function iniciar(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        if ($usuario->tieneMfaActivo()) {
            return back()->withErrors(['mfa' => 'La verificación en dos pasos ya está activa.']);
        }

        $this->mfa->iniciarActivacion($usuario);

        return redirect()->route('cuenta.seguridad')->withFragment('mfa');
    }

    public function confirmar(Request $request): RedirectResponse
    {
        $datos = $request->validate(['codigo' => ['required', 'string', 'max:10']]);

        $clave = 'mfa-activar|'.$request->user()->id_usuario;

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            throw ValidationException::withMessages(['codigo' => 'Demasiados intentos. Espera unos minutos.']);
        }

        $codigos = $this->mfa->confirmarActivacion($request->user(), $datos['codigo']);

        if ($codigos === null) {
            RateLimiter::hit($clave, 300);

            throw ValidationException::withMessages(['codigo' => 'El código no es válido. Revisa la hora de tu dispositivo.']);
        }

        RateLimiter::clear($clave);

        return redirect()->route('cuenta.seguridad')
            ->with('estado', 'Verificación en dos pasos activada. Guarda tus códigos de recuperación.')
            ->with('codigos_recuperacion', $codigos);
    }

    public function regenerarCodigos(Request $request): RedirectResponse
    {
        $request->validate(['password_actual' => ['required', 'current_password']]);

        if (! $request->user()->tieneMfaActivo()) {
            return back()->withErrors(['mfa' => 'Activa primero la verificación en dos pasos.']);
        }

        return redirect()->route('cuenta.seguridad')
            ->with('estado', 'Se generaron nuevos códigos de recuperación; los anteriores ya no sirven.')
            ->with('codigos_recuperacion', $this->mfa->regenerarCodigosRecuperacion($request->user()));
    }

    public function desactivar(Request $request): RedirectResponse
    {
        $request->validate(['password_actual' => ['required', 'current_password']]);

        $this->mfa->desactivar($request->user());

        return redirect()->route('cuenta.seguridad')->with('estado', 'Verificación en dos pasos desactivada.');
    }
}
