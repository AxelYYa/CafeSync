<x-layouts.app titulo="Help Mode" descripcion="Ficha de preparación del producto." ancho="max-w-4xl">
    <x-slot:acciones>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('kds.index') }}" class="btn btn-secundario"><x-icono nombre="atras" /> Volver</a>
    </x-slot:acciones>

    <div class="tarjeta p-6 sm:p-8">
        @include('ayuda._ficha')
    </div>
</x-layouts.app>
