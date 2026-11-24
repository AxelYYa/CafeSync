<x-layouts.invitado titulo="Nueva contraseña" subtitulo="Al guardarla se cerrarán todas tus sesiones abiertas.">
    <form method="POST" action="{{ route('recuperacion.restablecer') }}" class="flex flex-col gap-5" data-enviar-una-vez>
        @csrf
        <x-campo nombre="password" etiqueta="Nueva contraseña" tipo="password" autocomplete="new-password" autofocus required
                 ayuda="Mínimo 10 caracteres, con mayúsculas, minúsculas y números." />
        <x-campo nombre="password_confirmation" etiqueta="Confirma la contraseña" tipo="password" autocomplete="new-password" required />

        <button type="submit" class="btn btn-primario btn-lg w-full">Guardar contraseña</button>
    </form>
</x-layouts.invitado>
