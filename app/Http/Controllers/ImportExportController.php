<?php

namespace App\Http\Controllers;

use App\Exports\ClientBundleExport;
use App\Exports\ClientExport;
use App\Exports\ClientPriceExport;
use App\Imports\ClientImport;
use App\Imports\ClientPriceImport;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportExportController extends Controller
{
    public function __construct(private readonly ActivityLogService $activity) {}

    // ── Clients ──────────────────────────────────────────

    public function exportClients(): BinaryFileResponse
    {
        $this->activity->log(
            action: 'exported',
            module: 'Client',
            description: 'Exportación de clientes a Excel',
        );

        return Excel::download(new ClientExport(), 'clientes_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function importClients(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new ClientImport();
        Excel::import($import, $request->file('file'));

        $skipped = count($import->failures());
        $type    = $skipped > 0 ? 'warning' : 'success';
        $msg     = "Se importaron {$import->importedCount} cliente(s).";
        if ($skipped > 0) {
            $msg .= " {$skipped} fila(s) omitidas por errores o duplicados.";
        }

        $this->activity->log(
            action: 'imported',
            module: 'Client',
            description: "Importación de clientes: {$import->importedCount} importados, {$skipped} omitidos.",
            properties: ['importados' => $import->importedCount, 'omitidos' => $skipped],
        );

        return redirect()->route('clients.index')->with('flash', compact('type') + ['message' => $msg]);
    }

    public function templateClients(): BinaryFileResponse
    {
        return response()->download(public_path('csv/template_clientes.csv'), 'template_clientes.csv');
    }

    // ── Client Prices ──────────────────────────────────────

    public function exportClientPrices(Request $request): BinaryFileResponse
    {
        $this->activity->log(
            action: 'exported',
            module: 'ClientPrice',
            description: 'Exportación de precios de clientes a Excel',
        );

        return Excel::download(
            new ClientPriceExport($request->integer('price_list_id') ?: null),
            'precios_clientes_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function importClientPrices(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new ClientPriceImport();
        Excel::import($import, $request->file('file'));

        $skipped = count($import->failures());
        $type    = $skipped > 0 ? 'warning' : 'success';
        $msg     = "Se importaron {$import->importedCount} precio(s).";
        if ($skipped > 0) {
            $msg .= " {$skipped} fila(s) omitidas por errores o duplicados.";
        }

        $this->activity->log(
            action: 'imported',
            module: 'ClientPrice',
            description: "Importación de precios: {$import->importedCount} importados, {$skipped} omitidos.",
            properties: ['importados' => $import->importedCount, 'omitidos' => $skipped],
        );

        return redirect()->route('client-prices.index')->with('flash', compact('type') + ['message' => $msg]);
    }

    public function templateClientPrices(): BinaryFileResponse
    {
        return response()->download(
            public_path('csv/template_precios_clientes.csv'),
            'template_precios_clientes.csv'
        );
    }

    // ── Client Bundles ────────────────────────────────────

    public function exportClientBundles(): BinaryFileResponse
    {
        $this->activity->log(
            action: 'exported',
            module: 'ClientBundle',
            description: 'Exportación de bolsas a Excel',
        );

        return Excel::download(new ClientBundleExport(), 'bolsas_' . now()->format('Y-m-d') . '.xlsx');
    }
}
