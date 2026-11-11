@php
    $esGerente = $usuario->esGerente();
    $accesos = [
        ['ruta' => 'pos.index', 'icono' => 'recibo', 'titulo' => 'Punto de venta', 'texto' => 'Captura una orden y envíala a preparación.'],
        ['ruta' => 'kds.index', 'icono' => 'pantalla', 'titulo' => 'Pantallas KDS', 'texto' => 'Abre la pantalla de tu estación.'],
        ['ruta' => 'asistencia.index', 'icono' => 'reloj', 'titulo' => 'Reloj checador', 'texto' => 'Registra entrada o salida con tu PIN.'],
        ['ruta' => 'tareas.index', 'icono' => 'tareas', 'titulo' => 'Tareas del turno', 'texto' => 'Consulta y marca tu checklist.'],
    ];
@endphp

<x-layouts.app titulo="Hola, {{ $usuario->nombre }}" :descripcion="now()->translatedFormat('l j \\d\\e F, H:i')">
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Resumen">
        <a href="{{ route('ordenes.index') }}" class="tarjeta flex flex-col gap-2 p-5 hover:border-caramelo-400">
            <span class="text-sm font-semibold text-espresso-600">Órdenes en curso</span>
            <span class="text-4xl font-bold">{{ $ordenesActivas }}</span>
            <span class="text-xs text-espresso-500">Capturadas, en preparación, listas o entregadas sin cerrar</span>
        </a>
        <a href="{{ route('tareas.index') }}" class="tarjeta flex flex-col gap-2 p-5 hover:border-caramelo-400">
            <span class="text-sm font-semibold text-espresso-600">Tareas pendientes</span>
            <span class="text-4xl font-bold">{{ $tareasPendientes }}</span>
            <span class="text-xs text-espresso-500">Asignadas a ti o generales del turno</span>
        </a>
        @if ($esGerente)
            <div class="tarjeta flex flex-col gap-2 p-5">
                <span class="text-sm font-semibold text-espresso-600">Ventas cerradas hoy</span>
                <span class="text-4xl font-bold">${{ number_format((float) $ventasHoy, 2) }}</span>
                <span class="text-xs text-espresso-500">Suma de órdenes cerradas desde las 00:00</span>
            </div>
            <div @class(['tarjeta flex flex-col gap-2 p-5', 'border-red-300 bg-red-50' => $bajoMinimo->isNotEmpty()])>
                <span class="text-sm font-semibold text-espresso-600">Ingredientes bajo mínimo</span>
                <span @class(['text-4xl font-bold', 'text-red-700' => $bajoMinimo->isNotEmpty()])>{{ $bajoMinimo->count() }} <span class="text-lg font-semibold text-espresso-500">/ {{ $totalIngredientes }}</span></span>
                <span class="text-xs text-espresso-500">Ingredientes activos con existencia en o por debajo de su mínimo</span>
            </div>
        @endif
    </section>

    @if ($esGerente && $bajoMinimo->isNotEmpty())
        <section class="tarjeta overflow-hidden" aria-labelledby="titulo-bajo-minimo">
            <div class="flex items-center gap-3 border-b border-red-200 bg-red-50 px-5 py-4 text-red-900">
                <x-icono nombre="alerta" />
                <h2 id="titulo-bajo-minimo" class="font-bold">Reabastece estos ingredientes</h2>
            </div>
            <table class="tabla">
                <thead><tr><th>Ingrediente</th><th class="text-right">Existencia</th><th class="text-right">Mínimo</th></tr></thead>
                <tbody>
                    @foreach ($bajoMinimo as $ingrediente)
                        <tr>
                            <td class="font-semibold">{{ $ingrediente->nombre }}</td>
                            <td class="text-right text-red-700"><x-cantidad :valor="$ingrediente->stock_actual" :unidad="$ingrediente->unidad_medida" /></td>
                            <td class="text-right"><x-cantidad :valor="$ingrediente->stock_minimo" :unidad="$ingrediente->unidad_medida" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    <section aria-labelledby="titulo-accesos">
        <h2 id="titulo-accesos" class="mb-3 text-lg font-bold">Accesos rápidos</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($accesos as $acceso)
                <a href="{{ route($acceso['ruta']) }}" class="tarjeta group flex items-start gap-4 p-5 hover:border-caramelo-400">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-crema-100 text-espresso-700 group-hover:bg-caramelo-500 group-hover:text-espresso-950">
                        <x-icono :nombre="$acceso['icono']" clase="size-6" />
                    </span>
                    <span>
                        <span class="block font-bold">{{ $acceso['titulo'] }}</span>
                        <span class="mt-1 block text-sm text-espresso-600">{{ $acceso['texto'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.app>
