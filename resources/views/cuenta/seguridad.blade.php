<x-layouts.app titulo="Seguridad de la cuenta" :descripcion="$usuario->nombre.' · '.$usuario->username.' · '.$usuario->correo" ancho="max-w-5xl">
    @if (session('codigos_recuperacion'))
        <section class="tarjeta border-caramelo-400 bg-caramelo-500/5 p-6" aria-labelledby="titulo-codigos" role="alert">
            <h2 id="titulo-codigos" class="flex items-center gap-2 text-lg font-bold"><x-icono nombre="llave" /> Guarda tus códigos de recuperación</h2>
            <p class="mt-1 text-sm text-espresso-600">Cada código funciona una sola vez si pierdes tu dispositivo. No se volverán a mostrar.</p>
            <ul class="mt-4 grid grid-cols-2 gap-2 font-mono text-lg sm:grid-cols-4">
                @foreach (session('codigos_recuperacion') as $codigo)
                    <li class="rounded-lg bg-white px-3 py-2 text-center">{{ $codigo }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="tarjeta" id="sesiones" aria-labelledby="titulo-sesiones">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-crema-200 p-6">
            <div>
                <h2 id="titulo-sesiones" class="text-lg font-bold">Sesiones activas</h2>
                <p class="text-sm text-espresso-600">Dispositivos con tu cuenta abierta. Al cerrar una sesión, ese dispositivo pierde el acceso en su siguiente petición.</p>
            </div>
        </div>
        @if (! $sesionesDisponibles)
            <p class="p-6 text-sm text-espresso-600">El servidor no guarda las sesiones en la base de datos (<code>SESSION_DRIVER</code> distinto de <code>database</code>), así que no se pueden listar ni cerrar de forma remota.</p>
        @else
            <ul class="divide-y divide-crema-100">
                @foreach ($sesiones as $sesion)
                    <li class="flex flex-wrap items-center gap-4 px-6 py-4">
                        <span class="grid size-11 place-items-center rounded-xl bg-crema-100 text-espresso-700">
                            <x-icono :nombre="$sesion['dispositivo'] === 'Computadora' ? 'pantalla' : 'dispositivo'" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold">{{ $sesion['navegador'] }} en {{ $sesion['sistema'] }} · {{ $sesion['dispositivo'] }}</p>
                            <p class="text-sm text-espresso-500">IP {{ $sesion['ip'] ?? 'desconocida' }} · última actividad {{ $sesion['ultima_actividad']->diffForHumans() }}</p>
                        </div>
                        @if ($sesion['actual'])
                            <span class="insignia bg-emerald-100 text-emerald-800">Este dispositivo</span>
                        @else
                            <form method="POST" action="{{ route('sesiones.destroy', $sesion['id']) }}" data-enviar-una-vez data-confirmar="¿Cerrar la sesión de {{ $sesion['navegador'] }} en {{ $sesion['sistema'] }}?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secundario">Cerrar sesión</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
            @if (count($sesiones) > 1)
                <form method="POST" action="{{ route('sesiones.destroy-otras') }}" class="flex flex-wrap items-end gap-3 border-t border-crema-200 p-6" data-enviar-una-vez>
                    @csrf
                    @method('DELETE')
                    <div class="min-w-64 flex-1">
                        <x-campo nombre="password_actual" id="password-sesiones" etiqueta="Confirma tu contraseña para cerrar todas las demás" tipo="password" autocomplete="current-password" required />
                    </div>
                    <button type="submit" class="btn btn-peligro">Cerrar las demás sesiones</button>
                </form>
            @endif
        @endif
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="tarjeta flex flex-col gap-4 p-6" id="mfa" aria-labelledby="titulo-mfa">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="titulo-mfa" class="text-lg font-bold">Verificación en dos pasos</h2>
                    <p class="text-sm text-espresso-600">Pide un código de tu app de autenticación (Google Authenticator, Microsoft Authenticator, Authy…) además de la contraseña.</p>
                </div>
                @if ($usuario->tieneMfaActivo())
                    <span class="insignia shrink-0 bg-emerald-100 text-emerald-800">Activa</span>
                @else
                    <span class="insignia shrink-0 bg-stone-200 text-stone-700">Inactiva</span>
                @endif
            </div>

            @if ($usuario->tieneMfaActivo())
                <p class="text-sm">Te quedan <strong>{{ $codigosRestantes }}</strong> códigos de recuperación.</p>
                <form method="POST" action="{{ route('mfa.codigos') }}" class="flex flex-col gap-3" data-enviar-una-vez>
                    @csrf
                    <x-campo nombre="password_actual" id="password-codigos" etiqueta="Contraseña actual" tipo="password" autocomplete="current-password" required />
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-secundario">Generar códigos nuevos</button>
                        <button type="submit" formaction="{{ route('mfa.desactivar') }}" name="_method" value="DELETE" class="btn btn-fantasma text-red-700"
                                data-confirmar="¿Desactivar la verificación en dos pasos? Tu cuenta quedará protegida solo por la contraseña.">Desactivar</button>
                    </div>
                </form>
            @elseif ($secretoMfa)
                <ol class="flex list-decimal flex-col gap-2 pl-5 text-sm">
                    <li>En tu app de autenticación elige «Agregar cuenta» → «Introducir clave».</li>
                    <li>Escribe esta clave (tipo «basada en tiempo»):
                        <span class="mt-1 block rounded-lg bg-crema-100 px-3 py-2 font-mono text-base tracking-wider break-all select-all">{{ trim(chunk_split($secretoMfa, 4, ' ')) }}</span>
                    </li>
                    <li>En un celular también puedes abrir <a href="{{ $uriMfa }}" class="font-semibold text-caramelo-700 underline">este enlace de configuración</a>.</li>
                    <li>Escribe el código de 6 dígitos que muestra la app.</li>
                </ol>
                <form method="POST" action="{{ route('mfa.confirmar') }}" class="flex flex-wrap items-end gap-3" data-enviar-una-vez>
                    @csrf
                    <div class="w-40">
                        <x-campo nombre="codigo" etiqueta="Código" inputmode="numeric" autocomplete="one-time-code" maxlength="10" required class="text-center tracking-widest" />
                    </div>
                    <button type="submit" class="btn btn-primario">Activar</button>
                </form>
            @else
                <form method="POST" action="{{ route('mfa.iniciar') }}" data-enviar-una-vez>
                    @csrf
                    <button type="submit" class="btn btn-primario"><x-icono nombre="escudo" /> Configurar verificación en dos pasos</button>
                </form>
            @endif
        </section>

        <section class="tarjeta p-6" aria-labelledby="titulo-password">
            <h2 id="titulo-password" class="text-lg font-bold">Cambiar contraseña</h2>
            <p class="text-sm text-espresso-600">Al cambiarla se cierran tus sesiones en otros dispositivos.</p>
            <form method="POST" action="{{ route('cuenta.password') }}" class="mt-4 flex flex-col gap-4" data-enviar-una-vez>
                @csrf
                @method('PUT')
                <x-campo nombre="password_actual" id="password-actual-cambio" etiqueta="Contraseña actual" tipo="password" autocomplete="current-password" required />
                <x-campo nombre="password" etiqueta="Nueva contraseña" tipo="password" autocomplete="new-password" required ayuda="Mínimo 10 caracteres, con mayúsculas, minúsculas y números." />
                <x-campo nombre="password_confirmation" etiqueta="Confirma la nueva contraseña" tipo="password" autocomplete="new-password" required />
                <button type="submit" class="btn btn-primario self-start">Guardar contraseña</button>
            </form>
        </section>

        <section class="tarjeta p-6" aria-labelledby="titulo-recuperacion">
            <h2 id="titulo-recuperacion" class="text-lg font-bold">Datos de recuperación</h2>
            <p class="text-sm text-espresso-600">Se usan si olvidas tu contraseña. La respuesta secreta se guarda cifrada con hash y nunca se muestra.</p>
            <form method="POST" action="{{ route('cuenta.recuperacion') }}" class="mt-4 flex flex-col gap-4" data-enviar-una-vez>
                @csrf
                @method('PUT')
                <x-campo nombre="telefono" etiqueta="Teléfono para SMS o llamada" tipo="tel" :valor="$usuario->telefono" placeholder="+528711234567" ayuda="Formato internacional con +, sin espacios." />
                <x-campo nombre="pregunta_secreta" etiqueta="Pregunta secreta" :valor="$usuario->pregunta_secreta" maxlength="160" placeholder="¿Nombre de tu primera mascota?" />
                <x-campo nombre="respuesta_secreta" etiqueta="Respuesta" tipo="password" autocomplete="off" maxlength="120"
                         :ayuda="$usuario->tienePreguntaSecreta() ? 'Ya tienes una respuesta guardada; escribe una nueva solo si quieres cambiarla.' : null" />
                <x-campo nombre="password_actual" id="password-recuperacion" etiqueta="Contraseña actual" tipo="password" autocomplete="current-password" required />
                <button type="submit" class="btn btn-primario self-start">Guardar datos</button>
            </form>
        </section>

        <section class="tarjeta p-6" aria-labelledby="titulo-pin">
            <h2 id="titulo-pin" class="text-lg font-bold">PIN del reloj checador</h2>
            <p class="text-sm text-espresso-600">{{ $usuario->tienePin() ? 'Ya tienes un PIN. Puedes reemplazarlo.' : 'Aún no tienes PIN; lo necesitas para registrar asistencia.' }}</p>
            <form method="POST" action="{{ route('cuenta.pin') }}" class="mt-4 flex flex-col gap-4" data-enviar-una-vez>
                @csrf
                @method('PUT')
                <x-campo nombre="pin" etiqueta="Nuevo PIN (4 a 6 dígitos)" tipo="password" inputmode="numeric" autocomplete="off" maxlength="6" required />
                <x-campo nombre="pin_confirmation" etiqueta="Confirma el PIN" tipo="password" inputmode="numeric" autocomplete="off" maxlength="6" required />
                <x-campo nombre="password_actual" id="password-pin" etiqueta="Contraseña actual" tipo="password" autocomplete="current-password" required />
                <button type="submit" class="btn btn-primario self-start">Guardar PIN</button>
            </form>
        </section>
    </div>
</x-layouts.app>
