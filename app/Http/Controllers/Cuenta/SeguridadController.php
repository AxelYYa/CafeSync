<?php

namespace App\Http\Controllers\Cuenta;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\Mfa\MfaService;
use App\Services\PinService;
use App\Services\Recuperacion\RecuperacionPorPregunta;
use App\Services\Sesiones\SesionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SeguridadController extends Controller
{
    public function __construct(
        private readonly SesionService $sesiones,
        private readonly MfaService $mfa,
        private readonly PinService $pines,
    ) {}

    public function index(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();

        return view('cuenta.seguridad', [
            'usuario' => $usuario,
            'sesiones' => $this->sesiones->listar($usuario, $request->session()->getId()),
            'sesionesDisponibles' => $this->sesiones->usaBaseDeDatos(),
            'codigosRestantes' => $this->mfa->codigosRecuperacionRestantes($usuario),
            'secretoMfa' => $usuario->mfa_secret !== null && ! $usuario->tieneMfaActivo() ? $usuario->mfa_secret : null,
            'uriMfa' => $usuario->mfa_secret !== null && ! $usuario->tieneMfaActivo() ? $this->mfa->uriAprovisionamiento($usuario) : null,
        ]);
    }

    public function actualizarPassword(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'password_actual' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:password_actual', Password::defaults()],
        ]);

        $request->user()->forceFill(['password' => $datos['password']])->save();

        $cerradas = $this->sesiones->cerrarTodasExcepto($request->user(), $request->session()->getId());

        return back()->with('estado', "Contraseña actualizada. Se cerraron {$cerradas} sesión(es) en otros dispositivos.");
    }

    public function actualizarRecuperacion(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'password_actual' => ['required', 'current_password'],
            'telefono' => ['nullable', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'pregunta_secreta' => ['nullable', 'string', 'min:8', 'max:160', 'required_with:respuesta_secreta'],
            'respuesta_secreta' => ['nullable', 'string', 'min:3', 'max:120', 'required_with:pregunta_secreta'],
        ], [
            'telefono.regex' => 'Escribe el teléfono en formato internacional, por ejemplo +528711234567.',
        ]);

        $cambios = ['telefono' => $datos['telefono'] ?? null];

        if (filled($datos['pregunta_secreta'] ?? null)) {
            $cambios['pregunta_secreta'] = $datos['pregunta_secreta'];
            $cambios['respuesta_secreta_hash'] = Hash::make(RecuperacionPorPregunta::normalizar($datos['respuesta_secreta']));
        }

        $usuario->forceFill($cambios)->save();

        return back()->with('estado', 'Datos de recuperación actualizados.');
    }

    public function actualizarPin(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'password_actual' => ['required', 'current_password'],
            'pin' => ['required', 'digits_between:4,6', 'confirmed'],
        ]);

        if (! $this->pines->pinDisponible($datos['pin'], $request->user()->id_usuario)) {
            return back()->withErrors(['pin' => 'Ese PIN no está disponible. Elige otro.']);
        }

        $this->pines->asignar($request->user(), $datos['pin']);

        return back()->with('estado', 'PIN actualizado.');
    }
}
