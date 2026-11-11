<x-layouts.invitado titulo="Recuperar contraseña" subtitulo="Elige cómo quieres comprobar tu identidad.">
    <form method="POST" action="{{ route('recuperacion.enviar') }}" class="flex flex-col gap-5" data-enviar-una-vez>
        @csrf
        <x-campo nombre="identificador" etiqueta="Usuario o correo" autocomplete="username" autofocus required maxlength="180" />

        <fieldset>
            <legend class="etiqueta">Método de verificación</legend>
            <div class="grid gap-2">
                @foreach ($canales as $canal)
                    @php($disponible = in_array($canal, $disponibles, true))
                    <label @class([
                        'flex min-h-14 items-center gap-3 rounded-xl border px-4 py-3 transition',
                        'cursor-pointer border-crema-300 bg-white has-checked:border-caramelo-500 has-checked:bg-caramelo-500/10 hover:bg-crema-100' => $disponible,
                        'cursor-not-allowed border-dashed border-crema-300 bg-crema-100/50 text-espresso-500' => ! $disponible,
                    ])>
                        <input type="radio" name="canal" value="{{ $canal->value }}" class="size-5 accent-caramelo-600"
                               @checked(old('canal', $disponibles[0]->value ?? null) === $canal->value) @disabled(! $disponible) required>
                        <span class="flex-1 font-semibold">{{ $canal->etiqueta() }}</span>
                        @unless ($disponible)
                            <span class="text-xs">No configurado en este servidor</span>
                        @endunless
                    </label>
                @endforeach
            </div>
            @error('canal')
                <p class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>
            @enderror
        </fieldset>

        <button type="submit" class="btn btn-primario btn-lg w-full">Continuar</button>
    </form>

    <p class="mt-6 text-sm text-espresso-500">Por seguridad, la respuesta es la misma exista o no la cuenta. Si no recibes el código, revisa los datos de recuperación con tu gerente.</p>
    <a href="{{ route('login') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-espresso-600 hover:text-espresso-900">
        <x-icono nombre="atras" clase="size-4" /> Volver al inicio de sesión
    </a>
</x-layouts.invitado>
