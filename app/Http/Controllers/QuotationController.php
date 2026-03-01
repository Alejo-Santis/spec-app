<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\PriceList;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class QuotationController extends Controller
{
    public function __construct(private readonly ActivityLogService $activity) {}

    public function download(Client $client): Response
    {
        $activePriceList = PriceList::where('is_active', true)->first();

        $client->load([
            'currentPrices.serviceType',
            'currentPrices.priceList',
        ]);

        $prices = $client->currentPrices ?? collect();

        $pdf = Pdf::loadView('pdf.quotation', [
            'client'          => $client,
            'prices'          => $prices,
            'activePriceList' => $activePriceList,
            'generatedAt'     => now()->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        $filename = 'cotizacion-' . str()->slug($client->business_name) . '-' . now()->format('Y') . '.pdf';

        $this->activity->log(
            action: 'exported',
            module: 'Quotation',
            description: "Cotización PDF descargada para {$client->business_name}",
            subjectId: $client->id,
            subjectLabel: $client->business_name,
            properties: ['filename' => $filename, 'prices_count' => $prices->count()],
        );

        return $pdf->download($filename);
    }
}
