<x-layouts.base :titulo="'KDS · '.$estacion->nombre" clase-cuerpo="bg-espresso-950 text-crema-50">
    <div class="flex min-h-dvh flex-col"
         data-kds
         data-url-datos="{{ route('kds.datos', $estacion) }}"
         data-version="{{ $version }}"
         data-intervalo="{{ $intervalo }}">
        <header class="sticky top-0 z-10 flex flex-wrap items-center gap-4 border-b border-espresso-800 bg-espresso-950/95 px-5 py-3 backdrop-blur">
            <a href="{{ route('kds.index') }}" class="btn text-crema-200 hover:bg-espresso-800" aria-label="Cambiar de estación">
                <x-icono nombre="atras" />
            </a>
            <div class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-xl bg-caramelo-500 text-espresso-950"><x-icono nombre="llama" clase="size-6" /></span>
                <div>
                    <h1 class="text-2xl leading-tight font-bold">{{ $estacion->nombre }}</h1>
                    <p class="text-sm text-crema-300/70"><span data-kds-total>{{ $ordenes->count() }}</span> orden(es) en pantalla</p>
                </div>
            </div>
            <div class="ml-auto flex items-center gap-4">
                <p class="flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1.5 text-sm font-semibold text-emerald-300" data-kds-conexion role="status">
                    <span class="size-2.5 rounded-full bg-emerald-400" data-kds-punto></span>
                    <span data-kds-conexion-texto>Conectado</span>
                </p>
                <p class="text-3xl font-bold tabular-nums" data-reloj data-servidor="{{ now()->getTimestampMs() }}">{{ now()->format('H:i') }}</p>
            </div>
        </header>

        <div class="hidden border-b border-red-800 bg-red-900/60 px-5 py-3 text-red-50" data-kds-aviso-red role="alert">
            <p class="flex items-center gap-2 font-semibold"><x-icono nombre="sin-red" /> No se pudo actualizar la pantalla; revisa la conexión. Reintentando…</p>
        </div>
        <div class="hidden border-b border-red-800 bg-red-900/60 px-5 py-3 text-red-50" data-kds-error role="alert"></div>

        <main class="flex-1 p-5" data-kds-contenido>
            @include('kds._tarjetas', ['ordenes' => $ordenes, 'estacion' => $estacion])
        </main>
    </div>

    <dialog class="m-auto w-[min(48rem,calc(100vw-2rem))] rounded-3xl bg-white p-0 text-espresso-900 shadow-2xl" data-ayuda-dialogo aria-label="Receta del producto">
        <div class="max-h-[85dvh] overflow-y-auto p-6 sm:p-8" data-ayuda-contenido></div>
        <form method="dialog" class="border-t border-crema-200 p-4">
            <button class="btn btn-primario btn-lg w-full"><x-icono nombre="atras" /> Volver a la orden</button>
        </form>
    </dialog>
</x-layouts.base>
