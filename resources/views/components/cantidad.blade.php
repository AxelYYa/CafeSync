{{-- Cantidad DECIMAL(12,3) legible: 1500.000 g → "1,500 g"; 0.250 l → "0.25 l". --}}
@props(['valor', 'unidad' => null])

@php
    $texto = number_format((float) $valor, 3, '.', ',');
    $texto = str_contains($texto, '.') ? rtrim(rtrim($texto, '0'), '.') : $texto;
@endphp

<span {{ $attributes->class('font-mono tabular-nums') }}>{{ $texto }}@if ($unidad) <span class="font-sans text-espresso-500">{{ $unidad }}</span>@endif</span>
