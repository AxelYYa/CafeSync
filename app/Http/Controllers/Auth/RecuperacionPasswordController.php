<?php

namespace App\Http\Controllers\Auth;

use App\Enums\CanalRecuperacion;
use App\Http\Controllers\Controller;
use App\Services\Recuperacion\RecuperacionPasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RecuperacionPasswordController extends Controller
{
    public function __construct(private readonly RecuperacionPasswordService $recuperacion) {}

    public function create(): View
    {
        return view('auth.recuperar', [
            'canales' => CanalRecuperacion::cases(),
            'disponibles' => $this->recuperacion->canalesDisponibles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'identificador' => ['required', 'string', 'max:180'],
            'canal' => ['required', Rule::in(CanalRecuperacion::valores())],
        ]);

        $canal = CanalRecuperacion::from($datos['canal']);

        if (! in_array($canal, $this->recuperacion->canalesDisponibles(), true)) {
            throw ValidationException::withMessages([
                'canal' => 'Este método de recuperación no está disponible en este momento. Elige otro o contacta al gerente.',
            ]);
        }

        $this->limitar('recuperar-solicitud|'.$request->ip(), 5, 3600);
        $this->limitar('recuperar-solicitud|'.mb_strtolower($datos['identificador']).'|'.$canal->value, 3, 900);

        $this->recuperacion->solicitar($datos['identificador'], $canal);

        $request->session()->put('recuperacion', [
            'identificador' => $datos['identificador'],
            'canal' => $canal->value,
        ]);

        return redirect()->route('recuperacion.verificar');
    }

    public function formularioVerificacion(Request $request): View|RedirectResponse
    {
        $contexto = $request->session()->get('recuperacion');

        if (! is_array($contexto)) {
            return redirect()->route('recuperacion.solicitar');
        }

        $canal = CanalRecuperacion::from($contexto['canal']);

        return view('auth.recuperar-verificar', [
            'canal' => $canal,
            'pregunta' => $canal === CanalRecuperacion::PreguntaSecreta
                ? $this->recuperacion->preguntaPara($contexto['identificador'])
                : null,
        ]);
    }

    public function verificar(Request $request): RedirectResponse
    {
        $contexto = $request->session()->get('recuperacion');

        if (! is_array($contexto)) {
            return redirect()->route('recuperacion.solicitar');
        }

        $datos = $request->validate(['entrada' => ['required', 'string', 'max:160']]);

        $this->limitar('recuperar-verificacion|'.$request->ip(), 10, 600, 'entrada');

        $token = $this->recuperacion->verificar(
            $contexto['identificador'],
            CanalRecuperacion::from($contexto['canal']),
            $datos['entrada'],
        );

        if ($token === null) {
            throw ValidationException::withMessages([
                'entrada' => 'El dato no es válido, ya venció o se agotaron los intentos. Solicita uno nuevo.',
            ]);
        }

        $request->session()->forget('recuperacion');
        $request->session()->put('recuperacion_token', $token);

        return redirect()->route('recuperacion.nueva');
    }

    public function formularioNuevaContrasena(Request $request): View|RedirectResponse
    {
        $token = $request->session()->get('recuperacion_token');

        if (! is_string($token) || ! $this->recuperacion->tokenValido($token)) {
            return redirect()->route('recuperacion.solicitar');
        }

        return view('auth.recuperar-nueva');
    }

    public function restablecer(Request $request): RedirectResponse
    {
        $token = $request->session()->get('recuperacion_token');

        $datos = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! is_string($token) || ! $this->recuperacion->restablecer($token, $datos['password'])) {
            return redirect()->route('recuperacion.solicitar')
                ->withErrors(['identificador' => 'El enlace de recuperación venció. Inicia el proceso de nuevo.']);
        }

        Session::forget('recuperacion_token');

        return redirect()->route('login')->with('estado', 'Contraseña actualizada. Inicia sesión con tu nueva contraseña.');
    }

    private function limitar(string $clave, int $maximo, int $segundos, string $campo = 'identificador'): void
    {
        if (RateLimiter::tooManyAttempts($clave, $maximo)) {
            throw ValidationException::withMessages([
                $campo => 'Demasiadas solicitudes. Espera unos minutos antes de intentar de nuevo.',
            ]);
        }

        RateLimiter::hit($clave, $segundos);
    }
}
