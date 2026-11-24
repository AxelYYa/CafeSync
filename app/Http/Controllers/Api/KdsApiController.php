<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoPreparacion;
use App\Exceptions\ReglaNegocioException;
use App\Http\Controllers\Controller;
use App\Models\DetalleOrden;
use App\Models\Estacion;
use App\Services\Ventas\KdsService;
use App\Services\Ventas\PreparacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KdsApiController extends Controller
{
    public function __construct(
        private readonly KdsService $kds,
        private readonly PreparacionService $preparacion,
    ) {}

    public function ordenes(Estacion $estacion): JsonResponse
    {
        $ordenes = $this->kds->ordenesDeEstacion($estacion);

        return response()->json([
            'estacion' => $estacion->nombre,
            'version' => $this->kds->version($ordenes),
            'ordenes' => $ordenes->map(fn ($orden) => [
                'folio' => $orden->folio,
                'canal' => $orden->canal_origen->value,
                'estado' => $orden->estado->value,
                'creada_en' => $orden->created_at->toIso8601String(),
                'renglones' => $orden->detalles->map(fn ($detalle) => [
                    'id_detalle_orden' => $detalle->id_detalle_orden,
                    'producto' => $detalle->nombre_producto_snapshot,
                    'cantidad' => $detalle->cantidad,
                    'estado' => $detalle->estado_preparacion->value,
                ])->values(),
            ])->values(),
        ]);
    }

    public function avanzar(Request $request, DetalleOrden $detalle): JsonResponse
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in([EstadoPreparacion::EnPreparacion->value, EstadoPreparacion::Lista->value])],
        ]);

        try {
            $detalle = $this->preparacion->avanzar($detalle, EstadoPreparacion::from($datos['estado']));
        } catch (ReglaNegocioException $error) {
            return response()->json(['message' => $error->getMessage()], 422);
        }

        return response()->json([
            'id_detalle_orden' => $detalle->id_detalle_orden,
            'estado' => $detalle->estado_preparacion->value,
        ]);
    }
}
