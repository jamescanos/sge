@if ($errors->any())
    <div class="p-3 rounded bg-red-100 text-red-800">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<h3 class="font-semibold text-gray-800">1. Datos del usuario</h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="name" value="Nombre" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required />
    </div>
    <div>
        <x-input-label for="email" value="Correo" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
    </div>
    <div>
        <x-input-label for="password" :value="$user->exists ? 'Nueva contraseña (dejar vacío para no cambiarla)' : 'Contraseña'" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
    </div>
    <div>
        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
    </div>
</div>
<label class="inline-flex items-center gap-2">
    <input type="checkbox" name="active" value="1" class="rounded border-gray-300" @checked(old('active', $user->active))>
    <span>Usuario activo (puede iniciar sesión)</span>
</label>

<h3 class="font-semibold text-gray-800 pt-4 border-t">2. Roles</h3>
<p class="text-sm text-gray-600">
    Puede asignar varios roles. Si el usuario tiene más de uno, al iniciar sesión elegirá con cuál trabajar.
</p>
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2">
    @foreach ($roles as $role)
        <label class="flex items-start gap-2 rounded border p-2">
            <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="mt-1 rounded border-gray-300"
                   @checked(in_array($role->name, $userRoles))>
            <span>
                <span class="block font-medium">{{ $role->name }}</span>
                <span class="block text-xs text-gray-500">{{ $role->permissions->count() }} permisos</span>
            </span>
        </label>
    @endforeach
</div>

<h3 class="font-semibold text-gray-800 pt-4 border-t">3. Permisos directos (adicionales a los roles)</h3>
<p class="text-sm text-gray-600">
    Úselos para excepciones puntuales (ej.: un vendedor que también puede ver reportes).
    Los permisos directos aplican con <strong>cualquier</strong> rol activo. La etiqueta «por rol» indica que
    el permiso ya lo recibe por uno de sus roles (no es necesario marcarlo).
</p>
<x-permission-grid :groups="$groups" :actions="$actions" :selected="$directPermissions" :inherited="$inherited" />
