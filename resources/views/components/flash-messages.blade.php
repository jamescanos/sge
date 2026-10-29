@php
    $messages = array_filter([
        'success' => session('success'),
        'error' => session('error'),
    ]);
@endphp

@if ($messages)
    <div class="mx-auto max-w-7xl space-y-2 px-4 pt-6 sm:px-6 lg:px-8">
        @foreach ($messages as $type => $message)
            <div x-data="{ show: true }" x-show="show" x-transition role="{{ $type === 'error' ? 'alert' : 'status' }}"
                @class([
                    'flex items-start gap-3 rounded-md border px-4 py-3 text-sm shadow-sm',
                    'border-teal-200 bg-teal-50 text-teal-900 dark:border-teal-800 dark:bg-teal-950 dark:text-teal-100' => $type === 'success',
                    'border-red-200 bg-red-50 text-red-900 dark:border-red-800 dark:bg-red-950 dark:text-red-100' => $type === 'error',
                ])>
                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    @if ($type === 'success')
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                    @else
                        <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                    @endif
                </svg>
                <p class="flex-1">{{ $message }}</p>
                <button type="button" @click="show = false" class="rounded opacity-60 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-current" aria-label="Cerrar mensaje">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>
        @endforeach
    </div>
@endif
