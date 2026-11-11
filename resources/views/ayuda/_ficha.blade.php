{{-- Ficha de Help Mode: se muestra sobre el KDS (AJAX) o dentro de ayuda.show. --}}
<article class="flex flex-col gap-6">
    <header class="flex items-start gap-4">
        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-caramelo-500 text-espresso-950"><x-icono nombre="receta" clase="size-7" /></span>
        <div>
            <h2 class="text-2xl font-bold sm:text-3xl">{{ $producto->nombre }}</h2>
            <p class="mt-1 text-espresso-600">
                {{ $producto->estacion->nombre }}
                @if ($receta) · Receta versión {{ $receta->version }} @endif
            </p>
        </div>
    </header>

    @if ($receta === null)
        <x-vacio icono="alerta" titulo="Este producto no tiene receta configurada">Pide al gerente que registre los ingredientes y los pasos.</x-vacio>
    @else
        <section aria-labelledby="ingredientes-{{ $producto->id_producto }}">
            <h3 id="ingredientes-{{ $producto->id_producto }}" class="mb-3 text-sm font-bold tracking-wide text-espresso-600 uppercase">Ingredientes por unidad</h3>
            <ul class="grid gap-2 sm:grid-cols-2">
                @foreach ($receta->detalles as $componente)
                    <li class="flex items-center justify-between gap-3 rounded-xl bg-crema-100 px-4 py-3">
                        <span class="text-lg font-semibold">{{ $componente->ingrediente->nombre }}</span>
                        <x-cantidad :valor="$componente->cantidad" :unidad="$componente->ingrediente->unidad_medida" class="text-lg font-bold" />
                    </li>
                @endforeach
            </ul>
        </section>

        <section aria-labelledby="pasos-{{ $producto->id_producto }}">
            <h3 id="pasos-{{ $producto->id_producto }}" class="mb-3 text-sm font-bold tracking-wide text-espresso-600 uppercase">Preparación</h3>
            @if ($receta->pasos() === [])
                <p class="text-espresso-500">La receta no tiene pasos registrados.</p>
            @else
                <ol class="flex flex-col gap-3">
                    @foreach ($receta->pasos() as $numero => $paso)
                        <li class="flex gap-4">
                            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-espresso-800 font-bold text-crema-50">{{ $numero + 1 }}</span>
                            <p class="pt-1 text-lg leading-relaxed">{{ $paso }}</p>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    @endif
</article>
