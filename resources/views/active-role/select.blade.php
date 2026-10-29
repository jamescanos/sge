<x-guest-layout>
    <h2 class="text-lg font-semibold text-gray-800">Hola, {{ auth()->user()->name }}</h2>
    <p class="mt-1 text-sm text-gray-600">
        Su usuario tiene varios roles. Seleccione con cuál desea trabajar en esta sesión.
        Podrá cambiarlo en cualquier momento desde el menú de usuario.
    </p>

    <form method="POST" action="{{ route('active-role.update') }}" class="mt-4 space-y-3">
        @csrf
        @method('PUT')

        @foreach ($roles as $role)
            <label class="flex items-center gap-3 rounded border p-3 cursor-pointer hover:bg-gray-50">
                <input type="radio" name="role" value="{{ $role->name }}" class="text-blue-600"
                       @checked(old('role', $current) === $role->name)>
                <span class="flex-1">
                    <span class="block font-medium capitalize">{{ $role->name }}</span>
                    <span class="block text-xs text-gray-500">{{ $role->permissions_count }} permisos</span>
                </span>
            </label>
        @endforeach

        <x-input-error :messages="$errors->get('role')" />

        @if ($directCount > 0)
            <p class="text-xs text-gray-500">
                Además, tiene {{ $directCount }} permiso(s) directo(s) que aplican con cualquier rol.
            </p>
        @endif

        <div class="flex items-center justify-between pt-2">
            <x-primary-button>Continuar</x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-sm text-gray-600 underline">Cerrar sesión</button>
    </form>
</x-guest-layout>
