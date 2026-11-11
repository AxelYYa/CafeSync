<?php

namespace App\Services\Inventario;

use App\Enums\TipoMovimiento;
use App\Enums\UnidadMedida;
use App\Exceptions\ReglaNegocioException;
use App\Models\Ingrediente;
use App\Models\MovimientoInventario;
use App\Models\Usuario;
use App\Repositories\InventarioRepository;
use Illuminate\Support\Facades\DB;

/**
 * Movimientos manuales de inventario (entradas, salidas y ajustes). Sólo el gerente puede registrarlos.
 */
class InventarioService
{
    public function __construct(private readonly InventarioRepository $repositorio) {}

    /**
     * @param  string  $unidadCaptura  Unidad en la que se capturó la cantidad (se convierte a la unidad base del ingrediente).
     *
     * @throws ReglaNegocioException
     */
    public function registrarManual(
        Usuario $gerente,
        Ingrediente $ingrediente,
        TipoMovimiento $tipo,
        string $cantidad,
        string $unidadCaptura,
        ?bool $incrementaAjuste,
        ?string $motivo,
    ): MovimientoInventario {
        if (! $gerente->esGerente()) {
            throw new ReglaNegocioException('Solo el gerente puede modificar existencias.');
        }

        if (! in_array($tipo, [TipoMovimiento::Entrada, TipoMovimiento::Salida, TipoMovimiento::Ajuste], true)) {
            throw new ReglaNegocioException('Los consumos y reversiones se generan automáticamente al cerrar o revertir una venta.');
        }

        if ($tipo === TipoMovimiento::Ajuste && blank($motivo)) {
            throw new ReglaNegocioException('Todo ajuste requiere una observación que explique el motivo.');
        }

        $unidad = UnidadMedida::tryFrom($unidadCaptura);

        if ($unidad === null || ! $unidad->esCompatibleCon($ingrediente->unidad())) {
            throw new ReglaNegocioException("La unidad «{$unidadCaptura}» no es compatible con {$ingrediente->nombre} ({$ingrediente->unidad()->value}).");
        }

        $enBase = $unidad->convertirABase($cantidad);

        $incrementa = match ($tipo) {
            TipoMovimiento::Entrada => true,
            TipoMovimiento::Salida => false,
            default => $incrementaAjuste ?? throw new ReglaNegocioException('Indica si el ajuste suma o resta existencias.'),
        };

        return DB::transaction(function () use ($ingrediente, $tipo, $incrementa, $enBase, $gerente, $motivo): MovimientoInventario {
            $bloqueado = $this->repositorio->bloquear([$ingrediente->id_ingrediente])->get($ingrediente->id_ingrediente);

            return $this->repositorio->registrarMovimiento(
                $bloqueado,
                $tipo,
                $incrementa,
                $enBase,
                $gerente->id_usuario,
                null,
                $motivo,
            );
        });
    }
}
