{{-- Mensajes de resultado: confirmación (session 'estado') y errores generales que no pertenecen a un campo. --}}
@props(['camposGenerales' => ['orden', 'kds', 'sesiones', 'mfa', 'items', 'usuario']])

@if (session('estado'))
    <div role="status" data-aviso class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900">
        <x-icono nombre="check" class="mt-0.5 size-5 shrink-0" />
        <p class="text-sm font-medium">{{ session('estado') }}</p>
    </div>
@endif

@foreach ($camposGenerales as $campo)
    @error($campo)
        <div role="alert" class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-900">
            <x-icono nombre="alerta" class="mt-0.5 size-5 shrink-0" />
            <p class="text-sm font-medium">{{ $message }}</p>
        </div>
    @enderror
@endforeach
