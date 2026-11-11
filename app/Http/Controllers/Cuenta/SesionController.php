<?php

namespace App\Http\Controllers\Cuenta;

use App\Http\Controllers\Controller;
use App\Services\Sesiones\SesionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SesionController extends Controller
{
    public function __construct(private readonly SesionService $sesiones) {}

    public function destroy(Request $request, string $sesion): RedirectResponse
    {
        if ($sesion === $request->session()->getId()) {
            return back()->withErrors(['sesiones' => 'Para cerrar esta sesión usa el botón "Cerrar sesión".']);
        }

        $cerrada = $this->sesiones->cerrar($request->user(), $sesion);

        return $cerrada
            ? back()->with('estado', 'La sesión se cerró; ese dispositivo debe iniciar sesión de nuevo.')
            : back()->withErrors(['sesiones' => 'La sesión no existe o ya había sido cerrada.']);
    }

    public function destroyOtras(Request $request): RedirectResponse
    {
        $request->validate(['password_actual' => ['required', 'current_password']]);

        $cerradas = $this->sesiones->cerrarTodasExcepto($request->user(), $request->session()->getId());

        return back()->with('estado', "Se cerraron {$cerradas} sesión(es) en otros dispositivos.");
    }
}
