<x-layouts.app titulo="Órdenes" descripcion="Seguimiento de las órdenes del POS: entrega, cierre de venta y cancelación.">
    <x-slot:acciones>
        <a href="{{ route('pos.index') }}" class="btn btn-acento"><x-icono nombre="mas" /> Nueva orden</a>
    </x-slot:acciones>

    <form method="GET" action="{{ route('ordenes.index') }}" class="tarjeta flex flex-wrap items-end gap-3 p-4">
        <div>
            <label for="filtro-estado" class="etiqueta">Estado</label>
            <select id="filtro-estado" name="estado" class="campo">
                <option value="">En curso</option>
                @foreach ($estados as $estado)
                    <option value="{{ $estado->value }}" @selected(($filtros['estado'] ?? null) === $estado->value)>{{ $estado->etiqueta() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filtro-canal" class="etiqueta">Canal</label>
            <select id="filtro-canal" name="canal" class="campo">
                <option value="">Todos</option>
                @foreach ($canales as $canal)
                    <option value="{{ $canal->value }}" @selected(($filtros['canal'] ?? null) === $canal->value)>{{ $canal->etiqueta() }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primario">Filtrar</button>
        @if (array_filter($filtros))
            <a href="{{ route('ordenes.index') }}" class="btn btn-fantasma">Quitar filtros</a>
        @endif
    </form>

    @if ($ordenes->isEmpty())
        <x-vacio icono="taza" titulo="No hay órdenes con estos filtros">Las órdenes nuevas del POS aparecen aquí en cuanto se envían.</x-vacio>
    @else
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($ordenes as $orden)
                <article class="tarjeta flex flex-col gap-4 p-5">
                    <header class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <a href="{{ route('ordenes.show', $orden) }}" class="text-lg font-bold hover:text-caramelo-700">{{ $orden->folio }}</a>
                            <p class="text-sm text-espresso-500">{{ $orden->created_at->format('H:i') }} · {{ $orden->usuario->nombre }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-canal :canal="$orden->canal_origen" />
                            <x-estado :estado="$orden->estado" />
                        </div>
                    </header>
                    <ul class="flex flex-col gap-1.5 text-sm">
                        @foreach ($orden->detalles as $detalle)
                            <li class="flex items-center justify-between gap-3">
                                <span><span class="font-bold">{{ $detalle->cantidad }}×</span> {{ $detalle->nombre_producto_snapshot }}</span>
                                <x-estado :estado="$detalle->estado_preparacion" />
                            </li>
                        @endforeach
                    </ul>
                    <footer class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-crema-100 pt-4">
                        <span class="text-xl font-bold">${{ number_format((float) $orden->total, 2) }}</span>
                        @include('pos._acciones-orden', ['orden' => $orden])
                    </footer>
                </article>
            @endforeach
        </div>
        {{ $ordenes->links() }}
    @endif
</x-layouts.app>
