<x-layouts.app :titulo="'Editar a '.$usuario->nombre" descripcion="Cambia sus datos, su rol, su estado o restablece sus credenciales." ancho="max-w-6xl">
    <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" class="flex flex-col gap-6" data-enviar-una-vez
          @if ($usuario->activo) data-confirmar-si-desactiva="¿Desactivar la cuenta de {{ $usuario->nombre }}? Se cerrarán sus sesiones y se revocarán sus tokens." @endif>
        @csrf
        @method('PUT')
        @include('admin.usuarios._formulario', ['usuario' => $usuario])
    </form>
</x-layouts.app>
