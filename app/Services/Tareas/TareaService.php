<?php

namespace App\Services\Tareas;

use App\Enums\EstadoTarea;
use App\Exceptions\ReglaNegocioException;
use App\Models\AsignacionTarea;
use App\Models\PlantillaTarea;
use App\Models\Usuario;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TareaService
{
    /**
     * @throws ReglaNegocioException
     */
    public function asignar(
        Usuario $gerente,
        PlantillaTarea $plantilla,
        ?int $idTurno,
        ?int $idEstacion,
        ?int $idUsuarioAsignado,
        ?CarbonInterface $fechaLimite,
    ): AsignacionTarea {
        if (! $gerente->esGerente()) {
            throw new ReglaNegocioException('Solo el gerente puede asignar tareas.');
        }

        if (! $plantilla->activa) {
            throw new ReglaNegocioException('La plantilla está inactiva y no puede asignarse.');
        }

        if ($idTurno === null && $idEstacion === null && $idUsuarioAsignado === null) {
            throw new ReglaNegocioException('Indica un turno, una zona o un responsable para la tarea.');
        }

        return AsignacionTarea::create([
            'id_plantilla' => $plantilla->id_plantilla,
            'id_turno' => $idTurno,
            'id_estacion' => $idEstacion,
            'id_usuario_asignado' => $idUsuarioAsignado,
            'id_usuario_asigno' => $gerente->id_usuario,
            'estado' => EstadoTarea::Pendiente,
            'fecha_limite' => $fechaLimite,
        ]);
    }

    /**
     * Marca la tarea como completada y registra quién y cuándo (hora del servidor). Es idempotente.
     */
    public function completar(AsignacionTarea $asignacion, Usuario $usuario): AsignacionTarea
    {
        return DB::transaction(function () use ($asignacion, $usuario): AsignacionTarea {
            $asignacion = AsignacionTarea::query()->lockForUpdate()->findOrFail($asignacion->id_asignacion);

            if ($asignacion->estaCompletada()) {
                return $asignacion;
            }

            $asignacion->forceFill([
                'estado' => EstadoTarea::Completada,
                'id_usuario_completo' => $usuario->id_usuario,
                'completada_en' => now(),
            ])->save();

            return $asignacion;
        });
    }

    /**
     * El gerente puede reabrir una tarea completada por error.
     */
    public function reabrir(AsignacionTarea $asignacion, Usuario $gerente): AsignacionTarea
    {
        if (! $gerente->esGerente()) {
            throw new ReglaNegocioException('Solo el gerente puede reabrir tareas.');
        }

        $asignacion->forceFill([
            'estado' => EstadoTarea::Pendiente,
            'id_usuario_completo' => null,
            'completada_en' => null,
        ])->save();

        return $asignacion;
    }

    /**
     * Tareas visibles para un empleado: las asignadas a él y las generales de turno o zona.
     * Se incluyen las completadas hoy para que el avance del turno sea visible.
     *
     * @return Collection<int, AsignacionTarea>
     */
    public function visiblesPara(Usuario $usuario): Collection
    {
        return AsignacionTarea::query()
            ->with(['plantilla', 'turno', 'estacion', 'responsable:id_usuario,nombre', 'completadaPor:id_usuario,nombre'])
            ->where(fn ($consulta) => $consulta
                ->where('id_usuario_asignado', $usuario->id_usuario)
                ->orWhereNull('id_usuario_asignado'))
            ->where(fn ($consulta) => $consulta
                ->where('estado', '!=', EstadoTarea::Completada->value)
                ->orWhere('completada_en', '>=', now()->startOfDay()))
            ->orderBy('estado')
            ->orderBy('fecha_limite')
            ->orderBy('id_asignacion')
            ->get();
    }
}
