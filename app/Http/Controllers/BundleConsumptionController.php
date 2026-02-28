<?php

namespace App\Http\Controllers;

use App\Http\Concerns\WithFlashMessage;
use App\Models\ClientBundle;
use App\Services\ActivityLogService;
use App\Services\BundleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BundleConsumptionController extends Controller
{
    use WithFlashMessage;

    public function __construct(
        private BundleService $bundleService,
        private ActivityLogService $activity,
    ) {}

    public function index(ClientBundle $clientBundle): Response
    {
        $clientBundle->load(['client', 'bundleTier.serviceType', 'consumptions.creator']);

        return Inertia::render('ClientBundles/Consumptions', [
            'bundle' => $clientBundle,
        ]);
    }

    public function store(Request $request, ClientBundle $clientBundle): RedirectResponse
    {
        $data = $request->validate([
            'quantity'    => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
            'reference'   => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->bundleService->consume(
                bundle: $clientBundle,
                quantity: $data['quantity'],
                description: $data['description'] ?? '',
                reference: $data['reference'] ?? '',
            );
        } catch (\RuntimeException $e) {
            return back()->with(...$this->error($e->getMessage()));
        }

        $clientBundle->load(['client', 'bundleTier']);
        $this->activity->log(
            action: 'consumed',
            module: 'ClientBundle',
            description: "Consumo de {$data['quantity']} unid. en bolsa de {$clientBundle->client->business_name} ({$clientBundle->bundleTier->name})",
            subjectId: $clientBundle->id,
            subjectLabel: "{$clientBundle->client->business_name} / {$clientBundle->bundleTier->name}",
            properties: [
                'quantity'  => $data['quantity'],
                'reference' => $data['reference'] ?? null,
                'remaining' => $clientBundle->quantity_purchased - $clientBundle->quantity_consumed,
            ],
        );

        return redirect()->route('client-bundles.show', $clientBundle)
            ->with(...$this->success("Consumo de {$data['quantity']} unidades registrado correctamente."));
    }
}
