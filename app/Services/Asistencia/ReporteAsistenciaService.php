<?php

namespace App\Services\Asistencia;

use App\Enums\TipoAsistencia;
use App\Models\Asistencia;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Consulta gerencial del reloj checador: agrupa las marcas en jornadas (entrada → salida) y suma los minutos.
 * Es un registro operativo, no un cálculo de nómina (README §6).
 */
class ReporteAsistenciaService
{
    /**
     * @return array{
     *     desde: CarbonImmutable,
     *     hasta: CarbonImmutable,
     *     jornadas: Collection<int, array{usuario: string, id_usuario: int, entrada: Asistencia, salida: ?Asistencia, minutos: ?int}>,
     *     totales: Collection<int, array{usuario: string, jornadas: int, minutos: int, abiertas: int}>
     * }
     */
    public function resumen(?string $desde, ?string $hasta, ?int $idUsuario = null): array
    {
        $inicio = $desde ? CarbonImmutable::parse($desde)->startOfDay() : CarbonImmutable::now()->startOfDay();
        $fin = $hasta ? CarbonImmutable::parse($hasta)->endOfDay() : CarbonImmutable::now()->endOfDay();

        $marcas = Asistencia::query()
            ->with(['usuario:id_usuario,nombre', 'turno:id_turno,nombre'])
            ->whereBetween('fecha_hora', [$inicio, $fin])
            ->when($idUsuario, fn ($consulta, $id) => $consulta->where('id_usuario', $id))
            ->orderBy('id_usuario')
            ->orderBy('fecha_hora')
            ->orderBy('id_asistencia')
            ->get();

        $jornadas = $this->jornadas($marcas);

        $totales = $jornadas->groupBy('id_usuario')->map(fn (Collection $deUsuario) => [
            'usuario' => $deUsuario->first()['usuario'],
            'jornadas' => $deUsuario->count(),
            'minutos' => (int) $deUsuario->sum('minutos'),
            'abiertas' => $deUsuario->whereNull('salida')->count(),
        ])->sortBy('usuario')->values();

        return [
            'desde' => $inicio,
            'hasta' => $fin,
            'jornadas' => $jornadas->sortByDesc(fn (array $jornada) => $jornada['entrada']->fecha_hora)->values(),
            'totales' => $totales,
        ];
    }

    /**
     * Empareja cada entrada con la siguiente salida del mismo empleado. Una salida cuya entrada quedó
     * antes del periodo consultado se ignora.
     *
     * @param  Collection<int, Asistencia>  $marcas  Ordenadas por empleado y fecha.
     * @return Collection<int, array{usuario: string, id_usuario: int, entrada: Asistencia, salida: ?Asistencia, minutos: ?int}>
     */
    public function jornadas(Collection $marcas): Collection
    {
        $jornadas = [];
        $abiertas = [];

        foreach ($marcas as $marca) {
            if ($marca->tipo === TipoAsistencia::Entrada) {
                $abiertas[$marca->id_usuario] = count($jornadas);
                $jornadas[] = [
                    'usuario' => $marca->usuario->nombre,
                    'id_usuario' => $marca->id_usuario,
                    'entrada' => $marca,
                    'salida' => null,
                    'minutos' => null,
                ];

                continue;
            }

            $indice = $abiertas[$marca->id_usuario] ?? null;

            if ($indice === null) {
                continue;
            }

            $jornadas[$indice]['salida'] = $marca;
            $jornadas[$indice]['minutos'] = (int) $jornadas[$indice]['entrada']->fecha_hora->diffInMinutes($marca->fecha_hora);
            unset($abiertas[$marca->id_usuario]);
        }

        return collect($jornadas);
    }
}
