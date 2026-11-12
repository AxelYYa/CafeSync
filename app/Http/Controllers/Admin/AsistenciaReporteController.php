<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\Asistencia\ReporteAsistenciaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Consulta del historial del reloj checador por periodo y empleado (solo gerente).
 */
class AsistenciaReporteController extends Controller
{
    public function __construct(private readonly ReporteAsistenciaService $reporte) {}

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'id_usuario' => ['nullable', 'integer', 'exists:usuarios,id_usuario'],
        ]);

        return view('admin.asistencias.index', [
            ...$this->reporte->resumen($filtros['desde'] ?? null, $filtros['hasta'] ?? null, isset($filtros['id_usuario']) ? (int) $filtros['id_usuario'] : null),
            'empleados' => Usuario::query()->orderBy('nombre')->get(['id_usuario', 'nombre']),
            'filtros' => $filtros,
        ]);
    }
}
