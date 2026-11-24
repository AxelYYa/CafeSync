<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ReglaNegocioException;
use App\Http\Controllers\Controller;
use App\Models\Estacion;
use App\Services\Catalogo\EstacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Administración de estaciones de preparación (solo gerente). Cada producto se enruta a una estación
 * y cada pantalla KDS muestra únicamente los renglones de la suya (README RF-04).
 */
class EstacionController extends Controller
{
    public function __construct(private readonly EstacionService $estaciones) {}

    public function index(): View
    {
        return view('admin.estaciones.index', [
            'estaciones' => Estacion::query()
                ->withCount([
                    'productos',
                    'productos as productos_activos_count' => fn ($productos) => $productos->where('activo', true),
                    'detallesOrden as pendientes_count' => fn ($renglones) => $this->estaciones->filtrarPendientes($renglones),
                ])
                ->orderByDesc('activa')
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.estaciones.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $estacion = $this->estaciones->crear($this->validar($request, null));

        return redirect()->route('admin.estaciones.index')->with('estado', "Estación {$estacion->nombre} creada.");
    }

    public function edit(Estacion $estacion): View
    {
        return view('admin.estaciones.edit', [
            'estacion' => $estacion->loadCount('productos'),
            'pendientes' => $this->estaciones->renglonesPendientes($estacion),
        ]);
    }

    public function update(Request $request, Estacion $estacion): RedirectResponse
    {
        try {
            $this->estaciones->actualizar($estacion, $this->validar($request, $estacion));
        } catch (ReglaNegocioException $error) {
            return back()->withInput()->withErrors(['estacion' => $error->getMessage()]);
        }

        return redirect()->route('admin.estaciones.index')->with('estado', "Estación {$estacion->nombre} actualizada.");
    }

    /**
     * @return array{nombre: string, descripcion: ?string, activa: bool}
     */
    private function validar(Request $request, ?Estacion $estacion): array
    {
        $request->merge(['nombre' => trim((string) $request->input('nombre')), 'activa' => $request->boolean('activa')]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:80', Rule::unique('estaciones', 'nombre')->ignore($estacion?->id_estacion, 'id_estacion')],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'activa' => ['boolean'],
        ]);

        return [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'activa' => (bool) $datos['activa'],
        ];
    }
}
