<x-layouts.app :titulo="'Editar '.$estacion->nombre" :descripcion="$estacion->productos_count.' producto(s) asignados · '.$pendientes.' por preparar ahora'" ancho="max-w-3xl">
    @error('estacion')
        <div role="alert" class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-900">
            <x-icono nombre="alerta" class="mt-0.5 size-5 shrink-0" />
            <p class="text-sm font-medium">{{ $message }}</p>
        </div>
    @enderror

    <form method="POST" action="{{ route('admin.estaciones.update', $estacion) }}" class="flex flex-col gap-6" data-enviar-una-vez
          @if ($estacion->activa) data-confirmar-si-desactiva="¿Desactivar {{ $estacion->nombre }}? Sus productos dejarán de venderse en el POS." @endif>
        @csrf
        @method('PUT')
        @include('admin.estaciones._formulario', ['estacion' => $estacion])
    </form>
</x-layouts.app>
