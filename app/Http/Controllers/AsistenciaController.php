<?php

namespace App\Http\Controllers;

use App\Enums\TipoAsistencia;
use App\Exceptions\PinInvalidoException;
use App\Exceptions\ReglaNegocioException;
use App\Models\Asistencia;
use App\Services\Asistencia\AsistenciaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AsistenciaController extends Controller
{
    private const MAX_PIN_FALLIDOS = 5;

    public function __construct(private readonly AsistenciaService $asistencia) {}

    public function index(): View
    {
        return view('asistencia.index', [
            'marcasHoy' => Asistencia::query()
                ->with('usuario:id_usuario,nombre')
                ->where('fecha_hora', '>=', now()->startOfDay())
                ->orderByDesc('fecha_hora')
                ->orderByDesc('id_asistencia')
                ->limit(15)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'pin' => ['required', 'digits_between:4,6'],
            'tipo' => ['required', Rule::in([TipoAsistencia::Entrada->value, TipoAsistencia::Salida->value])],
        ]);

        $clave = 'pin|'.$request->ip();

        if (RateLimiter::tooManyAttempts($clave, self::MAX_PIN_FALLIDOS)) {
            throw ValidationException::withMessages([
                'pin' => 'Demasiados PIN incorrectos. Espera '.RateLimiter::availableIn($clave).' segundos.',
            ]);
        }

        try {
            $marca = $this->asistencia->registrar($datos['pin'], TipoAsistencia::from($datos['tipo']));
        } catch (PinInvalidoException $error) {
            RateLimiter::hit($clave, 60);

            throw ValidationException::withMessages(['pin' => $error->getMessage()]);
        } catch (ReglaNegocioException $error) {
            throw ValidationException::withMessages(['pin' => $error->getMessage()]);
        }

        RateLimiter::clear($clave);

        return redirect()->route('asistencia.index')->with(
            'estado',
            "{$marca->tipo->etiqueta()} registrada para {$marca->usuario->nombre} a las {$marca->fecha_hora->format('H:i:s')}."
        );
    }
}
