<?php

namespace App\Http\Controllers;

use App\Http\Concerns\WithFlashMessage;
use App\Models\Client;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    use WithFlashMessage;

    public function __construct(private ActivityLogService $activity) {}

    public function index(Request $request): Response
    {
        $clients = Client::query()
            ->when($request->search, fn ($q, $s) => $q->where('business_name', 'ilike', "%{$s}%")
                ->orWhere('document_number', 'ilike', "%{$s}%")
                ->orWhere('trade_name', 'ilike', "%{$s}%"))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('business_name')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'type', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'                => ['required', 'in:natural,juridica'],
            'business_name'       => ['required', 'string', 'max:255'],
            'trade_name'          => ['nullable', 'string', 'max:255'],
            'document_number'     => ['required', 'string', 'unique:clients,document_number'],
            'dv'                  => ['nullable', 'string', 'max:1'],
            'tax_regime'          => ['required', 'in:simple,ordinario'],
            'tax_responsibilities' => ['nullable', 'array'],
            'tax_responsibilities.*' => ['string'],
            'ciiu_code'           => ['nullable', 'string', 'max:10'],
            'email'               => ['nullable', 'email'],
            'email_billing'       => ['nullable', 'email'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'mobile'              => ['nullable', 'string', 'max:20'],
            'address'             => ['nullable', 'string'],
            'city'                => ['nullable', 'string', 'max:100'],
            'department'          => ['nullable', 'string', 'max:100'],
            'country'             => ['nullable', 'string', 'max:2'],
            'postal_code'         => ['nullable', 'string', 'max:10'],
            'is_active'           => ['boolean'],
            'notes'               => ['nullable', 'string'],
        ]);

        $client = Client::create($data);

        $this->activity->log(
            action: 'created',
            module: 'Client',
            description: "Cliente creado: {$client->business_name}",
            subjectId: $client->id,
            subjectLabel: $client->business_name,
        );

        return redirect()->route('clients.index')
            ->with(...$this->success('Cliente creado correctamente.'));
    }

    public function show(Client $client): Response
    {
        $client->load([
            'currentPrices.serviceType',
            'currentPrices.priceList',
            'activeBundles.bundleTier.serviceType',
            'activeBundles.priceList',
            'prices.adjustments.creator',
            'prices.serviceType',
            'prices.priceList',
        ]);

        return Inertia::render('Clients/Show', [
            'client' => $client->append('formatted_document'),
        ]);
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'type'                => ['required', 'in:natural,juridica'],
            'business_name'       => ['required', 'string', 'max:255'],
            'trade_name'          => ['nullable', 'string', 'max:255'],
            'document_number'     => ['required', 'string', 'unique:clients,document_number,' . $client->id],
            'dv'                  => ['nullable', 'string', 'max:1'],
            'tax_regime'          => ['required', 'in:simple,ordinario'],
            'tax_responsibilities' => ['nullable', 'array'],
            'tax_responsibilities.*' => ['string'],
            'ciiu_code'           => ['nullable', 'string', 'max:10'],
            'email'               => ['nullable', 'email'],
            'email_billing'       => ['nullable', 'email'],
            'phone'               => ['nullable', 'string', 'max:20'],
            'mobile'              => ['nullable', 'string', 'max:20'],
            'address'             => ['nullable', 'string'],
            'city'                => ['nullable', 'string', 'max:100'],
            'department'          => ['nullable', 'string', 'max:100'],
            'country'             => ['nullable', 'string', 'max:2'],
            'postal_code'         => ['nullable', 'string', 'max:10'],
            'is_active'           => ['boolean'],
            'notes'               => ['nullable', 'string'],
        ]);

        $changed = array_keys(array_diff_assoc(
            collect($data)->except(['tax_responsibilities'])->toArray(),
            $client->only(array_keys(collect($data)->except(['tax_responsibilities'])->toArray()))
        ));

        $client->update($data);

        $this->activity->log(
            action: 'updated',
            module: 'Client',
            description: "Cliente actualizado: {$client->business_name}",
            subjectId: $client->id,
            subjectLabel: $client->business_name,
            properties: ['campos_modificados' => $changed],
        );

        return redirect()->route('clients.show', $client)
            ->with(...$this->success('Cliente actualizado correctamente.'));
    }

    public function destroy(Client $client): RedirectResponse
    {
        $name = $client->business_name;
        $id   = $client->id;

        $client->delete();

        $this->activity->log(
            action: 'deleted',
            module: 'Client',
            description: "Cliente eliminado: {$name}",
            subjectId: $id,
            subjectLabel: $name,
        );

        return redirect()->route('clients.index')
            ->with(...$this->success('Cliente eliminado correctamente.'));
    }
}
