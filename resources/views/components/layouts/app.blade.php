@props(['titulo', 'descripcion' => null, 'ancho' => 'max-w-7xl'])

@php
    $usuario = auth()->user();
    $esGerente = $usuario->esGerente();

    $operacion = [
        ['ruta' => 'dashboard', 'activa' => 'dashboard', 'icono' => 'panel', 'texto' => 'Panel'],
        ['ruta' => 'pos.index', 'activa' => 'pos.*', 'icono' => 'recibo', 'texto' => 'Punto de venta'],
        ['ruta' => 'ordenes.index', 'activa' => 'ordenes.*', 'icono' => 'taza', 'texto' => 'Órdenes'],
        ['ruta' => 'kds.index', 'activa' => 'kds.*', 'icono' => 'pantalla', 'texto' => 'Pantallas KDS'],
        ['ruta' => 'asistencia.index', 'activa' => 'asistencia.*', 'icono' => 'reloj', 'texto' => 'Reloj checador'],
        ['ruta' => 'tareas.index', 'activa' => 'tareas.*', 'icono' => 'tareas', 'texto' => 'Mis tareas'],
    ];

    $administracion = $esGerente ? array_values(array_filter([
        ['ruta' => 'admin.inventario.index', 'activa' => 'admin.inventario.*', 'icono' => 'inventario', 'texto' => 'Inventario'],
        ['ruta' => 'admin.ingredientes.index', 'activa' => 'admin.ingredientes.*', 'icono' => 'inventario', 'texto' => 'Ingredientes'],
        ['ruta' => 'admin.productos.index', 'activa' => 'admin.productos.*|admin.recetas.*', 'icono' => 'receta', 'texto' => 'Productos y recetas'],
        ['ruta' => 'admin.estaciones.index', 'activa' => 'admin.estaciones.*', 'icono' => 'llama', 'texto' => 'Estaciones'],
        ['ruta' => 'admin.asignaciones.index', 'activa' => 'admin.asignaciones.*|admin.plantillas.*', 'icono' => 'tareas', 'texto' => 'Asignar tareas'],
        ['ruta' => 'admin.asistencias.index', 'activa' => 'admin.asistencias.*', 'icono' => 'reloj', 'texto' => 'Asistencias'],
        ['ruta' => 'admin.reportes.ventas', 'activa' => 'admin.reportes.*', 'icono' => 'reporte', 'texto' => 'Reporte de ventas'],
        ['ruta' => 'admin.usuarios.index', 'activa' => 'admin.usuarios.*', 'icono' => 'usuarios', 'texto' => 'Usuarios'],
        ['ruta' => 'admin.tokens.index', 'activa' => 'admin.tokens.*', 'icono' => 'llave', 'texto' => 'Tokens de pantallas'],
    ], fn (array $enlace) => Route::has($enlace['ruta']))) : [];

    $estaActiva = fn (array $enlace) => request()->routeIs(...explode('|', $enlace['activa']));
@endphp

<x-layouts.base :titulo="$titulo">
    <div class="lg:grid lg:min-h-dvh lg:grid-cols-[17rem_1fr]">
        <aside id="navegacion" data-navegacion class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col overflow-y-auto bg-espresso-900 text-crema-100 lg:sticky lg:top-0 lg:flex lg:h-dvh lg:w-auto">
            <div class="flex items-center gap-3 px-5 py-5">
                <span class="grid size-10 place-items-center rounded-xl bg-caramelo-500 text-espresso-950"><x-icono nombre="taza" /></span>
                <span class="text-lg font-bold text-crema-50">CaféSync</span>
            </div>

            <nav class="flex flex-1 flex-col gap-6 px-3 pb-6" aria-label="Principal">
                @foreach (['Operación' => $operacion, 'Administración' => $administracion] as $grupo => $enlaces)
                    @if ($enlaces !== [])
                        <div>
                            <p class="px-3 pb-2 text-xs font-bold tracking-wider text-crema-300/50 uppercase">{{ $grupo }}</p>
                            <ul class="flex flex-col gap-0.5">
                                @foreach ($enlaces as $enlace)
                                    <li>
                                        <a href="{{ route($enlace['ruta']) }}"
                                           @class([
                                               'flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-medium transition',
                                               'bg-caramelo-500 text-espresso-950' => $estaActiva($enlace),
                                               'text-crema-200 hover:bg-espresso-800 hover:text-crema-50' => ! $estaActiva($enlace),
                                           ])
                                           @if ($estaActiva($enlace)) aria-current="page" @endif>
                                            <x-icono :nombre="$enlace['icono']" />
                                            {{ $enlace['texto'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </nav>

            <div class="border-t border-espresso-700 p-4">
                <a href="{{ route('cuenta.seguridad') }}" class="flex items-center gap-3 rounded-xl p-2 hover:bg-espresso-800">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-espresso-700 text-sm font-bold text-crema-50">{{ mb_strtoupper(mb_substr($usuario->nombre, 0, 1)) }}</span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-semibold text-crema-50">{{ $usuario->nombre }}</span>
                        <span class="block text-xs text-crema-300/70">{{ $usuario->rol->nombre === 'gerente' ? 'Gerente' : 'Empleado' }} · Seguridad de la cuenta</span>
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn w-full justify-start text-crema-200 hover:bg-espresso-800 hover:text-crema-50">
                        <x-icono nombre="salir" /> Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>
        <div data-cerrar-navegacion class="fixed inset-0 z-30 hidden bg-espresso-950/50 lg:hidden"></div>

        <div class="min-w-0">
            <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-crema-200 bg-crema-50/90 px-4 py-3 backdrop-blur lg:hidden">
                <button type="button" data-abrir-navegacion class="btn btn-fantasma -ml-2 px-2" aria-controls="navegacion" aria-label="Abrir menú">
                    <x-icono nombre="menu" clase="size-6" />
                </button>
                <span class="font-bold">CaféSync</span>
            </header>

            <main class="mx-auto flex w-full {{ $ancho }} flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-espresso-900 sm:text-3xl">{{ $titulo }}</h1>
                        @if ($descripcion)
                            <p class="mt-1 text-espresso-600">{{ $descripcion }}</p>
                        @endif
                    </div>
                    @isset($acciones)
                        <div class="flex flex-wrap gap-2">{{ $acciones }}</div>
                    @endisset
                </div>

                <x-avisos />

                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.base>
