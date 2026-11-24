<x-layouts.app :titulo="'Orden '.$orden->folio" :descripcion="'Capturada el '.$orden->created_at->format('d/m/Y H:i').' por '.$orden->usuario->nombre">
    <x-slot:acciones>
        <a href="{{ route('ordenes.index') }}" class="btn btn-secundario"><x-icono nombre="atras" /> Órdenes</a>
    </x-slot:acciones>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="flex flex-col gap-6">
            <section class="tarjeta overflow-x-auto">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-crema-200 px-5 py-4">
                    <div class="flex flex-wrap gap-2">
                        <x-canal :canal="$orden->canal_origen" grande />
                        <x-estado :estado="$orden->estado" class="px-3.5 py-1.5 text-sm" />
                    </div>
                    @include('pos._acciones-orden', ['orden' => $orden])
                </div>
                <table class="tabla">
                    <thead><tr><th>Producto</th><th>Estación</th><th>Preparación</th><th class="text-right">Cant.</th><th class="text-right">P. unitario</th><th class="text-right">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($orden->detalles as $detalle)
                            <tr>
                                <td class="font-semibold">{{ $detalle->nombre_producto_snapshot }}</td>
                                <td>{{ $detalle->estacion->nombre }}</td>
                                <td><x-estado :estado="$detalle->estado_preparacion" /></td>
                                <td class="text-right">{{ $detalle->cantidad }}</td>
                                <td class="text-right">${{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                                <td class="text-right font-semibold">${{ number_format((float) $detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="5" class="text-right font-bold">Total</td><td class="text-right text-lg font-bold">${{ number_format((float) $orden->total, 2) }}</td></tr>
                    </tfoot>
                </table>
                @if ($orden->nota)
                    <p class="border-t border-crema-200 px-5 py-4 text-sm whitespace-pre-line"><span class="font-bold">Nota:</span> {{ $orden->nota }}</p>
                @endif
            </section>

            <section class="tarjeta overflow-hidden" aria-labelledby="titulo-consumos">
                <h2 id="titulo-consumos" class="border-b border-crema-200 px-5 py-4 font-bold">Movimientos de inventario de esta venta</h2>
                @php($movimientos = $orden->detalles->flatMap->movimientos->sortBy('id_movimiento'))
                @if ($movimientos->isEmpty())
                    <p class="px-5 py-6 text-sm text-espresso-500">Sin movimientos. Los ingredientes se descuentan únicamente al cerrar la venta.</p>
                @else
                    <table class="tabla">
                        <thead><tr><th>Ingrediente</th><th>Tipo</th><th class="text-right">Cantidad</th><th class="text-right">Saldo resultante</th><th>Fecha</th></tr></thead>
                        <tbody>
                            @foreach ($movimientos as $movimiento)
                                <tr>
                                    <td class="font-semibold">{{ $movimiento->ingrediente->nombre }}</td>
                                    <td>{{ $movimiento->tipo->etiqueta() }}</td>
                                    <td @class(['text-right', 'text-red-700' => ! $movimiento->incrementa, 'text-emerald-700' => $movimiento->incrementa])>
                                        {{ $movimiento->incrementa ? '+' : '−' }}<x-cantidad :valor="$movimiento->cantidad" :unidad="$movimiento->ingrediente->unidad_medida" />
                                    </td>
                                    <td class="text-right"><x-cantidad :valor="$movimiento->stock_resultante" /></td>
                                    <td class="text-sm text-espresso-500">{{ $movimiento->fecha_movimiento->format('d/m H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        </div>

        <aside class="flex flex-col gap-6">
            <section class="tarjeta p-5" aria-labelledby="titulo-historial">
                <h2 id="titulo-historial" class="mb-4 font-bold">Historial de estados</h2>
                <ol class="relative flex flex-col gap-4 border-l-2 border-crema-200 pl-5">
                    @foreach ($orden->historial->sortBy('id_historial') as $cambio)
                        <li class="relative">
                            <span class="absolute top-1 -left-[1.6rem] size-3 rounded-full border-2 border-white bg-caramelo-500"></span>
                            <x-estado :estado="$cambio->estado_nuevo" />
                            <p class="mt-1 text-xs text-espresso-500">{{ $cambio->fecha_cambio->format('d/m H:i:s') }} · {{ $cambio->usuario?->nombre ?? 'Sistema' }}</p>
                        </li>
                    @endforeach
                </ol>
            </section>

            @if ($orden->estaCerrada())
                <section class="tarjeta p-5" aria-labelledby="titulo-cierre">
                    <h2 id="titulo-cierre" class="font-bold">Venta cerrada</h2>
                    <p class="mt-1 text-sm text-espresso-600">{{ $orden->cerrada_en->format('d/m/Y H:i') }} · {{ ucfirst($orden->metodo_pago_registrado ?? 'sin método registrado') }}</p>
                    @if (auth()->user()->esGerente())
                        <form method="POST" action="{{ route('ordenes.revertir', $orden) }}" class="mt-4 flex flex-col gap-3" data-enviar-una-vez
                              data-confirmar="¿Revertir la venta? Los ingredientes regresarán al inventario y la orden quedará cancelada.">
                            @csrf
                            <div>
                                <label for="motivo" class="etiqueta">Motivo de la reversión</label>
                                <textarea id="motivo" name="motivo" rows="2" minlength="5" maxlength="200" required class="campo">{{ old('motivo') }}</textarea>
                                @error('motivo')<p class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="btn btn-peligro">Revertir venta</button>
                        </form>
                    @endif
                </section>
            @endif
        </aside>
    </div>
</x-layouts.app>
