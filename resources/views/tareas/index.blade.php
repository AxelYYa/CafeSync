<x-layouts.app titulo="Tareas del turno" descripcion="Tus tareas asignadas y las generales de tu turno o zona. Se incluyen las completadas hoy." ancho="max-w-4xl">
    @php($pendientes = $tareas->reject->estaCompletada())

    @if ($tareas->isNotEmpty())
        <div class="tarjeta p-5">
            @php($porcentaje = (int) round(100 * ($tareas->count() - $pendientes->count()) / $tareas->count()))
            <div class="flex items-center justify-between text-sm font-semibold">
                <span>{{ $tareas->count() - $pendientes->count() }} de {{ $tareas->count() }} completadas</span>
                <span>{{ $porcentaje }} %</span>
            </div>
            <div class="mt-2 h-3 overflow-hidden rounded-full bg-crema-200" role="progressbar" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100" aria-label="Avance del turno">
                <div class="h-full rounded-full bg-emerald-600" style="width: {{ $porcentaje }}%"></div>
            </div>
        </div>
    @endif

    @forelse ($tareas as $tarea)
        <article @class(['tarjeta flex items-start gap-4 p-5', 'bg-crema-100/60' => $tarea->estaCompletada()])>
            @if ($tarea->estaCompletada())
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-emerald-600 text-white" aria-hidden="true"><x-icono nombre="check" clase="size-7" /></span>
            @else
                @can('completar', $tarea)
                    <form method="POST" action="{{ route('tareas.completar', $tarea) }}" data-enviar-una-vez>
                        @csrf
                        <button type="submit" class="grid size-12 shrink-0 place-items-center rounded-xl border-2 border-crema-300 bg-white text-transparent transition hover:border-emerald-600 hover:text-emerald-600"
                                aria-label="Marcar «{{ $tarea->plantilla->titulo }}» como completada">
                            <x-icono nombre="check" clase="size-7" />
                        </button>
                    </form>
                @endcan
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 @class(['text-lg font-bold', 'text-espresso-500 line-through' => $tarea->estaCompletada()])>{{ $tarea->plantilla->titulo }}</h2>
                    <x-estado :estado="$tarea->estado" />
                </div>
                @if ($tarea->plantilla->descripcion)
                    <p class="mt-1 text-espresso-600">{{ $tarea->plantilla->descripcion }}</p>
                @endif
                <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-espresso-500">
                    @if ($tarea->turno)<span>Turno: {{ $tarea->turno->nombre }}</span>@endif
                    @if ($tarea->estacion)<span>Zona: {{ $tarea->estacion->nombre }}</span>@endif
                    <span>{{ $tarea->responsable ? 'Asignada a '.$tarea->responsable->nombre : 'Tarea general' }}</span>
                    @if ($tarea->fecha_limite)<span @class(['font-semibold text-red-700' => ! $tarea->estaCompletada() && $tarea->fecha_limite->isPast()])>Límite: {{ $tarea->fecha_limite->format('d/m H:i') }}</span>@endif
                    @if ($tarea->estaCompletada())<span>Completada {{ $tarea->completada_en->format('H:i') }} por {{ $tarea->completadaPor?->nombre }}</span>@endif
                </p>
            </div>
        </article>
    @empty
        <x-vacio icono="tareas" titulo="No tienes tareas asignadas">Cuando el gerente asigne tareas a tu turno, zona o a ti, aparecerán aquí.</x-vacio>
    @endforelse
</x-layouts.app>
