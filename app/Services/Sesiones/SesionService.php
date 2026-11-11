<?php

namespace App\Services\Sesiones;

use App\Models\Usuario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Consulta y revoca las sesiones web guardadas en la tabla `sessions` (SESSION_DRIVER=database).
 * Eliminar la fila invalida la sesión de inmediato: la siguiente petición con esa cookie queda sin autenticar.
 */
class SesionService
{
    /**
     * @return list<array{id: string, ip: ?string, dispositivo: string, navegador: string, sistema: string, ultima_actividad: Carbon, actual: bool}>
     */
    public function listar(Usuario $usuario, string $sesionActual): array
    {
        if (! $this->usaBaseDeDatos()) {
            return [];
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $usuario->getAuthIdentifier())
            ->orderByDesc('last_activity')
            ->get()
            ->map(function (object $fila) use ($sesionActual): array {
                $agente = $this->interpretarAgente((string) $fila->user_agent);

                return [
                    'id' => $fila->id,
                    'ip' => $fila->ip_address,
                    'dispositivo' => $agente['dispositivo'],
                    'navegador' => $agente['navegador'],
                    'sistema' => $agente['sistema'],
                    'ultima_actividad' => Carbon::createFromTimestamp($fila->last_activity),
                    'actual' => $fila->id === $sesionActual,
                ];
            })
            ->all();
    }

    /**
     * Cierra una sesión concreta del usuario. Devuelve false si no le pertenece o no existe.
     */
    public function cerrar(Usuario $usuario, string $idSesion): bool
    {
        return DB::table(config('session.table', 'sessions'))
            ->where('id', $idSesion)
            ->where('user_id', $usuario->getAuthIdentifier())
            ->delete() > 0;
    }

    /**
     * Cierra todas las sesiones del usuario salvo la indicada. Devuelve cuántas se cerraron.
     */
    public function cerrarTodasExcepto(Usuario $usuario, ?string $sesionConservada): int
    {
        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $usuario->getAuthIdentifier())
            ->when($sesionConservada, fn ($consulta) => $consulta->where('id', '!=', $sesionConservada))
            ->delete();
    }

    public function usaBaseDeDatos(): bool
    {
        return config('session.driver') === 'database' && Schema::hasTable(config('session.table', 'sessions'));
    }

    /**
     * @return array{dispositivo: string, navegador: string, sistema: string}
     */
    public function interpretarAgente(string $agente): array
    {
        $sistema = match (true) {
            (bool) preg_match('/Windows/i', $agente) => 'Windows',
            (bool) preg_match('/Android/i', $agente) => 'Android',
            (bool) preg_match('/iPhone|iPad|iOS/i', $agente) => 'iOS',
            (bool) preg_match('/Mac OS X|Macintosh/i', $agente) => 'macOS',
            (bool) preg_match('/Linux/i', $agente) => 'Linux',
            default => 'Desconocido',
        };

        $navegador = match (true) {
            (bool) preg_match('/Edg\//i', $agente) => 'Edge',
            (bool) preg_match('/OPR\/|Opera/i', $agente) => 'Opera',
            (bool) preg_match('/Firefox\//i', $agente) => 'Firefox',
            (bool) preg_match('/Chrome\//i', $agente) => 'Chrome',
            (bool) preg_match('/Safari\//i', $agente) => 'Safari',
            default => 'Desconocido',
        };

        $dispositivo = preg_match('/Mobile|Android|iPhone|iPad|Tablet/i', $agente) ? 'Móvil o tableta' : 'Computadora';

        return compact('dispositivo', 'navegador', 'sistema');
    }
}
