<x-layouts.app titulo="Usuarios" descripcion="Cuentas del personal, su rol y su estado. Una cuenta inactiva no puede iniciar sesión ni registrar asistencia.">
    <x-slot:acciones>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-acento"><x-icono nombre="mas" /> Nuevo usuario</a>
    </x-slot:acciones>

    <form method="GET" action="{{ route('admin.usuarios.index') }}" class="tarjeta flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-56 flex-1">
            <label for="buscar" class="etiqueta">Buscar</label>
            <input id="buscar" name="buscar" type="search" value="{{ $filtros['buscar'] ?? '' }}" class="campo" placeholder="Nombre, usuario o correo">
        </div>
        <div>
            <label for="rol" class="etiqueta">Rol</label>
            <select id="rol" name="rol" class="campo">
                <option value="">Todos</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol->id_rol }}" @selected((int) ($filtros['rol'] ?? 0) === $rol->id_rol)>{{ ucfirst($rol->nombre) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="estado" class="etiqueta">Estado</label>
            <select id="estado" name="estado" class="campo">
                <option value="">Todos</option>
                <option value="activos" @selected(($filtros['estado'] ?? null) === 'activos')>Activos</option>
                <option value="inactivos" @selected(($filtros['estado'] ?? null) === 'inactivos')>Inactivos</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primario">Filtrar</button>
    </form>

    @if ($usuarios->isEmpty())
        <x-vacio icono="usuarios" titulo="No hay usuarios con estos filtros" />
    @else
        <div class="tarjeta overflow-x-auto">
            <table class="tabla">
                <thead>
                    <tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Seguridad</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr @class(['opacity-60' => ! $usuario->activo])>
                            <td>
                                <p class="font-semibold">{{ $usuario->nombre }}</p>
                                <p class="text-xs text-espresso-500">{{ $usuario->correo }}</p>
                            </td>
                            <td class="font-mono">{{ $usuario->username }}</td>
                            <td>{{ ucfirst($usuario->rol->nombre) }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <span @class(['insignia', 'bg-emerald-100 text-emerald-800' => $usuario->tienePin(), 'bg-stone-200 text-stone-600' => ! $usuario->tienePin()])>{{ $usuario->tienePin() ? 'Con PIN' : 'Sin PIN' }}</span>
                                    @if ($usuario->tieneMfaActivo())
                                        <span class="insignia bg-sky-100 text-sky-800">MFA</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span @class(['insignia', 'bg-emerald-100 text-emerald-800' => $usuario->activo, 'bg-red-100 text-red-800' => ! $usuario->activo])>{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-secundario">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $usuarios->links() }}
    @endif
</x-layouts.app>
