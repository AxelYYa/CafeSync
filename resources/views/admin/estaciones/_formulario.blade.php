{{-- Campos compartidos por alta y edición de estaciones. $estacion es null en el alta. --}}
<section class="tarjeta flex flex-col gap-4 p-6">
    <x-campo nombre="nombre" etiqueta="Nombre" :valor="$estacion?->nombre" required minlength="3" maxlength="80" placeholder="Barra caliente"
             ayuda="Así aparece en el KDS y en el catálogo del POS. Debe ser único." />
    <div>
        <label for="descripcion" class="etiqueta">Descripción <span class="font-normal text-espresso-500">(opcional)</span></label>
        <textarea id="descripcion" name="descripcion" rows="2" maxlength="255" class="campo" placeholder="Espresso y bebidas calientes."
                  @error('descripcion') aria-invalid="true" @enderror>{{ old('descripcion', $estacion?->descripcion) }}</textarea>
        @error('descripcion')<p class="mt-1 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
    </div>
    <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-crema-300 px-4">
        <input type="hidden" name="activa" value="0">
        <input type="checkbox" name="activa" value="1" class="size-5 accent-emerald-700" @checked(old('activa', $estacion?->activa ?? true))>
        <span class="font-semibold">Estación activa</span>
        <span class="text-sm text-espresso-500">Si está inactiva, sus productos dejan de venderse en el POS y no aparece en la lista de pantallas KDS.</span>
    </label>
</section>

<div class="flex flex-wrap gap-3">
    <button type="submit" class="btn btn-primario btn-lg">{{ $estacion ? 'Guardar cambios' : 'Crear estación' }}</button>
    <a href="{{ route('admin.estaciones.index') }}" class="btn btn-secundario btn-lg">Cancelar</a>
</div>
