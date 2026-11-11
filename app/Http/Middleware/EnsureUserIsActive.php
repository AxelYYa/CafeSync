<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Cierra la sesión de cuentas inhabilitadas aunque la cookie siga siendo válida.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario !== null && ! $usuario->activo) {
            if ($request->is('api/*') || $request->expectsJson()) {
                abort(403, 'La cuenta está desactivada.');
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login' => 'Tu cuenta está desactivada. Contacta al gerente.',
            ]);
        }

        return $next($request);
    }
}
