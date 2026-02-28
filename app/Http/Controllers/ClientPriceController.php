<?php

namespace App\Http\Controllers;

use App\Http\Concerns\WithFlashMessage;
use App\Models\Client;
use App\Models\ClientPrice;
use App\Models\PriceList;
use App\Models\ServiceType;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientPriceController extends Controller
{
    use WithFlashMessage;

    public function __construct(private ActivityLogService $activity) {}

    public function index(Request $request): Response
    {
        $priceLists      = PriceList::orderByDesc('year')->get(['id', 'year', 'name', 'is_active']);
        $activePriceList = $priceLists->firstWhere('is_active', true);

        $prices = ClientPrice::with(['client', 'serviceType', 'priceList'])
            ->when(
                $request->price_list_id ?? $activePriceList?->id,
                fn ($q, $id) => $q->where('price_list_id', $id)
            )
            ->when($request->client_search, fn ($q, $s) => $q->whereHas('client',
                fn ($cq) => $cq->where('business_name', 'ilike', "%{$s}%")
            ))
            ->when($request->service_type_id, fn ($q, $id) => $q->where('service_type_id', $id))
            ->orderBy('client_id')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('ClientPrices/Index', [
            'prices'       => $prices,
            'priceLists'   => $priceLists,
            'serviceTypes' => ServiceType::where('is_active', true)->get(['id', 'name']),
            'filters'      => $request->only(['price_list_id', 'client_search', 'service_type_id']),
        ]);
    }

    public function clientPrices(Client $client): Response
    {
        $client->load([
            'prices.serviceType',
            'prices.priceList',
            'prices.adjustments',
        ]);

        return Inertia::render('ClientPrices/ClientPrices', [
            'client'       => $client,
            'priceLists'   => PriceList::orderByDesc('year')->get(),
            'serviceTypes' => ServiceType::where('is_active', true)->where('billing_type', 'unit')->get(),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('ClientPrices/Create', [
            'clients'          => Client::where('is_active', true)->orderBy('business_name')->get(['id', 'business_name', 'document_number']),
            'priceLists'       => PriceList::orderByDesc('year')->get(['id', 'year', 'name', 'is_active', 'adjustment_percentage']),
            'serviceTypes'     => ServiceType::where('is_active', true)->where('billing_type', 'unit')->get(),
            'selectedClientId' => $request->client_id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'             => ['required', 'exists:clients,id'],
            'service_type_id'       => ['required', 'exists:service_types,id'],
            'price_list_id'         => ['required', 'exists:price_lists,id'],
            'duration_years'        => ['nullable', 'integer', 'in:1,2'],
            'base_price'            => ['required', 'numeric', 'min:0'],
            'adjustment_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'negotiated_price'      => ['nullable', 'numeric', 'min:0'],
            'discount_percentage'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'applies_iva'           => ['boolean'],
            'iva_percentage'        => ['required', 'numeric', 'min:0', 'max:100'],
            'valid_from'            => ['required', 'date'],
            'valid_until'           => ['nullable', 'date', 'after:valid_from'],
            'notes'                 => ['nullable', 'string'],
        ]);

        $price  = ClientPrice::create($data);
        $client = Client::find($data['client_id']);
        $svc    = ServiceType::find($data['service_type_id']);

        $this->activity->log(
            action: 'created',
            module: 'ClientPrice',
            description: "Precio asignado a {$client->business_name}: {$svc->name} → $ " . number_format($price->final_price, 0, ',', '.'),
            subjectId: $price->id,
            subjectLabel: "{$client->business_name} / {$svc->name}",
            properties: ['final_price' => $price->final_price, 'client_id' => $client->id],
        );

        return redirect()->route('clients.show', $data['client_id'])
            ->with(...$this->success('Precio creado correctamente.'));
    }

    public function edit(ClientPrice $clientPrice): Response
    {
        $clientPrice->load(['client', 'serviceType', 'priceList', 'adjustments.creator']);

        return Inertia::render('ClientPrices/Edit', [
            'clientPrice'  => $clientPrice,
            'priceLists'   => PriceList::orderByDesc('year')->get(['id', 'year', 'name']),
            'serviceTypes' => ServiceType::where('is_active', true)->where('billing_type', 'unit')->get(),
        ]);
    }

    public function update(Request $request, ClientPrice $clientPrice): RedirectResponse
    {
        $data = $request->validate([
            'adjustment_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'negotiated_price'      => ['nullable', 'numeric', 'min:0'],
            'discount_percentage'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'applies_iva'           => ['boolean'],
            'iva_percentage'        => ['required', 'numeric', 'min:0', 'max:100'],
            'valid_from'            => ['required', 'date'],
            'valid_until'           => ['nullable', 'date', 'after:valid_from'],
            'notes'                 => ['nullable', 'string'],
        ]);

        $oldPrice = $clientPrice->final_price;
        $clientPrice->update($data);
        $clientPrice->load(['client', 'serviceType']);

        $this->activity->log(
            action: 'updated',
            module: 'ClientPrice',
            description: "Precio actualizado: {$clientPrice->client->business_name} / {$clientPrice->serviceType->name}",
            subjectId: $clientPrice->id,
            subjectLabel: "{$clientPrice->client->business_name} / {$clientPrice->serviceType->name}",
            properties: ['precio_anterior' => $oldPrice, 'precio_nuevo' => $clientPrice->fresh()->final_price],
        );

        return redirect()->route('clients.show', $clientPrice->client_id)
            ->with(...$this->success('Precio actualizado correctamente.'));
    }

    public function destroy(ClientPrice $clientPrice): RedirectResponse
    {
        $clientId = $clientPrice->client_id;
        $clientPrice->load(['client', 'serviceType']);
        $label = "{$clientPrice->client->business_name} / {$clientPrice->serviceType->name}";

        $clientPrice->delete();

        $this->activity->log(
            action: 'deleted',
            module: 'ClientPrice',
            description: "Precio eliminado: {$label}",
            subjectId: $clientId,
            subjectLabel: $label,
        );

        return redirect()->route('clients.show', $clientId)
            ->with(...$this->success('Precio eliminado correctamente.'));
    }
}
