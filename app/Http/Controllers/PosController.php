<?php

namespace App\Http\Controllers;

use App\Enums\CanalOrigen;
use App\Exceptions\ReglaNegocioException;
use App\Services\Inventario\DisponibilidadService;
use App\Services\Ventas\OrdenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        private readonly DisponibilidadService $disponibilidad,
        private readonly OrdenService $ordenes,
    ) {}

    public function index(): View
    {
        $catalogo = $this->disponibilidad->porProducto();

        return view('pos.index', [
            'catalogo' => $catalogo->groupBy(fn (array $fila) => $fila['producto']->estacion->nombre),
            'canales' => CanalOrigen::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'canal_origen' => ['required', Rule::in(CanalOrigen::valores())],
            'nota' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:99'],
        ], [
            'items.required' => 'Agrega al menos un producto a la orden.',
            'items.min' => 'Agrega al menos un producto a la orden.',
        ]);

        try {
            $orden = $this->ordenes->crear(
                $request->user(),
                CanalOrigen::from($datos['canal_origen']),
                array_values($datos['items']),
                $datos['nota'] ?? null,
            );
        } catch (ReglaNegocioException $error) {
            throw ValidationException::withMessages(['items' => $error->getMessage()]);
        }

        return redirect()->route('pos.index')->with('estado', "Orden {$orden->folio} enviada a preparación.");
    }
}
