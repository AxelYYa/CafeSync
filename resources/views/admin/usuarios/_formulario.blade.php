{{-- Campos compartidos por alta y edición de usuarios. $usuario es null en el alta. --}}
@php($editando = $usuario !== null)

<div class="grid gap-6 lg:grid-cols-2">
    <section class="tarjeta flex flex-col gap-4 p-6" aria-labelledby="titulo-datos">
        <h2 id="titulo-datos" class="text-lg font-bold">Datos de la cuenta</h2>
        <x-campo nombre="nombre" etiqueta="Nombre completo" :valor="$usuario?->nombre" required maxlength="120" />
        <x-campo nombre="username" etiqueta="Nombre de usuario" :valor="$usuario?->username" required maxlength="40" autocomplete="off"
                 ayuda="Letras sin acentos, números, punto, guion y guion bajo. Sirve para iniciar sesión." />
        <x-campo nombre="correo" etiqueta="Correo" tipo="email" :valor="$usuario?->correo" required maxlength="180" autocomplete="off" />
        <x-campo nombre="telefono" etiqueta="Teléfono (opcional)" tipo="tel" :valor="$usuario?->telefono" placeholder="+528711234567"
                 ayuda="Formato internacional. Se usa para recuperar la contraseña por SMS o llamada." />

        <div>
            <label for="id_rol" class="etiqueta">Rol</label>
            <select id="id_rol" name="id_rol" class="campo" required @error('id_rol') aria-invalid="true" @enderror>
                @foreach ($roles as $rol)
                    <option value="{{ $rol->id_rol }}" @selected((int) old('id_rol', $usuario?->id_rol ?? $roles->firstWhere('nombre', 'empleado')?->id_rol) === $rol->id_rol)>
                        {{ ucfirst($rol->nombre) }} — {{ $rol->descripcion }}
                    </option>
                @endforeach
            </select>
            @error('id_rol')<p class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
        </div>

        <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-crema-300 px-4">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" class="size-5 accent-emerald-700" @checked(old('activo', $usuario?->activo ?? true))>
            <span class="font-semibold">Cuenta activa</span>
            <span class="text-sm text-espresso-500">Al desactivarla se cierran sus sesiones y se revocan sus tokens.</span>
        </label>
    </section>

    <section class="tarjeta flex flex-col gap-4 p-6" aria-labelledby="titulo-credenciales">
        <h2 id="titulo-credenciales" class="text-lg font-bold">Credenciales</h2>
        <p class="-mt-2 text-sm text-espresso-600">
            {{ $editando ? 'Deja los campos vacíos para conservar la contraseña y el PIN actuales.' : 'Comparte la contraseña inicial en persona; la persona puede cambiarla desde «Seguridad de la cuenta».' }}
        </p>
        <x-campo nombre="password" :etiqueta="$editando ? 'Nueva contraseña' : 'Contraseña'" tipo="password" autocomplete="new-password" :required="! $editando"
                 ayuda="Mínimo 10 caracteres, con mayúsculas, minúsculas y números." />
        <x-campo nombre="password_confirmation" etiqueta="Confirma la contraseña" tipo="password" autocomplete="new-password" :required="! $editando" />
        <x-campo nombre="pin" :etiqueta="$editando && $usuario->tienePin() ? 'Nuevo PIN del reloj (opcional)' : 'PIN del reloj (opcional)'" tipo="password"
                 inputmode="numeric" autocomplete="off" maxlength="6" ayuda="4 a 6 dígitos, único por empleado. Se guarda solo como hash." />
        <x-campo nombre="pin_confirmation" etiqueta="Confirma el PIN" tipo="password" inputmode="numeric" autocomplete="off" maxlength="6" />
    </section>
</div>

<div class="flex flex-wrap gap-3">
    <button type="submit" class="btn btn-primario btn-lg">{{ $editando ? 'Guardar cambios' : 'Crear usuario' }}</button>
    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secundario btn-lg">Cancelar</a>
</div>
