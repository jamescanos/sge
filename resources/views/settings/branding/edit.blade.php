<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800">Identidad corporativa</h2>
    </x-slot>

    @php
        $canEdit = auth()->user()->can('editar-branding');
    @endphp

    {{-- Alpine guarda los valores del formulario para actualizar la vista previa mientras se escribe --}}
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
         x-data="{
             name: @js(old('trade_name', $company->trade_name)),
             slogan: @js(old('slogan', $company->slogan)),
             legal: @js(old('legal_name', $company->legal_name)),
             nit: @js(old('nit', $company->nit)),
             color: @js(old('primary_color', $company->primary_color)),
             logo: @js($company->logoUrl()),
             removeLogo: false,
             initials() { return this.name.split(' ').filter(Boolean).slice(0, 2).map(w => w[0].toUpperCase()).join('') },
             preview(event) {
                 const file = event.target.files[0];
                 if (file) { this.logo = URL.createObjectURL(file); this.removeLogo = false; }
             },
         }">

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Formulario --}}
            <form method="POST" action="{{ route('settings.branding.update') }}" enctype="multipart/form-data"
                  class="space-y-6 lg:col-span-2">
                @csrf
                @method('PUT')

                <fieldset @disabled(! $canEdit) class="space-y-6">
                    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70">
                        <h3 class="font-semibold text-slate-800">Datos de la organización</h3>
                        <p class="text-sm text-slate-500">Aparecen en el menú, en el inicio de sesión y en los documentos PDF.</p>

                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="trade_name" value="Nombre comercial" />
                                <x-text-input id="trade_name" name="trade_name" class="mt-1 block w-full" x-model="name" required />
                                <x-input-error :messages="$errors->get('trade_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="legal_name" value="Razón social" />
                                <x-text-input id="legal_name" name="legal_name" class="mt-1 block w-full" x-model="legal" required />
                                <x-input-error :messages="$errors->get('legal_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="nit" value="NIT" />
                                <x-text-input id="nit" name="nit" class="mt-1 block w-full" x-model="nit" placeholder="901.234.567-8" required />
                                <x-input-error :messages="$errors->get('nit')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="slogan" value="Eslogan (opcional)" />
                                <x-text-input id="slogan" name="slogan" class="mt-1 block w-full" x-model="slogan" />
                                <x-input-error :messages="$errors->get('slogan')" class="mt-2" />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70">
                        <h3 class="font-semibold text-slate-800">Contacto</h3>
                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="address" value="Dirección" />
                                <x-text-input id="address" name="address" class="mt-1 block w-full" :value="old('address', $company->address)" />
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="city" value="Ciudad" />
                                <x-text-input id="city" name="city" class="mt-1 block w-full" :value="old('city', $company->city)" />
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="phone" value="Teléfono" />
                                <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $company->phone)" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="email" value="Correo electrónico" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $company->email)" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="website" value="Sitio web" />
                                <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" :value="old('website', $company->website)" placeholder="https://www.empresa.com" />
                                <x-input-error :messages="$errors->get('website')" class="mt-2" />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70">
                        <h3 class="font-semibold text-slate-800">Logo y color de marca</h3>

                        <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <x-input-label for="logo" value="Logo (PNG, JPG o WEBP, máximo 1 MB)" />
                                <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" @change="preview($event)"
                                       class="mt-1 block w-full text-sm text-slate-600 file:me-3 file:rounded-lg file:border-0 file:bg-brand/10 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand hover:file:bg-brand/20">
                                <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                                @if ($company->logo_path)
                                    <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-600">
                                        <input type="checkbox" name="remove_logo" value="1" x-model="removeLogo" class="rounded border-slate-300 text-brand focus:ring-brand">
                                        Quitar el logo actual
                                    </label>
                                @endif
                            </div>

                            <div>
                                <x-input-label for="primary_color" value="Color de marca" />
                                <div class="mt-1 flex items-center gap-3">
                                    <input type="color" x-model="color" class="h-10 w-14 cursor-pointer rounded-lg border border-slate-300 bg-white p-1" aria-label="Selector de color">
                                    <x-text-input id="primary_color" name="primary_color" class="block w-32 font-mono uppercase" x-model="color" maxlength="7" required />
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($palette as $swatch)
                                        <button type="button" @click="color = '{{ $swatch }}'" class="h-7 w-7 rounded-full ring-2 ring-offset-2 transition hover:scale-110"
                                                style="background-color: {{ $swatch }}" :class="color === '{{ $swatch }}' ? 'ring-slate-500' : 'ring-transparent'"
                                                aria-label="Usar el color {{ $swatch }}"></button>
                                    @endforeach
                                </div>
                                <x-input-error :messages="$errors->get('primary_color')" class="mt-2" />
                            </div>
                        </div>
                    </section>
                </fieldset>

                @if ($canEdit)
                    <div class="flex justify-end">
                        <x-primary-button>Guardar identidad corporativa</x-primary-button>
                    </div>
                @else
                    <p class="text-sm text-slate-500">Solo puede consultar la identidad corporativa (le falta el permiso editar-branding).</p>
                @endif
            </form>

            {{-- Vista previa en vivo --}}
            <aside class="space-y-4">
                <div class="sticky top-24 space-y-4">
                    <p class="text-sm font-semibold uppercase tracking-wider text-slate-500">Vista previa</p>

                    {{-- Menú lateral --}}
                    <div class="rounded-2xl bg-slate-900 p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <template x-if="logo && ! removeLogo">
                                <img :src="logo" alt="" class="h-11 w-11 rounded-lg bg-white object-contain p-0.5">
                            </template>
                            <template x-if="! logo || removeLogo">
                                <span class="flex h-11 w-11 items-center justify-center rounded-lg text-sm font-bold text-white" :style="{ backgroundColor: color }" x-text="initials()"></span>
                            </template>
                            <span class="min-w-0">
                                <span class="block truncate font-bold text-white" x-text="name"></span>
                                <span class="block truncate text-xs text-slate-400" x-text="slogan"></span>
                            </span>
                        </div>
                        <div class="mt-4 space-y-1">
                            <div class="rounded-lg px-3 py-2 text-sm font-semibold text-white" :style="{ backgroundColor: color }">Dashboard</div>
                            <div class="rounded-lg px-3 py-2 text-sm text-slate-300">Productos</div>
                            <div class="rounded-lg px-3 py-2 text-sm text-slate-300">Ventas</div>
                        </div>
                    </div>

                    {{-- Encabezado de documento PDF --}}
                    <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200/70">
                        <p class="text-xs text-slate-400">Encabezado de los PDF</p>
                        <p class="mt-1 font-bold" :style="{ color: color }" x-text="legal"></p>
                        <p class="text-xs text-slate-500">NIT <span x-text="nit"></span></p>
                        <div class="mt-3 h-2 rounded-full" :style="{ backgroundColor: color }"></div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
