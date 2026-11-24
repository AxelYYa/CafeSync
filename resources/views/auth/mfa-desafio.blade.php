<x-layouts.invitado titulo="Verificación en dos pasos" subtitulo="Escribe el código de 6 dígitos de tu app de autenticación.">
    <form method="POST" action="{{ route('mfa.verificar') }}" class="flex flex-col gap-5" data-enviar-una-vez>
        @csrf
        <x-campo nombre="codigo" etiqueta="Código" inputmode="numeric" autocomplete="one-time-code" autofocus required maxlength="20"
                 class="text-center text-2xl tracking-[0.4em]"
                 ayuda="¿Perdiste el dispositivo? Escribe uno de tus códigos de recuperación (formato ABCDE-FGHIJ)." />

        <button type="submit" class="btn btn-primario btn-lg w-full">Verificar</button>
    </form>

    <a href="{{ route('login') }}" class="mt-6 inline-flex items-center gap-1 text-sm font-semibold text-espresso-600 hover:text-espresso-900">
        <x-icono nombre="atras" clase="size-4" /> Volver al inicio de sesión
    </a>
</x-layouts.invitado>
