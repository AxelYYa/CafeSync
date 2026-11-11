@props(['codigo', 'titulo', 'icono' => 'alerta'])

<x-layouts.base :titulo="$titulo">
    <main class="grid min-h-dvh place-items-center px-6 py-12">
        <div class="max-w-md text-center">
            <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-espresso-800 text-caramelo-400"><x-icono :nombre="$icono" clase="size-8" /></span>
            <p class="mt-6 text-sm font-bold tracking-widest text-caramelo-700">ERROR {{ $codigo }}</p>
            <h1 class="mt-2 text-3xl font-bold">{{ $titulo }}</h1>
            <p class="mt-3 text-espresso-600">{{ $slot }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-primario">Ir al inicio</a>
            </div>
        </div>
    </main>
</x-layouts.base>
