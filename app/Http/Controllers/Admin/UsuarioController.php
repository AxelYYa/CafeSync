<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ReglaNegocioException;
use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Usuarios\UsuarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Administración de usuarios y roles (README §28.1: «Crear usuarios y configurar roles», solo gerente).
 */
class UsuarioController extends Controller
{
    public function __construct(private readonly UsuarioService $usuarios) {}

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:120'],
            'rol' => ['nullable', 'integer', 'exists:roles,id_rol'],
            'estado' => ['nullable', Rule::in(['activos', 'inactivos'])],
        ]);

        $usuarios = Usuario::query()
            ->with('rol:id_rol,nombre')
            ->when($filtros['buscar'] ?? null, fn ($consulta, $texto) => $consulta->where(fn ($coincidencias) => $coincidencias
                ->where('nombre', 'like', "%{$texto}%")
                ->orWhere('username', 'like', "%{$texto}%")
                ->orWhere('correo', 'like', "%{$texto}%")))
            ->when($filtros['rol'] ?? null, fn ($consulta, $rol) => $consulta->where('id_rol', $rol))
            ->when(($filtros['estado'] ?? null) === 'activos', fn ($consulta) => $consulta->where('activo', true))
            ->when(($filtros['estado'] ?? null) === 'inactivos', fn ($consulta) => $consulta->where('activo', false))
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => Rol::query()->orderBy('nombre')->get(),
            'filtros' => $filtros,
        ]);
    }

    public function create(): View
    {
        return view('admin.usuarios.create', ['roles' => Rol::query()->orderBy('nombre')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request, null);

        try {
            $usuario = $this->usuarios->crear($datos);
        } catch (ReglaNegocioException $error) {
            return back()->withInput()->withErrors(['pin' => $error->getMessage()]);
        }

        return redirect()->route('admin.usuarios.index')->with('estado', "Cuenta de {$usuario->nombre} creada.");
    }

    public function edit(Usuario $usuario): View
    {
        return view('admin.usuarios.edit', [
            'usuario' => $usuario->load('rol'),
            'roles' => Rol::query()->orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $datos = $this->validar($request, $usuario);

        try {
            $this->usuarios->actualizar($request->user(), $usuario, $datos, $request->session()->getId());
        } catch (ReglaNegocioException $error) {
            return back()->withInput()->withErrors(['usuario' => $error->getMessage()]);
        }

        return redirect()->route('admin.usuarios.index')->with('estado', "Cuenta de {$usuario->nombre} actualizada.");
    }

    /**
     * @return array{id_rol: int, nombre: string, username: string, correo: string, telefono: ?string, password: ?string, activo: bool, pin: ?string}
     */
    private function validar(Request $request, ?Usuario $usuario): array
    {
        $request->merge([
            'correo' => Str::lower(trim((string) $request->input('correo'))),
            'username' => trim((string) $request->input('username')),
            'activo' => $request->boolean('activo'),
        ]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'regex:/^[A-Za-z0-9._-]+$/', 'min:3', 'max:40', Rule::unique('usuarios', 'username')->ignore($usuario?->id_usuario, 'id_usuario')],
            'correo' => ['required', 'email', 'max:180', Rule::unique('usuarios', 'correo')->ignore($usuario?->id_usuario, 'id_usuario')],
            'telefono' => ['nullable', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'id_rol' => ['required', 'integer', 'exists:roles,id_rol'],
            'activo' => ['boolean'],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'pin' => ['nullable', 'digits_between:4,6', 'confirmed'],
        ], [
            'telefono.regex' => 'Escribe el teléfono en formato internacional, por ejemplo +528711234567.',
            'username.regex' => 'El usuario solo puede tener letras sin acentos, números, punto, guion y guion bajo.',
        ]);

        return [
            'id_rol' => (int) $datos['id_rol'],
            'nombre' => $datos['nombre'],
            'username' => $datos['username'],
            'correo' => $datos['correo'],
            'telefono' => $datos['telefono'] ?? null,
            'password' => $datos['password'] ?? null,
            'activo' => (bool) $datos['activo'],
            'pin' => $datos['pin'] ?? null,
        ];
    }
}
