{{--
    Matriz Módulo × Acción con casillas de verificación.
    - $groups / $actions: vienen de App\Support\PermissionCatalog
    - $selected: nombres de permisos marcados
    - $inherited: ['ver-productos' => ['vendedor'], ...] permisos que el usuario ya recibe por sus roles
    - $disabled: solo lectura (ej.: rol administrador)
--}}
@props(['groups', 'actions', 'name' => 'permissions[]', 'selected' => [], 'inherited' => [], 'disabled' => false])

<div class="overflow-x-auto">
    <table class="min-w-full text-sm border">
        <thead class="bg-gray-50">
            <tr class="text-left">
                <th class="p-2 border-b">Módulo</th>
                @foreach ($actions as $action)
                    <th class="p-2 border-b text-center">{{ \App\Support\PermissionCatalog::actionLabel($action) }}</th>
                @endforeach
                @unless ($disabled)
                    <th class="p-2 border-b"></th>
                @endunless
            </tr>
        </thead>
        <tbody>
            @foreach ($groups as $module => $group)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2 font-medium">{{ $group['label'] }}</td>
                    @foreach ($actions as $action)
                        <td class="p-2 text-center">
                            @if ($permission = $group['permissions'][$action] ?? null)
                                <label class="inline-flex flex-col items-center gap-1" title="{{ $permission->name }}">
                                    <input type="checkbox" name="{{ $name }}" value="{{ $permission->name }}"
                                           class="rounded border-gray-300 text-blue-600"
                                           @checked(in_array($permission->name, $selected))
                                           @disabled($disabled)>
                                    @isset($inherited[$permission->name])
                                        <span class="text-[10px] leading-tight text-teal-700">
                                            por rol: {{ implode(', ', $inherited[$permission->name]) }}
                                        </span>
                                    @endisset
                                </label>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    @endforeach
                    @unless ($disabled)
                        <td class="p-2 text-right">
                            <button type="button" class="text-xs text-blue-700 underline"
                                    onclick="const c = this.closest('tr').querySelectorAll('input[type=checkbox]'); const all = [...c].every(x => x.checked); c.forEach(x => x.checked = !all);">
                                Todo / nada
                            </button>
                        </td>
                    @endunless
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
