<?php

namespace App\Services\Usuarios;

use App\Enums\NombreRol;
use App\Exceptions\ReglaNegocioException;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\PinService;
use App\Services\Sesiones\SesionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Alta y edición de cuentas por el gerente. Cuando una cuenta se desactiva o cambia de contraseña,
 * se cierran sus sesiones web y se revocan sus tokens de API para que el cambio tenga efecto inmediato.
 */
class UsuarioService
{
    public function __construct(
        private readonly PinService $pines,
        private readonly SesionService $sesiones,
    ) {}

    /**
     * @param  array{id_rol: int, nombre: string, username: string, correo: string, telefono?: ?string, password: string, activo?: bool, pin?: ?string}  $datos
     *
     * @throws ReglaNegocioException
     */
    public function crear(array $datos): Usuario
    {
        $this->asegurarPinDisponible($datos['pin'] ?? null, null);

        return DB::transaction(function () use ($datos): Usuario {
            $usuario = Usuario::create([
                'id_rol' => $datos['id_rol'],
                'nombre' => trim($datos['nombre']),
                'username' => trim($datos['username']),
                'correo' => Str::lower(trim($datos['correo'])),
                'telefono' => $datos['telefono'] ?? null,
                'password' => $datos['password'],
                'activo' => $datos['activo'] ?? true,
            ]);

            if (filled($datos['pin'] ?? null)) {
                $this->pines->asignar($usuario, $datos['pin']);
            }

            return $usuario;
        });
    }

    /**
     * @param  array{id_rol: int, nombre: string, username: string, correo: string, telefono?: ?string, password?: ?string, activo: bool, pin?: ?string}  $datos
     * @param  string|null  $sesionActual  Sesión del gerente que edita; se conserva si edita su propia cuenta.
     *
     * @throws ReglaNegocioException
     */
    public function actualizar(Usuario $gerente, Usuario $usuario, array $datos, ?string $sesionActual = null): Usuario
    {
        $esSuCuenta = $gerente->is($usuario);
        $rolGerente = Rol::query()->where('nombre', NombreRol::Gerente->value)->value('id_rol');

        if ($esSuCuenta && (! $datos['activo'] || (int) $datos['id_rol'] !== (int) $rolGerente)) {
            throw new ReglaNegocioException('No puedes quitarte el rol de gerente ni desactivar tu propia cuenta.');
        }

        $this->asegurarPinDisponible($datos['pin'] ?? null, $usuario->id_usuario);

        return DB::transaction(function () use ($usuario, $datos, $esSuCuenta, $sesionActual): Usuario {
            $cambiaPassword = filled($datos['password'] ?? null);
            $seDesactiva = $usuario->activo && ! $datos['activo'];

            $usuario->fill([
                'id_rol' => $datos['id_rol'],
                'nombre' => trim($datos['nombre']),
                'username' => trim($datos['username']),
                'correo' => Str::lower(trim($datos['correo'])),
                'telefono' => $datos['telefono'] ?? null,
                'activo' => $datos['activo'],
            ]);

            if ($cambiaPassword) {
                $usuario->forceFill(['password' => $datos['password'], 'remember_token' => Str::random(60)]);
            }

            $usuario->save();

            if (filled($datos['pin'] ?? null)) {
                $this->pines->asignar($usuario, $datos['pin']);
            }

            if ($cambiaPassword || $seDesactiva) {
                $this->sesiones->cerrarTodasExcepto($usuario, $esSuCuenta ? $sesionActual : null);
                $usuario->tokens()->delete();
            }

            return $usuario;
        });
    }

    /**
     * @throws ReglaNegocioException
     */
    private function asegurarPinDisponible(?string $pin, ?int $idUsuario): void
    {
        if (filled($pin) && ! $this->pines->pinDisponible($pin, $idUsuario)) {
            throw new ReglaNegocioException('Ese PIN ya lo usa otro empleado. Elige otro.');
        }
    }
}
