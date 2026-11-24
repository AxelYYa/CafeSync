<?php

namespace App\Services\Recuperacion;

use App\Enums\CanalRecuperacion;
use App\Models\RecuperacionPassword;
use App\Models\Usuario;
use App\Services\Sesiones\SesionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Orquesta la recuperación de contraseña en tres pasos: solicitar, verificar y restablecer.
 * Ninguna respuesta revela si el usuario existe: los pasos fallidos son indistinguibles desde fuera.
 */
class RecuperacionPasswordService
{
    public const MINUTOS_VIGENCIA_CODIGO = 10;

    public const MINUTOS_VIGENCIA_TOKEN = 15;

    public const MAX_INTENTOS = 5;

    private const PREGUNTAS_SEÑUELO = [
        '¿Cuál es el nombre de tu primera mascota?',
        '¿En qué ciudad naciste?',
        '¿Cómo se llamaba tu escuela primaria?',
        '¿Cuál es tu comida favorita?',
        '¿Cuál es el segundo nombre de tu madre?',
    ];

    /**
     * @var array<string, EstrategiaRecuperacion>
     */
    private array $estrategias = [];

    /**
     * @param  iterable<EstrategiaRecuperacion>  $estrategias
     */
    public function __construct(iterable $estrategias, private readonly SesionService $sesiones)
    {
        foreach ($estrategias as $estrategia) {
            $this->estrategias[$estrategia->canal()->value] = $estrategia;
        }
    }

    public function estrategia(CanalRecuperacion $canal): EstrategiaRecuperacion
    {
        return $this->estrategias[$canal->value];
    }

    /**
     * @return list<CanalRecuperacion>
     */
    public function canalesDisponibles(): array
    {
        return array_values(array_map(
            fn (EstrategiaRecuperacion $estrategia) => $estrategia->canal(),
            array_filter($this->estrategias, fn (EstrategiaRecuperacion $estrategia) => $estrategia->disponible())
        ));
    }

    public function buscarUsuario(string $identificador): ?Usuario
    {
        $identificador = trim($identificador);

        return Usuario::query()
            ->where('activo', true)
            ->where(fn ($consulta) => $consulta
                ->where('username', $identificador)
                ->orWhere('correo', mb_strtolower($identificador)))
            ->first();
    }

    /**
     * Crea la solicitud y dispara el canal. Si el usuario no existe o no tiene datos para el canal, no hace nada.
     */
    public function solicitar(string $identificador, CanalRecuperacion $canal): void
    {
        $estrategia = $this->estrategia($canal);
        $usuario = $this->buscarUsuario($identificador);

        if ($usuario === null || ! $estrategia->aplicaA($usuario)) {
            return;
        }

        RecuperacionPassword::query()
            ->where('id_usuario', $usuario->id_usuario)
            ->where('canal', $canal->value)
            ->whereNull('usado_en')
            ->update(['usado_en' => now()]);

        $solicitud = RecuperacionPassword::create([
            'id_usuario' => $usuario->id_usuario,
            'canal' => $canal->value,
            'expira_en' => now()->addMinutes(self::MINUTOS_VIGENCIA_CODIGO),
        ]);

        try {
            $estrategia->iniciar($usuario, $solicitud);
        } catch (Throwable $error) {
            Log::error('No se pudo iniciar la recuperación de contraseña.', [
                'canal' => $canal->value,
                'id_usuario' => $usuario->id_usuario,
                'error' => $error->getMessage(),
            ]);
            $solicitud->forceFill(['usado_en' => now()])->save();
        }
    }

    /**
     * Pregunta que se muestra en el canal de pregunta secreta. Para usuarios inexistentes o sin pregunta
     * se devuelve un señuelo estable, de modo que la pantalla no delate qué cuentas existen.
     */
    public function preguntaPara(string $identificador): string
    {
        $usuario = $this->buscarUsuario($identificador);

        if ($usuario !== null && $usuario->tienePreguntaSecreta()) {
            return $usuario->pregunta_secreta;
        }

        $indice = hexdec(substr(hash('sha256', mb_strtolower(trim($identificador))), 0, 6)) % count(self::PREGUNTAS_SEÑUELO);

        return self::PREGUNTAS_SEÑUELO[$indice];
    }

    /**
     * Verifica el código o la respuesta. Devuelve el token de restablecimiento (en claro, una sola vez) o null.
     */
    public function verificar(string $identificador, CanalRecuperacion $canal, string $entrada): ?string
    {
        $estrategia = $this->estrategia($canal);
        $usuario = $this->buscarUsuario($identificador);

        $solicitud = $usuario === null ? null : RecuperacionPassword::query()
            ->where('id_usuario', $usuario->id_usuario)
            ->where('canal', $canal->value)
            ->whereNull('usado_en')
            ->whereNull('verificado_en')
            ->where('expira_en', '>', now())
            ->latest('id_recuperacion')
            ->first();

        if ($usuario === null || $solicitud === null || $solicitud->intentos >= self::MAX_INTENTOS) {
            // Trabajo equivalente para no delatar la existencia de la cuenta por tiempo de respuesta.
            Hash::make($entrada);

            return null;
        }

        $solicitud->increment('intentos');

        if (! $estrategia->verificar($usuario, $solicitud, $entrada)) {
            return null;
        }

        $token = Str::random(64);

        $solicitud->forceFill([
            'verificado_en' => now(),
            'token_hash' => hash('sha256', $token),
            'expira_en' => now()->addMinutes(self::MINUTOS_VIGENCIA_TOKEN),
        ])->save();

        return $token;
    }

    public function tokenValido(string $token): bool
    {
        return $this->solicitudPorToken($token) !== null;
    }

    /**
     * Cambia la contraseña con un token verificado. Cierra todas las sesiones del usuario.
     */
    public function restablecer(string $token, string $nuevaContrasena): bool
    {
        return DB::transaction(function () use ($token, $nuevaContrasena): bool {
            $solicitud = $this->solicitudPorToken($token);

            if ($solicitud === null) {
                return false;
            }

            $usuario = $solicitud->usuario;
            $usuario->forceFill(['password' => $nuevaContrasena, 'remember_token' => Str::random(60)])->save();

            RecuperacionPassword::query()
                ->where('id_usuario', $usuario->id_usuario)
                ->whereNull('usado_en')
                ->update(['usado_en' => now()]);

            $this->sesiones->cerrarTodasExcepto($usuario, null);

            return true;
        });
    }

    private function solicitudPorToken(string $token): ?RecuperacionPassword
    {
        return RecuperacionPassword::query()
            ->with('usuario')
            ->where('token_hash', hash('sha256', $token))
            ->whereNotNull('verificado_en')
            ->whereNull('usado_en')
            ->where('expira_en', '>', now())
            ->first();
    }
}
