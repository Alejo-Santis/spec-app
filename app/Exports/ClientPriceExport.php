<?php

namespace App\Exports;

use App\Models\ClientPrice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientPriceExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private ?int $priceListId = null) {}

    public function query()
    {
        return ClientPrice::with(['client', 'serviceType', 'priceList'])
            ->when($this->priceListId, fn ($q) => $q->where('price_list_id', $this->priceListId))
            ->orderBy('client_id');
    }

    public function title(): string
    {
        return 'Precios Clientes';
    }

    public function headings(): array
    {
        return [
            'ID', 'Cliente', 'Documento', 'Tipo de Servicio', 'Lista de Precios',
            'Años Vigencia', 'Precio Base', 'Ajuste %', 'Precio Negociado',
            'Descuento %', 'Precio Final', 'Aplica IVA', 'IVA %',
            'Válido Desde', 'Válido Hasta', 'Notas',
        ];
    }

    public function map($price): array
    {
        return [
            $price->id,
            $price->client->business_name ?? '',
            $price->client->document_number ?? '',
            $price->serviceType->name ?? '',
            $price->priceList->name ?? '',
            $price->duration_years ?? '',
            $price->base_price,
            $price->adjustment_percentage ?? '',
            $price->negotiated_price ?? '',
            $price->discount_percentage ?? '',
            $price->final_price,
            $price->applies_iva ? 'Sí' : 'No',
            $price->iva_percentage,
            $price->valid_from,
            $price->valid_until ?? '',
            $price->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
