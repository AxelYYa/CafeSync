{{-- Tarjetas de preparación de una estación. Se vuelven a renderizar en cada cambio detectado por la consulta periódica. --}}
@use('App\Enums\EstadoPreparacion')

@if ($ordenes->isEmpty())
    <div class="flex min-h-[60dvh] flex-col items-center justify-center gap-4 text-center text-crema-300/70">
        <x-icono nombre="taza" clase="size-16" />
        <p class="text-2xl font-bold text-crema-100">Sin órdenes pendientes</p>
        <p>Las órdenes nuevas aparecerán aquí automáticamente.</p>
    </div>
@else
    <div class="grid grid-cols-[repeat(auto-fill,minmax(20rem,1fr))] gap-5">
        @foreach ($ordenes as $orden)
            @php($terminada = $orden->detalles->every(fn ($detalle) => $detalle->estado_preparacion === EstadoPreparacion::Lista))
            <article @class([
                        'flex flex-col overflow-hidden rounded-3xl border-2 bg-espresso-900 shadow-xl',
                        'border-espresso-700' => ! $terminada,
                        'border-emerald-600/60 opacity-70' => $terminada,
                     ])
                     data-orden="{{ $orden->id_orden }}"
                     data-creada="{{ $orden->created_at->getTimestamp() }}">
                <header class="flex items-start justify-between gap-3 border-b border-espresso-700 px-5 py-4">
                    <div>
                        <p class="text-2xl font-bold tracking-tight">#{{ str_pad((string) $orden->id_orden, 3, '0', STR_PAD_LEFT) }}</p>
                        <p class="text-xs text-crema-300/60">{{ $orden->folio }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <x-canal :canal="$orden->canal_origen" grande />
                        <p class="text-lg font-bold tabular-nums" data-transcurrido>{{ (int) $orden->created_at->diffInMinutes(now()) }} min</p>
                    </div>
                </header>

                @if ($orden->nota)
                    <p class="border-b border-espresso-700 bg-caramelo-500/15 px-5 py-3 text-base font-semibold text-caramelo-400 whitespace-pre-line">{{ $orden->nota }}</p>
                @endif

                <ul class="flex flex-1 flex-col divide-y divide-espresso-800">
                    @foreach ($orden->detalles as $detalle)
                        @php($siguiente = $detalle->estado_preparacion->siguiente())
                        <li class="flex flex-col gap-3 px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <p @class(['text-xl leading-snug font-bold', 'line-through decoration-2 opacity-60' => $detalle->estado_preparacion === EstadoPreparacion::Lista])>
                                    <span class="text-caramelo-400">{{ $detalle->cantidad }}×</span> {{ $detalle->nombre_producto_snapshot }}
                                </p>
                                <x-estado :estado="$detalle->estado_preparacion" class="shrink-0" />
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('ayuda.show', $detalle->id_producto) }}" data-ayuda class="btn btn-lg flex-none border border-espresso-600 text-crema-100 hover:bg-espresso-800">
                                    <x-icono nombre="receta" /> Receta
                                </a>
                                @if ($siguiente)
                                    <form method="POST" action="{{ route('kds.estado', $detalle) }}" class="flex-1" data-avanzar>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="estado" value="{{ $siguiente->value }}">
                                        <button type="submit" @class([
                                            'btn btn-lg w-full',
                                            'btn-acento' => $siguiente === EstadoPreparacion::EnPreparacion,
                                            'btn-exito' => $siguiente === EstadoPreparacion::Lista,
                                        ])>
                                            {{ $siguiente === EstadoPreparacion::EnPreparacion ? 'Iniciar' : 'Marcar lista' }}
                                        </button>
                                    </form>
                                @else
                                    <p class="flex flex-1 items-center justify-center gap-2 font-semibold text-emerald-400"><x-icono nombre="check" /> Lista</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if ($terminada)
                    <footer class="bg-emerald-700/30 px-5 py-3 text-center font-bold text-emerald-200">Todo listo en esta estación</footer>
                @endif
            </article>
        @endforeach
    </div>
@endif
