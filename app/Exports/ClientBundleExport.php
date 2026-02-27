<?php

namespace App\Exports;

use App\Models\ClientBundle;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientBundleExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function query()
    {
        return ClientBundle::with(['client', 'bundleTier.serviceType', 'priceList'])
            ->orderBy('client_id');
    }

    public function title(): string
    {
        return 'Bolsas';
    }

    public function headings(): array
    {
        return [
            'ID', 'Cliente', 'Documento', 'Servicio', 'Bolsa (Tier)',
            'Lista de Precios', 'Cant. Comprada', 'Cant. Consumida', 'Cant. Restante',
            'Precio Pagado', 'Fecha Compra', 'Fecha Vencimiento', 'Activa', 'Notas',
        ];
    }

    public function map($bundle): array
    {
        return [
            $bundle->id,
            $bundle->client->business_name ?? '',
            $bundle->client->document_number ?? '',
            $bundle->bundleTier->serviceType->name ?? '',
            $bundle->bundleTier->name ?? '',
            $bundle->priceList->name ?? '',
            $bundle->quantity_purchased,
            $bundle->quantity_consumed,
            $bundle->quantity_purchased - $bundle->quantity_consumed,
            $bundle->price_paid,
            $bundle->purchased_at,
            $bundle->expires_at ?? '',
            $bundle->is_active ? 'Sí' : 'No',
            $bundle->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
