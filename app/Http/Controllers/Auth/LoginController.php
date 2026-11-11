<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\Mfa\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const MAX_INTENTOS = 5;

    private const SEGUNDOS_BLOQUEO = 300;

    private const MINUTOS_DESAFIO_MFA = 5;

    public function __construct(private readonly MfaService $mfa) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'login' => ['required', 'string', 'max:180'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $clave = $this->claveLimite($datos['login'], $request);
        $this->asegurarSinBloqueo($clave);

        $usuario = Usuario::query()
            ->where(fn ($consulta) => $consulta
                ->where('username', trim($datos['login']))
                ->orWhere('correo', Str::lower(trim($datos['login']))))
            ->first();

        $contrasenaValida = $usuario !== null
            ? Hash::check($datos['password'], $usuario->password)
            : $this->falsearComprobacion($datos['password']);

        if (! $contrasenaValida) {
            RateLimiter::hit($clave, self::SEGUNDOS_BLOQUEO);

            throw ValidationException::withMessages(['login' => 'Usuario o contraseña incorrectos.']);
        }

        RateLimiter::clear($clave);

        if (! $usuario->activo) {
            throw ValidationException::withMessages(['login' => 'Tu cuenta está desactivada. Contacta al gerente.']);
        }

        if ($usuario->tieneMfaActivo()) {
            $request->session()->put('mfa.pendiente', [
                'id' => $usuario->id_usuario,
                'expira' => now()->addMinutes(self::MINUTOS_DESAFIO_MFA)->timestamp,
            ]);

            return redirect()->route('mfa.desafio');
        }

        return $this->iniciarSesion($request, $usuario);
    }

    public function desafioMfa(Request $request): View|RedirectResponse
    {
        return $this->usuarioPendienteMfa($request) === null
            ? redirect()->route('login')
            : view('auth.mfa-desafio');
    }

    public function verificarMfa(Request $request): RedirectResponse
    {
        $datos = $request->validate(['codigo' => ['required', 'string', 'max:20']]);

        $usuario = $this->usuarioPendienteMfa($request);

        if ($usuario === null) {
            return redirect()->route('login')->withErrors(['login' => 'La verificación expiró. Inicia sesión de nuevo.']);
        }

        $clave = 'mfa|'.$usuario->id_usuario.'|'.$request->ip();
        $this->asegurarSinBloqueo($clave, 'codigo');

        if (! $this->mfa->verificarDesafio($usuario, $datos['codigo'])) {
            RateLimiter::hit($clave, self::SEGUNDOS_BLOQUEO);

            throw ValidationException::withMessages(['codigo' => 'El código no es válido o ya fue utilizado.']);
        }

        RateLimiter::clear($clave);
        $request->session()->forget('mfa.pendiente');

        return $this->iniciarSesion($request, $usuario);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('estado', 'Sesión cerrada correctamente.');
    }

    private function iniciarSesion(Request $request, Usuario $usuario): RedirectResponse
    {
        Auth::guard('web')->login($usuario);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function usuarioPendienteMfa(Request $request): ?Usuario
    {
        $pendiente = $request->session()->get('mfa.pendiente');

        if (! is_array($pendiente) || ($pendiente['expira'] ?? 0) < now()->timestamp) {
            $request->session()->forget('mfa.pendiente');

            return null;
        }

        return Usuario::query()->where('activo', true)->find($pendiente['id']);
    }

    private function claveLimite(string $login, Request $request): string
    {
        return 'login|'.Str::lower(trim($login)).'|'.$request->ip();
    }

    private function asegurarSinBloqueo(string $clave, string $campo = 'login'): void
    {
        if (RateLimiter::tooManyAttempts($clave, self::MAX_INTENTOS)) {
            $segundos = RateLimiter::availableIn($clave);

            throw ValidationException::withMessages([
                $campo => 'Demasiados intentos. Vuelve a intentar en '.ceil($segundos / 60).' minuto(s).',
            ]);
        }
    }

    private function falsearComprobacion(string $contrasena): bool
    {
        Hash::make($contrasena);

        return false;
    }
}
