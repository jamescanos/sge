<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" maxlength="100" :value="old('name', $category->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="description" value="Descripción (opcional)" />
        <x-textarea id="description" name="description" rows="3" class="mt-1 block w-full">{{ old('description', $category->description) }}</x-textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div>
        <label for="active" class="inline-flex items-center gap-2">
            <input id="active" name="active" type="checkbox" value="1" class="rounded border-gray-300 text-teal-700 shadow-sm focus:ring-teal-600 dark:border-gray-700 dark:bg-gray-900" @checked(old('active', $category->active))>
            <span class="text-sm text-gray-700 dark:text-gray-300">Categoría activa</span>
        </label>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Solo las categorías activas se pueden asignar a productos nuevos.</p>
        <x-input-error class="mt-2" :messages="$errors->get('active')" />
    </div>
</div>
