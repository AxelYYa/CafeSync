@props(['titulo', 'subtitulo' => null])

<x-layouts.base :titulo="$titulo">
    <div class="grid min-h-dvh lg:grid-cols-[1fr_minmax(28rem,36rem)]">
        <aside class="relative hidden overflow-hidden bg-espresso-900 p-12 text-crema-100 lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -top-24 -right-24 size-96 rounded-full bg-caramelo-500/10"></div>
            <div class="absolute -bottom-32 -left-16 size-[28rem] rounded-full bg-crema-100/5"></div>
            <a href="{{ route('login') }}" class="relative flex items-center gap-3 text-2xl font-bold text-crema-50">
                <span class="grid size-11 place-items-center rounded-xl bg-caramelo-500 text-espresso-950"><x-icono nombre="taza" clase="size-6" /></span>
                CaféSync
            </a>
            <div class="relative max-w-lg">
                <p class="text-4xl leading-tight font-bold text-crema-50">Del pedido a la barra, sin tickets perdidos.</p>
                <p class="mt-4 text-lg text-crema-200/80">Caja, pantallas de preparación, recetas, inventario, asistencia y tareas del turno en una sola plataforma.</p>
            </div>
            <ul class="relative flex flex-wrap gap-2 text-sm text-crema-200/80">
                @foreach (['POS', 'KDS por estación', 'Help Mode', 'Inventario por receta', 'Reloj checador', 'Tareas'] as $modulo)
                    <li class="rounded-full border border-crema-100/15 px-3 py-1">{{ $modulo }}</li>
                @endforeach
            </ul>
        </aside>

        <main class="flex flex-col justify-center px-6 py-10 sm:px-12">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="grid size-10 place-items-center rounded-xl bg-espresso-800 text-caramelo-400"><x-icono nombre="taza" /></span>
                    <span class="text-xl font-bold">CaféSync</span>
                </div>
                <h1 class="text-3xl font-bold text-espresso-900">{{ $titulo }}</h1>
                @if ($subtitulo)
                    <p class="mt-2 text-espresso-600">{{ $subtitulo }}</p>
                @endif
                <div class="mt-6 flex flex-col gap-3">
                    <x-avisos :campos-generales="[]" />
                </div>
                <div class="mt-6">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>
</x-layouts.base>
