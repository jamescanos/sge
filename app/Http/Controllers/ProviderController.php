<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProviderRequest;
use App\Http\Requests\UpdateProviderRequest;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProviderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver-proveedores', only: ['index']),
            new Middleware('permission:crear-proveedores', only: ['create', 'store']),
            new Middleware('permission:editar-proveedores', only: ['edit', 'update']),
            new Middleware('permission:eliminar-proveedores', only: ['destroy', 'restore']),
        ];
    }

    public function index(Request $request)
    {
        $showTrashed = $request->boolean('trashed') && $request->user()->can('eliminar-proveedores');

        $providers = Provider::query()
            ->withCount('purchases')
            ->when($showTrashed, fn ($q) => $q->onlyTrashed())
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%'.$request->string('search').'%';
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)
                        ->orWhere('document_number', 'like', $search)
                        ->orWhere('contact_name', 'like', $search);
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('active', $request->input('status') === 'active'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('providers.index', [
            'providers' => $providers,
            'showTrashed' => $showTrashed,
            'trashedCount' => Provider::onlyTrashed()->count(),
        ]);
    }

    public function create()
    {
        return view('providers.create', [
            'provider' => new Provider(['active' => true, 'document_type' => 'NIT']),
        ]);
    }

    public function store(StoreProviderRequest $request)
    {
        Provider::create($request->validated());

        return redirect()->route('providers.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    public function edit(Provider $provider)
    {
        return view('providers.edit', ['provider' => $provider]);
    }

    public function update(UpdateProviderRequest $request, Provider $provider)
    {
        $provider->update($request->validated());

        return redirect()->route('providers.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy(Provider $provider)
    {
        // Regla: no se puede eliminar un proveedor con compras asociadas
        if ($provider->purchases()->exists()) {
            return back()->with('error', "No se puede eliminar «{$provider->name}»: tiene compras asociadas.");
        }

        $provider->delete();

        return redirect()->route('providers.index')
            ->with('success', "Proveedor «{$provider->name}» enviado a la papelera.");
    }

    public function restore(Provider $provider)
    {
        $provider->restore();

        return redirect()->route('providers.index', ['trashed' => 1])
            ->with('success', "Proveedor «{$provider->name}» restaurado.");
    }
}
