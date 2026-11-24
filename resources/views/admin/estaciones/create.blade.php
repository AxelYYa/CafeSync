<x-layouts.app titulo="Nueva estación" descripcion="Agrega una zona de preparación con su propia pantalla KDS." ancho="max-w-3xl">
    <form method="POST" action="{{ route('admin.estaciones.store') }}" class="flex flex-col gap-6" data-enviar-una-vez>
        @csrf
        @include('admin.estaciones._formulario', ['estacion' => null])
    </form>
</x-layouts.app>
