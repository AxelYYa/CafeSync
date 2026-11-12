{{-- Campo de formulario con etiqueta, ayuda y error del validador. `bolsa` es el error bag del formulario. --}}
@props(['nombre', 'etiqueta', 'tipo' => 'text', 'valor' => null, 'ayuda' => null, 'id' => null, 'bolsa' => 'default'])

@php
    $id ??= 'campo-'.str_replace(['.', '[', ']'], '-', $nombre);
    $clave = str_replace(['[', ']'], ['.', ''], $nombre);
@endphp

<div>
    <label for="{{ $id }}" class="etiqueta">{{ $etiqueta }}</label>
    <input
        id="{{ $id }}"
        name="{{ $nombre }}"
        type="{{ $tipo }}"
        @if ($tipo !== 'password') value="{{ old($clave, $valor) }}" @endif
        @error($clave, $bolsa) aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror
        {{ $attributes->merge(['class' => 'campo']) }}
    >
    @if ($ayuda)
        <p class="mt-1 text-xs text-espresso-500">{{ $ayuda }}</p>
    @endif
    @error($clave, $bolsa)
        <p id="{{ $id }}-error" class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>
    @enderror
</div>
