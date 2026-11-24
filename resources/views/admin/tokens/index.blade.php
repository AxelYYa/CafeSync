<x-layouts.app titulo="Tokens de pantallas" descripcion="Credenciales para que una pantalla KDS consulte la API sin una sesión de navegador. Cada token tiene permisos limitados, vence y puede revocarse.">
    @if (session('token_nuevo'))
        <section class="tarjeta border-caramelo-400 bg-caramelo-500/5 p-6" role="alert" aria-labelledby="titulo-token-nuevo">
            <h2 id="titulo-token-nuevo" class="flex items-center gap-2 text-lg font-bold"><x-icono nombre="llave" /> Copia el token ahora</h2>
            <p class="mt-1 text-sm text-espresso-600">Solo se guarda su huella en la base de datos; no se podrá volver a ver. Configúralo en el dispositivo como <code>Authorization: Bearer &lt;token&gt;</code>.</p>
            <p class="mt-4 rounded-lg bg-white px-4 py-3 font-mono text-sm break-all select-all">{{ session('token_nuevo') }}</p>
        </section>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[24rem_minmax(0,1fr)]">
        <section class="tarjeta p-6 xl:self-start" aria-labelledby="titulo-nuevo-token">
            <h2 id="titulo-nuevo-token" class="text-lg font-bold">Nuevo token</h2>
            <form method="POST" action="{{ route('admin.tokens.store') }}" class="mt-4 flex flex-col gap-4" data-enviar-una-vez>
                @csrf
                <x-campo nombre="nombre" etiqueta="Dispositivo" placeholder="Tableta barra caliente" required maxlength="60" />
                <div>
                    <label for="id_usuario" class="etiqueta">Cuenta que representa</label>
                    <select id="id_usuario" name="id_usuario" class="campo" required>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id_usuario }}" @selected((int) old('id_usuario') === $usuario->id_usuario)>{{ $usuario->nombre }} ({{ $usuario->username }})</option>
                        @endforeach
                    </select>
                    @error('id_usuario')<p class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
                </div>
                <fieldset>
                    <legend class="etiqueta">Permisos</legend>
                    <div class="flex flex-col gap-2">
                        @foreach ($habilidades as $habilidad)
                            <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-crema-300 px-4 has-checked:border-caramelo-500 has-checked:bg-caramelo-500/10">
                                <input type="checkbox" name="habilidades[]" value="{{ $habilidad->value }}" class="size-5 accent-caramelo-600"
                                       @checked(in_array($habilidad->value, old('habilidades', [\App\Enums\HabilidadToken::KdsLeer->value]), true))>
                                <span>
                                    <span class="block text-sm font-semibold">{{ $habilidad->etiqueta() }}</span>
                                    <span class="block font-mono text-xs text-espresso-500">{{ $habilidad->value }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('habilidades')<p class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
                </fieldset>
                <p class="text-xs text-espresso-500">Vence en {{ config('sanctum.expiration') ? intdiv((int) config('sanctum.expiration'), 1440).' día(s)' : 'ningún momento (sin vencimiento configurado)' }}.</p>
                <button type="submit" class="btn btn-primario">Crear token</button>
            </form>
        </section>

        <section class="tarjeta overflow-x-auto" aria-labelledby="titulo-tokens">
            <h2 id="titulo-tokens" class="border-b border-crema-200 px-5 py-4 font-bold">Tokens emitidos</h2>
            @if ($tokens->isEmpty())
                <div class="p-5"><x-vacio icono="llave" titulo="No hay tokens emitidos" /></div>
            @else
                <table class="tabla">
                    <thead><tr><th>Dispositivo</th><th>Cuenta</th><th>Permisos</th><th>Último uso</th><th>Vence</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                    <tbody>
                        @foreach ($tokens as $token)
                            @php($vencido = $token->expires_at?->isPast())
                            <tr @class(['opacity-60' => $vencido])>
                                <td class="font-semibold">{{ $token->name }}</td>
                                <td>{{ $token->tokenable?->nombre ?? '—' }}</td>
                                <td class="font-mono text-xs">{{ implode(', ', $token->abilities) }}</td>
                                <td class="text-sm">{{ $token->last_used_at?->diffForHumans() ?? 'Nunca' }}</td>
                                <td class="text-sm">
                                    @if ($vencido)
                                        <span class="insignia bg-red-100 text-red-800">Vencido</span>
                                    @else
                                        {{ $token->expires_at?->format('d/m/Y') ?? 'Sin vencimiento' }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('admin.tokens.destroy', $token) }}" data-enviar-una-vez data-confirmar="¿Revocar el token «{{ $token->name }}»? El dispositivo dejará de recibir órdenes.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-fantasma text-red-700">Revocar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </div>
</x-layouts.app>
