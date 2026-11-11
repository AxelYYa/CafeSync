<?php

namespace App\Services\Asistencia;

use App\Enums\TipoAsistencia;
use App\Exceptions\ReglaNegocioException;
use App\Models\Asistencia;
use App\Models\Turno;
use App\Models\Usuario;
use App\Services\PinService;
use Illuminate\Support\Facades\DB;

class AsistenciaService
{
    public function __construct(private readonly PinService $pines) {}

    /**
     * Registra una entrada o salida identificando al empleado por su PIN.
     * La fecha y hora siempre son las del servidor; un PIN inválido no genera ningún registro.
     *
     * @throws ReglaNegocioException
     */
    public function registrar(string $pin, TipoAsistencia $tipo): Asistencia
    {
        $usuario = $this->pines->resolver($pin);

        if ($usuario === null) {
            throw new ReglaNegocioException('PIN no válido.');
        }

        return DB::transaction(function () use ($usuario, $tipo): Asistencia {
            Usuario::query()->lockForUpdate()->find($usuario->id_usuario);

            $ultima = Asistencia::query()
                ->where('id_usuario', $usuario->id_usuario)
                ->orderByDesc('fecha_hora')
                ->orderByDesc('id_asistencia')
                ->first();

            $this->validarSecuencia($ultima, $tipo);

            $ahora = now();

            return Asistencia::create([
                'id_usuario' => $usuario->id_usuario,
                'id_turno' => $this->turnoDe($ahora->format('H:i:s'))?->id_turno,
                'tipo' => $tipo,
                'metodo' => 'PIN',
                'fecha_hora' => $ahora,
            ])->load('usuario');
        });
    }

    /**
     * @throws ReglaNegocioException
     */
    private function validarSecuencia(?Asistencia $ultima, TipoAsistencia $tipo): void
    {
        if ($tipo === TipoAsistencia::Entrada && $ultima?->tipo === TipoAsistencia::Entrada) {
            throw new ReglaNegocioException('Ya registraste tu entrada a las '.$ultima->fecha_hora->format('H:i').'. Registra primero tu salida.');
        }

        if ($tipo === TipoAsistencia::Salida && $ultima?->tipo !== TipoAsistencia::Entrada) {
            throw new ReglaNegocioException('No hay una entrada abierta. Registra primero tu entrada.');
        }
    }

    private function turnoDe(string $hora): ?Turno
    {
        return Turno::query()->where('activo', true)->get()->first(fn (Turno $turno) => $turno->incluyeHora($hora));
    }
}
