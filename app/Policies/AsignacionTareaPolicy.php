<?php

namespace App\Policies;

use App\Models\AsignacionTarea;
use App\Models\Usuario;

class AsignacionTareaPolicy
{
    /**
     * El gerente puede completar cualquier tarea; el empleado sólo las que le asignaron a él
     * o las generales (sin responsable individual).
     */
    public function completar(Usuario $usuario, AsignacionTarea $asignacion): bool
    {
        if ($usuario->esGerente()) {
            return true;
        }

        return $asignacion->id_usuario_asignado === null
            || $asignacion->id_usuario_asignado === $usuario->id_usuario;
    }
}
