<x-layouts.invitado titulo="Iniciar sesión" subtitulo="Usa tu nombre de usuario o tu correo.">
    <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5" data-enviar-una-vez>
        @csrf
        <x-campo nombre="login" etiqueta="Usuario o correo" autocomplete="username" autofocus required maxlength="180" />
        <x-campo nombre="password" etiqueta="Contraseña" tipo="password" autocomplete="current-password" required maxlength="255" />

        <button type="submit" class="btn btn-primario btn-lg w-full">Entrar</button>
    </form>

    <div class="mt-6 flex flex-col gap-3 border-t border-crema-200 pt-6 text-sm">
        <a href="{{ route('recuperacion.solicitar') }}" class="font-semibold text-caramelo-700 hover:underline">¿Olvidaste tu contraseña?</a>
        <p class="text-espresso-500">¿Solo vas a registrar tu entrada o salida? El reloj checador está disponible después de iniciar sesión en la terminal del turno.</p>
    </div>
</x-layouts.invitado>
