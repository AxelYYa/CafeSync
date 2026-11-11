<?php

namespace App\Providers;

use App\Models\DetalleOrden;
use App\Models\Orden;
use App\Models\Usuario;
use App\Observers\DetalleOrdenObserver;
use App\Observers\OrdenObserver;
use App\Services\Recuperacion\EstrategiaRecuperacion;
use App\Services\Recuperacion\RecuperacionPasswordService;
use App\Services\Recuperacion\RecuperacionPorCorreo;
use App\Services\Recuperacion\RecuperacionPorLlamada;
use App\Services\Recuperacion\RecuperacionPorPregunta;
use App\Services\Recuperacion\RecuperacionPorSms;
use App\Services\Sesiones\SesionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag([
            RecuperacionPorCorreo::class,
            RecuperacionPorSms::class,
            RecuperacionPorLlamada::class,
            RecuperacionPorPregunta::class,
        ], EstrategiaRecuperacion::class);

        $this->app->bind(RecuperacionPasswordService::class, fn ($app) => new RecuperacionPasswordService(
            $app->tagged(EstrategiaRecuperacion::class),
            $app->make(SesionService::class),
        ));
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Orden::observe(OrdenObserver::class);
        DetalleOrden::observe(DetalleOrdenObserver::class);

        Password::defaults(fn () => Password::min(10)->letters()->mixedCase()->numbers());

        Gate::define('administrar', fn (Usuario $usuario) => $usuario->esGerente());
    }
}
