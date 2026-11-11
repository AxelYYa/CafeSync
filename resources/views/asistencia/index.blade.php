<x-layouts.app titulo="Reloj checador" descripcion="Escribe tu PIN y elige entrada o salida. La hora la registra el servidor." ancho="max-w-6xl">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,28rem)_minmax(0,1fr)]">
        <section class="tarjeta p-6" aria-label="Registrar asistencia">
            <p class="text-center text-5xl font-bold tabular-nums" data-reloj data-servidor="{{ now()->getTimestampMs() }}" data-segundos>{{ now()->format('H:i:s') }}</p>
            <p class="mt-1 text-center text-sm text-espresso-500">{{ now()->translatedFormat('l j \\d\\e F') }} · referencia del servidor; la marca usa la hora del servidor al guardar</p>

            <form method="POST" action="{{ route('asistencia.store') }}" class="mt-6 flex flex-col gap-4" data-teclado-pin data-enviar-una-vez>
                @csrf
                <div>
                    <label for="pin" class="sr-only">PIN</label>
                    <input id="pin" name="pin" type="password" inputmode="numeric" autocomplete="off" pattern="\d{4,6}" minlength="4" maxlength="6" required autofocus
                           placeholder="••••" class="campo h-16 text-center text-3xl tracking-[0.5em]" @error('pin') aria-invalid="true" aria-describedby="pin-error" @enderror>
                    @error('pin')
                        <p id="pin-error" role="alert" class="mt-2 flex items-center justify-center gap-2 text-base font-semibold text-red-700"><x-icono nombre="alerta" /> {{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-3 gap-2" aria-label="Teclado numérico">
                    @foreach (['1', '2', '3', '4', '5', '6', '7', '8', '9'] as $digito)
                        <button type="button" class="btn btn-secundario h-16 text-2xl" data-digito="{{ $digito }}">{{ $digito }}</button>
                    @endforeach
                    <button type="button" class="btn btn-fantasma h-16" data-borrar aria-label="Borrar último dígito"><x-icono nombre="atras" clase="size-6" /></button>
                    <button type="button" class="btn btn-secundario h-16 text-2xl" data-digito="0">0</button>
                    <button type="button" class="btn btn-fantasma h-16 text-sm" data-limpiar>Limpiar</button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="submit" name="tipo" value="entrada" class="btn btn-exito h-16 text-lg">Entrada</button>
                    <button type="submit" name="tipo" value="salida" class="btn btn-primario h-16 text-lg">Salida</button>
                </div>
                @error('tipo')
                    <p class="text-sm font-medium text-red-700">{{ $message }}</p>
                @enderror
            </form>
        </section>

        <section class="tarjeta overflow-hidden" aria-labelledby="titulo-marcas">
            <h2 id="titulo-marcas" class="border-b border-crema-200 px-5 py-4 font-bold">Marcas de hoy</h2>
            @if ($marcasHoy->isEmpty())
                <div class="p-5"><x-vacio icono="reloj" titulo="Aún no hay registros hoy" /></div>
            @else
                <table class="tabla">
                    <thead><tr><th>Hora</th><th>Empleado</th><th>Movimiento</th></tr></thead>
                    <tbody>
                        @foreach ($marcasHoy as $marca)
                            <tr>
                                <td class="font-mono text-base font-semibold">{{ $marca->fecha_hora->format('H:i:s') }}</td>
                                <td>{{ $marca->usuario->nombre }}</td>
                                <td>
                                    <span @class(['insignia', 'bg-emerald-100 text-emerald-800' => $marca->tipo->value === 'entrada', 'bg-stone-200 text-stone-700' => $marca->tipo->value === 'salida'])>{{ $marca->tipo->etiqueta() }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </div>
</x-layouts.app>
