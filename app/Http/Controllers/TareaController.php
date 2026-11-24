<?php

namespace App\Http\Controllers;

use App\Models\AsignacionTarea;
use App\Services\Tareas\TareaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TareaController extends Controller
{
    public function __construct(private readonly TareaService $tareas) {}

    public function index(Request $request): View
    {
        return view('tareas.index', [
            'tareas' => $this->tareas->visiblesPara($request->user()),
        ]);
    }

    public function completar(Request $request, AsignacionTarea $asignacion): RedirectResponse
    {
        Gate::authorize('completar', $asignacion);

        $this->tareas->completar($asignacion, $request->user());

        return back()->with('estado', 'Tarea marcada como completada.');
    }
}
