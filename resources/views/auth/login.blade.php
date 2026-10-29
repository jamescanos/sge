<x-guest-layout>
    <h2 class="text-2xl font-bold text-slate-800">Iniciar sesión</h2>
    <p class="mt-1 text-sm text-slate-500">Ingrese con su correo y contraseña.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="mt-4 flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-brand shadow-sm focus:ring-brand" name="remember">
                <span class="ms-2 text-sm text-slate-600">Recordarme</span>
            </label>

            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-brand hover:underline focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2" href="{{ route('password.request') }}">
                    ¿Olvidó su contraseña?
                </a>
            @endif
        </div>

        <x-primary-button class="mt-6 w-full py-2.5">
            Ingresar
        </x-primary-button>
    </form>
</x-guest-layout>
