@php($duracion = fn (?int $minutos) => $minutos === null ? '—' : intdiv($minutos, 60).' h '.str_pad((string) ($minutos % 60), 2, '0', STR_PAD_LEFT).' min')

<x-layouts.app titulo="Asistencias" descripcion="Historial del reloj checador. Las horas son las registradas por el servidor; no es un cálculo de nómina.">
    <form method="GET" action="{{ route('admin.asistencias.index') }}" class="tarjeta flex flex-wrap items-end gap-3 p-4">
        <x-campo nombre="desde" etiqueta="Desde" tipo="date" :valor="$desde->toDateString()" />
        <x-campo nombre="hasta" etiqueta="Hasta" tipo="date" :valor="$hasta->toDateString()" />
        <div>
            <label for="id_usuario" class="etiqueta">Empleado</label>
            <select id="id_usuario" name="id_usuario" class="campo">
                <option value="">Todos</option>
                @foreach ($empleados as $empleado)
                    <option value="{{ $empleado->id_usuario }}" @selected((int) ($filtros['id_usuario'] ?? 0) === $empleado->id_usuario)>{{ $empleado->nombre }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primario">Consultar</button>
    </form>

    @if ($jornadas->isEmpty())
        <x-vacio icono="reloj" titulo="Sin marcas en este periodo">Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}.</x-vacio>
    @else
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Totales por empleado">
            @foreach ($totales as $total)
                <article class="tarjeta p-5">
                    <p class="font-bold">{{ $total['usuario'] }}</p>
                    <p class="mt-2 text-3xl font-bold">{{ $duracion($total['minutos']) }}</p>
                    <p class="mt-1 text-sm text-espresso-500">
                        {{ $total['jornadas'] }} jornada(s)
                        @if ($total['abiertas'] > 0)
                            · <span class="font-semibold text-amber-700">{{ $total['abiertas'] }} sin salida</span>
                        @endif
                    </p>
                </article>
            @endforeach
        </section>

        <div class="tarjeta overflow-x-auto">
            <table class="tabla">
                <thead><tr><th>Empleado</th><th>Entrada</th><th>Salida</th><th>Turno</th><th class="text-right">Duración</th></tr></thead>
                <tbody>
                    @foreach ($jornadas as $jornada)
                        <tr>
                            <td class="font-semibold">{{ $jornada['usuario'] }}</td>
                            <td class="font-mono">{{ $jornada['entrada']->fecha_hora->format('d/m/Y H:i:s') }}</td>
                            <td class="font-mono">
                                @if ($jornada['salida'])
                                    {{ $jornada['salida']->fecha_hora->format('d/m/Y H:i:s') }}
                                @else
                                    <span class="insignia bg-amber-100 text-amber-800">Sin salida</span>
                                @endif
                            </td>
                            <td>{{ $jornada['entrada']->turno?->nombre ?? '—' }}</td>
                            <td class="text-right font-semibold">{{ $duracion($jornada['minutos']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.app>
