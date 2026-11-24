<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HabilidadToken;
use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Tokens de API para pantallas KDS (Laravel Sanctum). Son distintos de la sesión web:
 * se entregan a un dispositivo, llevan permisos limitados, vencen y se revocan aquí.
 */
class TokenApiController extends Controller
{
    public function index(): View
    {
        return view('admin.tokens.index', [
            'tokens' => PersonalAccessToken::query()->with('tokenable')->latest('id')->get(),
            'usuarios' => Usuario::query()->where('activo', true)->orderBy('nombre')->get(['id_usuario', 'nombre', 'username']),
            'habilidades' => HabilidadToken::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:60'],
            'id_usuario' => ['required', 'integer', Rule::exists('usuarios', 'id_usuario')->where('activo', true)],
            'habilidades' => ['required', 'array', 'min:1'],
            'habilidades.*' => ['required', Rule::in(HabilidadToken::valores())],
        ], [
            'habilidades.required' => 'Elige al menos un permiso para el token.',
        ]);

        $minutos = config('sanctum.expiration');

        $token = Usuario::query()->findOrFail($datos['id_usuario'])->createToken(
            $datos['nombre'],
            array_values(array_unique($datos['habilidades'])),
            $minutos ? now()->addMinutes((int) $minutos) : null,
        );

        return redirect()->route('admin.tokens.index')
            ->with('estado', "Token «{$datos['nombre']}» creado. Cópialo ahora: no se volverá a mostrar.")
            ->with('token_nuevo', $token->plainTextToken);
    }

    public function destroy(PersonalAccessToken $token): RedirectResponse
    {
        $token->delete();

        return redirect()->route('admin.tokens.index')->with('estado', "Token «{$token->name}» revocado; el dispositivo ya no podrá usarlo.");
    }
}
