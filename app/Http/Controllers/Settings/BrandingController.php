<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateBrandingRequest;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver-branding', only: ['edit']),
            new Middleware('permission:editar-branding', only: ['update']),
        ];
    }

    public function edit(): View
    {
        return view('settings.branding.edit', [
            'company' => CompanySetting::current(),
            // Colores sugeridos (el usuario también puede elegir cualquier otro)
            'palette' => ['#0f766e', '#1d4ed8', '#4f46e5', '#7c3aed', '#be123c', '#c2410c', '#15803d', '#0f172a'],
        ]);
    }

    public function update(UpdateBrandingRequest $request): RedirectResponse
    {
        // Solo existe una fila: se toma la primera o se crea si aún no existe
        $company = CompanySetting::query()->firstOrNew();
        $data = $request->safe()->except(['logo', 'remove_logo']);

        // Si se quita el logo o se sube uno nuevo, se borra el archivo anterior
        if (($request->boolean('remove_logo') || $request->hasFile('logo')) && $company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            // Se guarda en storage/app/public/branding con un nombre único
            $data['logo_path'] = $request->file('logo')->store('branding', 'public');
        }

        $company->fill($data)->save();
        CompanySetting::forget();

        return redirect()->route('settings.branding.edit')
            ->with('success', 'La identidad corporativa se actualizó correctamente.');
    }
}
