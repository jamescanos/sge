@if ($errors->any())
    <div class="p-3 rounded bg-red-100 text-red-800">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="max-w-md">
    <x-input-label for="name" value="Nombre del rol" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
        :value="old('name', $role->name)" :readonly="$isSuperRole" required />
    <p class="mt-1 text-xs text-gray-500">Se guarda en minúsculas y con guiones (ej.: «Jefe de Bodega» → jefe-de-bodega).</p>
</div>

<h3 class="font-semibold text-gray-800 pt-4 border-t">Permisos del rol</h3>
@if ($isSuperRole)
    <p class="text-sm text-amber-700">El rol administrador siempre tiene todos los permisos; no se puede modificar.</p>
@endif
<x-permission-grid :groups="$groups" :actions="$actions" :selected="$isSuperRole ? collect($groups)->flatMap(fn ($g) => collect($g['permissions'])->pluck('name'))->all() : $selected" :disabled="$isSuperRole" />
