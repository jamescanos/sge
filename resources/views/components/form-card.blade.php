@props(['action', 'method' => 'POST', 'cancel', 'submit' => 'Guardar'])

<div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
    <form method="POST" action="{{ $action }}" class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method($method)
        @endif

        <div class="p-6">
            {{ $slot }}
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/50">
            <a href="{{ $cancel }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Cancelar</a>
            <x-primary-button>{{ $submit }}</x-primary-button>
        </div>
    </form>
</div>
