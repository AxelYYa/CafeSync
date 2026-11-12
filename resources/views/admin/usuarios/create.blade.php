<x-layouts.app titulo="Nuevo usuario" descripcion="Da de alta a una persona del personal y asígnale su rol." ancho="max-w-6xl">
    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="flex flex-col gap-6" data-enviar-una-vez>
        @csrf
        @include('admin.usuarios._formulario', ['usuario' => null])
    </form>
</x-layouts.app>
