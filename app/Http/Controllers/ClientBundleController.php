<?php

namespace App\Http\Controllers;

use App\Http\Concerns\WithFlashMessage;
use App\Models\BundleTier;
use App\Models\Client;
use App\Models\ClientBundle;
use App\Models\PriceList;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientBundleController extends Controller
{
    use WithFlashMessage;

    public function clientBundles(Client $client): Response
    {
        $client->load([
            'bundles.bundleTier.serviceType',
            'bundles.priceList',
            'bundles.consumptions.creator',
        ]);

        return Inertia::render('ClientBundles/ClientBundles', [
            'client' => $client,
        ]);
    }

    public function create(Request $request): Response
    {
        $clients     = Client::where('is_active', true)->orderBy('business_name')->get(['id', 'business_name']);
        $priceLists  = PriceList::orderByDesc('year')->get(['id', 'year', 'name']);
        $bundleTypes = ServiceType::where('billing_type', 'bundle')->where('is_active', true)->get(['id', 'name']);
        $bundleTiers = BundleTier::with('serviceType')->where('is_active', true)->get();

        return Inertia::render('ClientBundles/Create', [
            'clients'          => $clients,
            'priceLists'       => $priceLists,
            'bundleTypes'      => $bundleTypes,
            'bundleTiers'      => $bundleTiers,
            'selectedClientId' => $request->client_id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'          => ['required', 'exists:clients,id'],
            'bundle_tier_id'     => ['required', 'exists:bundle_tiers,id'],
            'price_list_id'      => ['required', 'exists:price_lists,id'],
            'quantity_purchased' => ['required', 'integer', 'min:1'],
            'price_paid'         => ['required', 'numeric', 'min:0'],
            'purchased_at'       => ['required', 'date'],
            'expires_at'         => ['nullable', 'date', 'after:purchased_at'],
            'notes'              => ['nullable', 'string'],
        ]);

        ClientBundle::create($data);

        return redirect()->route('clients.bundles', $data['client_id'])
            ->with(...$this->success('Bolsa creada correctamente.'));
    }

    public function show(ClientBundle $clientBundle): Response
    {
        $clientBundle->load([
            'client',
            'bundleTier.serviceType',
            'priceList',
            'consumptions.creator',
        ]);

        return Inertia::render('ClientBundles/Show', [
            'bundle' => $clientBundle,
        ]);
    }

    public function edit(ClientBundle $clientBundle): Response
    {
        return Inertia::render('ClientBundles/Edit', [
            'bundle'     => $clientBundle->load(['client', 'bundleTier', 'priceList']),
            'priceLists' => PriceList::orderByDesc('year')->get(['id', 'year', 'name']),
        ]);
    }

    public function update(Request $request, ClientBundle $clientBundle): RedirectResponse
    {
        $data = $request->validate([
            'expires_at' => ['nullable', 'date'],
            'is_active'  => ['boolean'],
            'notes'      => ['nullable', 'string'],
        ]);

        $clientBundle->update($data);

        return redirect()->route('client-bundles.show', $clientBundle)
            ->with(...$this->success('Bolsa actualizada correctamente.'));
    }
}
