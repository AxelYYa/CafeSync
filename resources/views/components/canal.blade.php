@props(['canal', 'grande' => false])

@php
    $estilos = [
        'mostrador' => 'bg-stone-200 text-mostrador',
        'drive_thru' => 'bg-blue-100 text-drive',
        'delivery' => 'bg-orange-100 text-delivery',
    ];
@endphp

<span {{ $attributes->class(['insignia', $estilos[$canal->value], 'px-3.5 py-1.5 text-sm' => $grande]) }}>
    <x-icono :nombre="$canal->icono()" :clase="$grande ? 'size-5' : 'size-4'" />
    {{ $canal->etiqueta() }}
</span>
