<?php

namespace App\Http\Controllers;

use App\Enums\EstadoOrden;
use App\Models\Ingrediente;
use App\Models\Orden;
use App\Models\Usuario;
use App\Repositories\InventarioRepository;
use App\Services\Tareas\TareaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly InventarioRepository $inventario,
        private readonly TareaService $tareas,
    ) {}

    public function __invoke(Request $request): View
    {
        /** @var Usuario $usuario */
        $usuario = $request->user()->load('rol');

        $esGerente = $usuario->esGerente();

        return view('dashboard.index', [
            'usuario' => $usuario,
            'ordenesActivas' => Orden::query()->whereIn('estado', array_column(EstadoOrden::activos(), 'value'))->count(),
            'ventasHoy' => $esGerente ? (string) Orden::query()->where('estado', EstadoOrden::Cerrada->value)->where('cerrada_en', '>=', now()->startOfDay())->sum('total') : null,
            'bajoMinimo' => $esGerente ? $this->inventario->bajoMinimo() : collect(),
            'totalIngredientes' => $esGerente ? Ingrediente::query()->where('activo', true)->count() : null,
            'tareasPendientes' => $this->tareas->visiblesPara($usuario)->filter(fn ($tarea) => ! $tarea->estaCompletada())->count(),
        ]);
    }
}
