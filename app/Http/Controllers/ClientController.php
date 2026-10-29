<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ClientController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver-clientes', only: ['index']),
            new Middleware('permission:crear-clientes', only: ['create', 'store']),
            new Middleware('permission:editar-clientes', only: ['edit', 'update']),
            new Middleware('permission:eliminar-clientes', only: ['destroy', 'restore']),
        ];
    }

    public function index(Request $request)
    {
        $showTrashed = $request->boolean('trashed') && $request->user()->can('eliminar-clientes');

        $clients = Client::query()
            ->withCount('sales')
            ->when($showTrashed, fn ($q) => $q->onlyTrashed())
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%'.$request->string('search').'%';
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)
                        ->orWhere('document_number', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('active', $request->input('status') === 'active'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('clients.index', [
            'clients' => $clients,
            'showTrashed' => $showTrashed,
            'trashedCount' => Client::onlyTrashed()->count(),
        ]);
    }

    public function create()
    {
        return view('clients.create', [
            'client' => new Client(['active' => true, 'document_type' => 'CC']),
        ]);
    }

    public function store(StoreClientRequest $request)
    {
        Client::create($request->validated());

        return redirect()->route('clients.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function edit(Client $client)
    {
        return view('clients.edit', ['client' => $client]);
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return redirect()->route('clients.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Client $client)
    {
        // Regla: no se puede eliminar un cliente con ventas asociadas
        if ($client->sales()->exists()) {
            return back()->with('error', "No se puede eliminar «{$client->name}»: tiene ventas asociadas.");
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', "Cliente «{$client->name}» enviado a la papelera.");
    }

    public function restore(Client $client)
    {
        $client->restore();

        return redirect()->route('clients.index', ['trashed' => 1])
            ->with('success', "Cliente «{$client->name}» restaurado.");
    }
}
