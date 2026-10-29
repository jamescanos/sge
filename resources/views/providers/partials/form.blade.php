<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="document_type" value="Tipo de documento" />
        <select id="document_type" name="document_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            @foreach (['NIT' => 'NIT', 'CC' => 'Cédula', 'CE' => 'Cédula extranjería', 'PP' => 'Pasaporte'] as $value => $label)
                <option value="{{ $value }}" @selected(old('document_type', $provider->document_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('document_type')" />
    </div>

    <div>
        <x-input-label for="document_number" value="Número de documento" />
        <x-text-input id="document_number" name="document_number" type="text" class="mt-1 block w-full"
            :value="old('document_number', $provider->document_number)" required />
        <x-input-error class="mt-2" :messages="$errors->get('document_number')" />
    </div>
</div>

<div>
    <x-input-label for="name" value="Razón social" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
        :value="old('name', $provider->name)" required />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div>
    <x-input-label for="contact_name" value="Persona de contacto (opcional)" />
    <x-text-input id="contact_name" name="contact_name" type="text" class="mt-1 block w-full"
        :value="old('contact_name', $provider->contact_name)" />
    <x-input-error class="mt-2" :messages="$errors->get('contact_name')" />
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="email" value="Correo (opcional)" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
            :value="old('email', $provider->email)" />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div>
        <x-input-label for="phone" value="Teléfono (opcional)" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
            :value="old('phone', $provider->phone)" />
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
    </div>
</div>

<div>
    <x-input-label for="address" value="Dirección (opcional)" />
    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full"
        :value="old('address', $provider->address)" />
    <x-input-error class="mt-2" :messages="$errors->get('address')" />
</div>

<div>
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="active" value="1" class="rounded border-gray-300"
            @checked(old('active', $provider->active))>
        <span>Proveedor activo</span>
    </label>
</div>
