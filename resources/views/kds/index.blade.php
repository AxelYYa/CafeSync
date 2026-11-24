<x-layouts.app titulo="Pantallas KDS" descripcion="Elige la estación de este dispositivo. Cada pantalla muestra solo los productos que le corresponden.">
    @if ($estaciones->isEmpty())
        <x-vacio icono="llama" titulo="No hay estaciones activas">El gerente debe dar de alta al menos una estación de preparación.</x-vacio>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($estaciones as $estacion)
                <a href="{{ route('kds.show', $estacion) }}" class="tarjeta group flex min-h-40 flex-col justify-between gap-4 p-6 hover:border-caramelo-400">
                    <div class="flex items-start justify-between gap-3">
                        <span class="grid size-14 place-items-center rounded-2xl bg-espresso-800 text-caramelo-400 group-hover:bg-caramelo-500 group-hover:text-espresso-950">
                            <x-icono nombre="llama" clase="size-7" />
                        </span>
                        <span @class([
                            'insignia px-3 py-1.5 text-sm',
                            'bg-amber-100 text-amber-800' => $estacion->pendientes_count > 0,
                            'bg-stone-100 text-stone-600' => $estacion->pendientes_count === 0,
                        ])>{{ $estacion->pendientes_count }} por preparar</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">{{ $estacion->nombre }}</h2>
                        @if ($estacion->descripcion)
                            <p class="mt-1 text-sm text-espresso-600">{{ $estacion->descripcion }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
