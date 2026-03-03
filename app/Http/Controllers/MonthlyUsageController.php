<?php

namespace App\Http\Controllers;

use App\Http\Concerns\WithFlashMessage;
use App\Models\Client;
use App\Models\ClientPrice;
use App\Models\MonthlyUsage;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonthlyUsageController extends Controller
{
    use WithFlashMessage;

    public function __construct(private ActivityLogService $activity) {}

    public function index(Request $request): Response
    {
        $usages = MonthlyUsage::with(['client', 'clientPrice.serviceType', 'creator'])
            ->when($request->client_search, fn ($q, $s) => $q->whereHas('client',
                fn ($cq) => $cq->where('business_name', 'ilike', "%{$s}%")
            ))
            ->when($request->period_year,  fn ($q, $y) => $q->where('period_year', $y))
            ->when($request->period_month, fn ($q, $m) => $q->where('period_month', $m))
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->orderBy('client_id')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('MonthlyUsages/Index', [
            'usages'  => $usages,
            'filters' => $request->only(['client_search', 'period_year', 'period_month']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'       => ['required', 'exists:clients,id'],
            'client_price_id' => ['required', 'exists:client_prices,id'],
            'period_year'     => ['required', 'integer', 'min:2020', 'max:2099'],
            'period_month'    => ['required', 'integer', 'min:1', 'max:12'],
            'document_count'  => ['required', 'integer', 'min:1'],
            'notes'           => ['nullable', 'string'],
        ]);

        $exists = MonthlyUsage::where('client_price_id', $data['client_price_id'])
            ->where('period_year', $data['period_year'])
            ->where('period_month', $data['period_month'])
            ->exists();

        if ($exists) {
            return back()->with(...$this->error('Ya existe un registro de uso para ese servicio en ese período.'));
        }

        $clientPrice = ClientPrice::findOrFail($data['client_price_id']);

        $usage = MonthlyUsage::create([
            'client_id'       => $data['client_id'],
            'client_price_id' => $data['client_price_id'],
            'period_year'     => $data['period_year'],
            'period_month'    => $data['period_month'],
            'document_count'  => $data['document_count'],
            'unit_price'      => $clientPrice->final_price,
            'total_amount'    => round($clientPrice->final_price * $data['document_count'], 2),
            'notes'           => $data['notes'] ?? null,
            'created_by'      => auth()->id(),
        ]);

        $client = Client::find($data['client_id']);

        $this->activity->log(
            action: 'created',
            module: 'MonthlyUsage',
            description: "Uso mensual registrado: {$client->business_name} — {$usage->period_label} ({$data['document_count']} docs)",
            subjectId: $usage->id,
            subjectLabel: "{$client->business_name} / {$usage->period_label}",
            properties: ['document_count' => $data['document_count'], 'total' => $usage->total_amount],
        );

        return redirect()->route('clients.show', $client)
            ->with(...$this->success('Uso mensual registrado correctamente.'));
    }

    public function destroy(MonthlyUsage $monthlyUsage): RedirectResponse
    {
        $monthlyUsage->load('client');
        $client = $monthlyUsage->client;
        $label  = "{$client->business_name} / {$monthlyUsage->period_label}";

        $monthlyUsage->delete();

        $this->activity->log(
            action: 'deleted',
            module: 'MonthlyUsage',
            description: "Uso mensual eliminado: {$label}",
        );

        return redirect()->route('clients.show', $client)
            ->with(...$this->success('Registro eliminado correctamente.'));
    }
}
