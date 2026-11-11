@php
    $esPregunta = $canal === \App\Enums\CanalRecuperacion::PreguntaSecreta;
    $instruccion = match ($canal) {
        \App\Enums\CanalRecuperacion::Correo => 'Si la cuenta existe, enviamos un código de 6 dígitos a su correo. Vence en 10 minutos.',
        \App\Enums\CanalRecuperacion::Sms => 'Si la cuenta tiene un teléfono registrado, enviamos un SMS con un código de 6 dígitos. Vence en 10 minutos.',
        \App\Enums\CanalRecuperacion::Llamada => 'Si la cuenta tiene un teléfono registrado, recibirás una llamada que dicta un código de 6 dígitos. Vence en 10 minutos.',
        \App\Enums\CanalRecuperacion::PreguntaSecreta => 'Responde tu pregunta de seguridad. No importan mayúsculas ni espacios extra.',
    };
@endphp

<x-layouts.invitado titulo="Comprobar identidad" :subtitulo="$instruccion">
    <form method="POST" action="{{ route('recuperacion.comprobar') }}" class="flex flex-col gap-5" data-enviar-una-vez>
        @csrf
        @if ($esPregunta)
            <p class="rounded-xl bg-crema-100 px-4 py-3 font-semibold">{{ $pregunta }}</p>
            <x-campo nombre="entrada" etiqueta="Tu respuesta" autocomplete="off" autofocus required maxlength="160" />
        @else
            <x-campo nombre="entrada" etiqueta="Código recibido" inputmode="numeric" autocomplete="one-time-code" autofocus required maxlength="10"
                     class="text-center text-2xl tracking-[0.4em]" />
        @endif

        <button type="submit" class="btn btn-primario btn-lg w-full">Verificar</button>
    </form>

    <a href="{{ route('recuperacion.solicitar') }}" class="mt-6 inline-flex items-center gap-1 text-sm font-semibold text-espresso-600 hover:text-espresso-900">
        <x-icono nombre="atras" clase="size-4" /> Solicitar otro código o cambiar de método
    </a>
</x-layouts.invitado>
