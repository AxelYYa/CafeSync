<x-layouts.app titulo="Punto de venta" descripcion="Toca un producto para agregarlo. Precio, estación y receta se toman del catálogo del servidor." ancho="max-w-[110rem]">
    <x-slot:acciones>
        <a href="{{ route('ordenes.index') }}" class="btn btn-secundario"><x-icono nombre="taza" /> Órdenes en curso</a>
    </x-slot:acciones>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_26rem]" data-pos data-items-previos='@json(array_values(old('items', [])))'>
        <section aria-label="Catálogo" class="flex flex-col gap-6">
            @forelse ($catalogo as $estacion => $filas)
                <div>
                    <h2 class="mb-3 flex items-center gap-2 text-sm font-bold tracking-wide text-espresso-600 uppercase">
                        <x-icono nombre="llama" clase="size-4" /> {{ $estacion }}
                    </h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5">
                        @foreach ($filas as $fila)
                            @php
                                $producto = $fila['producto'];
                                $agotado = ! $fila['tiene_receta'] || $fila['maximo'] === 0;
                            @endphp
                            <button type="button"
                                    data-producto="{{ $producto->id_producto }}"
                                    data-nombre="{{ $producto->nombre }}"
                                    data-precio="{{ $producto->precio }}"
                                    data-maximo="{{ $fila['maximo'] ?? 0 }}"
                                    @disabled($agotado)
                                    class="tarjeta group flex min-h-28 flex-col items-start justify-between gap-2 p-4 text-left transition hover:border-caramelo-400 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:border-crema-200">
                                <span class="text-base leading-snug font-bold">{{ $producto->nombre }}</span>
                                <span class="flex w-full items-end justify-between gap-2">
                                    <span class="text-lg font-bold text-caramelo-700">${{ number_format((float) $producto->precio, 2) }}</span>
                                    @if (! $fila['tiene_receta'])
                                        <span class="insignia bg-red-100 text-red-800">Sin receta</span>
                                    @elseif ($fila['maximo'] === 0)
                                        <span class="insignia bg-red-100 text-red-800">Agotado</span>
                                    @elseif ($fila['maximo'] <= 5)
                                        <span class="insignia bg-amber-100 text-amber-800">Quedan {{ $fila['maximo'] }}</span>
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @empty
                <x-vacio icono="receta" titulo="No hay productos a la venta">El gerente debe dar de alta productos activos con estación y receta.</x-vacio>
            @endforelse
        </section>

        <aside aria-label="Orden actual" class="xl:sticky xl:top-6 xl:self-start">
            <form method="POST" action="{{ route('pos.ordenes.store') }}" class="tarjeta flex flex-col" data-formulario-orden>
                @csrf
                <div class="border-b border-crema-200 p-5">
                    <h2 class="text-lg font-bold">Orden actual</h2>

                    <fieldset class="mt-4">
                        <legend class="etiqueta">Canal de origen</legend>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($canales as $canal)
                                <label class="flex min-h-16 cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-crema-200 bg-white text-xs font-bold transition has-checked:border-caramelo-500 has-checked:bg-caramelo-500/10 hover:bg-crema-100">
                                    <input type="radio" name="canal_origen" value="{{ $canal->value }}" class="sr-only" required @checked(old('canal_origen', 'mostrador') === $canal->value)>
                                    <x-icono :nombre="$canal->icono()" clase="size-6" />
                                    {{ $canal->etiqueta() }}
                                </label>
                            @endforeach
                        </div>
                        @error('canal_origen')
                            <p class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </fieldset>
                </div>

                <div class="max-h-[40vh] min-h-40 overflow-y-auto p-3 xl:max-h-[calc(100dvh-30rem)]">
                    <ul class="flex flex-col gap-2" data-lineas></ul>
                    <p class="px-2 py-10 text-center text-sm text-espresso-500" data-orden-vacia>Toca un producto del catálogo para empezar.</p>
                    @error('items')
                        <p class="mx-2 rounded-xl bg-red-50 px-3 py-2 text-sm font-medium text-red-800">{{ $message }}</p>
                    @enderror
                    @error('items.*')
                        <p class="mx-2 mt-2 rounded-xl bg-red-50 px-3 py-2 text-sm font-medium text-red-800">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-4 border-t border-crema-200 p-5">
                    <div>
                        <label for="nota" class="etiqueta">Nota para preparación <span class="font-normal text-espresso-500">(opcional)</span></label>
                        <textarea id="nota" name="nota" rows="2" maxlength="500" class="campo" placeholder="Ej. sin azúcar, leche deslactosada…">{{ old('nota') }}</textarea>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-sm font-semibold text-espresso-600"><span data-unidades>0</span> producto(s)</span>
                        <span class="text-3xl font-bold" data-total>$0.00</span>
                    </div>
                    <p class="-mt-2 text-xs text-espresso-500">Importe de referencia; el servidor recalcula el total con los precios vigentes.</p>
                    <div class="grid grid-cols-[auto_1fr] gap-2">
                        <button type="button" class="btn btn-secundario btn-lg" data-vaciar-orden disabled>Vaciar</button>
                        <button type="submit" class="btn btn-acento btn-lg" data-enviar-orden disabled>Enviar a preparación</button>
                    </div>
                </div>
            </form>
        </aside>
    </div>

    <template id="plantilla-linea">
        <li class="flex items-center gap-3 rounded-xl bg-crema-50 px-3 py-2">
            <div class="min-w-0 flex-1">
                <p class="truncate font-semibold" data-nombre></p>
                <p class="text-sm text-espresso-500" data-subtotal></p>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" class="btn btn-secundario size-11 p-0" data-restar aria-label="Quitar una unidad"><x-icono nombre="menos" /></button>
                <span class="w-8 text-center text-lg font-bold tabular-nums" data-cantidad></span>
                <button type="button" class="btn btn-secundario size-11 p-0" data-sumar aria-label="Agregar una unidad"><x-icono nombre="mas" /></button>
            </div>
        </li>
    </template>
</x-layouts.app>
