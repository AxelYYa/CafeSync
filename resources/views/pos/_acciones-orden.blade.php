{{-- Acciones del ciclo de la orden según su estado. Las reglas se validan otra vez en el servidor. --}}
@use('App\Enums\EstadoOrden')
@php($estado = $orden->estado)

<div class="flex flex-wrap items-center gap-2">
    @if ($estado === EstadoOrden::Lista)
        <form method="POST" action="{{ route('ordenes.entregar', $orden) }}" data-enviar-una-vez>
            @csrf
            <button type="submit" class="btn btn-secundario">Entregar</button>
        </form>
    @endif

    @if (in_array($estado, EstadoOrden::cerrables(), true))
        <form method="POST" action="{{ route('ordenes.cerrar', $orden) }}" class="flex gap-2" data-enviar-una-vez
              data-confirmar="¿Cerrar la venta {{ $orden->folio }}? Se descontarán los ingredientes del inventario.">
            @csrf
            <label class="sr-only" for="metodo-{{ $orden->id_orden }}">Método de pago registrado</label>
            <select id="metodo-{{ $orden->id_orden }}" name="metodo_pago_registrado" class="campo min-h-11 w-auto py-1 text-sm">
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
                <option value="otro">Otro</option>
            </select>
            <button type="submit" class="btn btn-exito"><x-icono nombre="check" /> Cerrar venta</button>
        </form>
    @endif

    @if (in_array($estado, EstadoOrden::activos(), true))
        <form method="POST" action="{{ route('ordenes.cancelar', $orden) }}" data-enviar-una-vez
              data-confirmar="¿Cancelar la orden {{ $orden->folio }}? Desaparecerá de las pantallas de preparación.">
            @csrf
            <button type="submit" class="btn btn-fantasma text-red-700">Cancelar</button>
        </form>
    @endif
</div>
