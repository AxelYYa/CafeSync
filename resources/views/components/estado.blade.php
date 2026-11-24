{{-- Insignia de estado para órdenes (EstadoOrden), renglones (EstadoPreparacion) y tareas (EstadoTarea). --}}
@props(['estado'])

@php
    $estilos = [
        'capturada' => 'bg-stone-200 text-stone-700',
        'pendiente' => 'bg-stone-200 text-stone-700',
        'en_preparacion' => 'bg-amber-100 text-amber-800',
        'en_proceso' => 'bg-amber-100 text-amber-800',
        'lista' => 'bg-emerald-100 text-emerald-800',
        'completada' => 'bg-emerald-100 text-emerald-800',
        'entregada' => 'bg-sky-100 text-sky-800',
        'cerrada' => 'bg-espresso-800 text-crema-50',
        'cancelada' => 'bg-red-100 text-red-800',
    ];
@endphp

<span {{ $attributes->class(['insignia', $estilos[$estado->value] ?? 'bg-stone-200 text-stone-700']) }}>{{ $estado->etiqueta() }}</span>
