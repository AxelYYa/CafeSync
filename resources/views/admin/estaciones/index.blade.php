<x-layouts.app titulo="Estaciones" descripcion="Zonas de preparación. Cada producto se envía a su estación y cada pantalla KDS muestra solo lo suyo.">
    <x-slot:acciones>
        <a href="{{ route('admin.estaciones.create') }}" class="btn btn-acento"><x-icono nombre="mas" /> Nueva estación</a>
    </x-slot:acciones>

    @if ($estaciones->isEmpty())
        <x-vacio icono="llama" titulo="Aún no hay estaciones">Crea al menos una para que los productos tengan dónde prepararse.</x-vacio>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($estaciones as $estacion)
                <article @class(['tarjeta flex flex-col gap-4 p-5', 'opacity-60' => ! $estacion->activa])>
                    <header class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="grid size-12 place-items-center rounded-xl bg-espresso-800 text-caramelo-400"><x-icono nombre="llama" clase="size-6" /></span>
                            <div>
                                <h2 class="text-lg font-bold">{{ $estacion->nombre }}</h2>
                                <span @class(['insignia', 'bg-emerald-100 text-emerald-800' => $estacion->activa, 'bg-stone-200 text-stone-700' => ! $estacion->activa])>{{ $estacion->activa ? 'Activa' : 'Inactiva' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.estaciones.edit', $estacion) }}" class="btn btn-secundario">Editar</a>
                    </header>
                    @if ($estacion->descripcion)
                        <p class="text-sm text-espresso-600">{{ $estacion->descripcion }}</p>
                    @endif
                    <dl class="mt-auto grid grid-cols-2 gap-3 border-t border-crema-100 pt-4 text-sm">
                        <div>
                            <dt class="text-espresso-500">Productos activos</dt>
                            <dd class="text-xl font-bold">{{ $estacion->productos_activos_count }} <span class="text-sm font-normal text-espresso-500">de {{ $estacion->productos_count }}</span></dd>
                        </div>
                        <div>
                            <dt class="text-espresso-500">Por preparar ahora</dt>
                            <dd @class(['text-xl font-bold', 'text-amber-700' => $estacion->pendientes_count > 0])>{{ $estacion->pendientes_count }}</dd>
                        </div>
                    </dl>
                    @if ($estacion->activa)
                        <a href="{{ route('kds.show', $estacion) }}" class="btn btn-fantasma justify-start px-0"><x-icono nombre="pantalla" /> Abrir su pantalla KDS</a>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</x-layouts.app>
