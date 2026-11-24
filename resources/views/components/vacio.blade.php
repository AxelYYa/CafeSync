@props(['icono' => 'info', 'titulo'])

<div {{ $attributes->class('flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-crema-300 px-6 py-12 text-center') }}>
    <span class="grid size-14 place-items-center rounded-full bg-crema-100 text-espresso-500">
        <x-icono :nombre="$icono" clase="size-7" />
    </span>
    <p class="text-base font-semibold text-espresso-800">{{ $titulo }}</p>
    @if ($slot->isNotEmpty())
        <p class="max-w-md text-sm text-espresso-500">{{ $slot }}</p>
    @endif
</div>
