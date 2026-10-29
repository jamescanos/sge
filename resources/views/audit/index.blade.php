<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Auditoría — Bitácora de cambios</h2>
    </x-slot>

    @php
        $eventColors = [
            'created' => 'bg-green-100 text-green-800',
            'updated' => 'bg-blue-100 text-blue-800',
            'deleted' => 'bg-red-100 text-red-800',
            'restored' => 'bg-amber-100 text-amber-800',
        ];
        // Convierte cualquier valor (texto, número, arreglo, booleano) en texto legible
        $show = fn ($v) => is_array($v) ? (implode(', ', $v) ?: '(vacío)') : (is_bool($v) ? ($v ? 'sí' : 'no') : ($v ?? '—'));
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">

                <form method="GET" action="{{ route('audit.index') }}" class="flex flex-wrap items-end gap-2">
                    <div>
                        <x-input-label for="module" value="Módulo" />
                        <select id="module" name="module" class="mt-1 border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="event" value="Evento" />
                        <select id="event" name="event" class="mt-1 border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            @foreach ($events as $value => $label)
                                <option value="{{ $value }}" @selected(request('event') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="user" value="Usuario" />
                        <select id="user" name="user" class="mt-1 border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" @selected((int) request('user') === $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="from" value="Desde" />
                        <input id="from" type="date" name="from" value="{{ request('from') }}" class="mt-1 border-gray-300 rounded-md">
                    </div>
                    <div>
                        <x-input-label for="to" value="Hasta" />
                        <input id="to" type="date" name="to" value="{{ request('to') }}" class="mt-1 border-gray-300 rounded-md">
                    </div>
                    <x-primary-button>Filtrar</x-primary-button>
                </form>

                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="p-2">Fecha</th>
                            <th class="p-2">Usuario</th>
                            <th class="p-2">Módulo</th>
                            <th class="p-2">Evento</th>
                            <th class="p-2">Registro</th>
                            <th class="p-2">Cambios (antes → después)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            @php
                                $new = $activity->attribute_changes?->get('attributes', []) ?? [];
                                $old = $activity->attribute_changes?->get('old', []) ?? [];
                            @endphp
                            <tr class="border-b align-top">
                                <td class="p-2 whitespace-nowrap">{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="p-2">{{ $activity->causer?->name ?? 'Sistema' }}</td>
                                <td class="p-2">{{ $activity->log_name }}</td>
                                <td class="p-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs {{ $eventColors[$activity->event] ?? 'bg-gray-100' }}">{{ $activity->description }}</span>
                                </td>
                                <td class="p-2">
                                    {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
                                    <span class="block text-xs text-gray-500">{{ $activity->subject?->name ?? $activity->subject?->invoice_number ?? $activity->subject?->purchase_number }}</span>
                                </td>
                                <td class="p-2">
                                    <ul class="text-xs space-y-0.5">
                                        @foreach ($new as $field => $value)
                                            <li>
                                                <strong>{{ $field }}:</strong>
                                                @if (array_key_exists($field, $old))
                                                    <span class="text-red-700 line-through">{{ $show($old[$field]) }}</span> →
                                                @endif
                                                <span class="text-green-700">{{ $show($value) }}</span>
                                            </li>
                                        @endforeach
                                        @if (empty($new) && ! empty($old))
                                            @foreach ($old as $field => $value)
                                                <li><strong>{{ $field }}:</strong> {{ $show($value) }}</li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500">No hay actividad registrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $activities->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
