<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function query()
    {
        return Client::query()->orderBy('business_name');
    }

    public function title(): string
    {
        return 'Clientes';
    }

    public function headings(): array
    {
        return [
            'ID', 'Tipo', 'Razón Social / Nombre', 'Nombre Comercial',
            'Documento', 'DV', 'Régimen Tributario', 'Email', 'Email Facturación',
            'Teléfono', 'Celular', 'Dirección', 'Ciudad', 'Departamento',
            'País', 'Cód. Postal', 'Activo', 'Notas',
        ];
    }

    public function map($client): array
    {
        return [
            $client->id,
            $client->type,
            $client->business_name,
            $client->trade_name ?? '',
            $client->document_number,
            $client->dv ?? '',
            $client->tax_regime,
            $client->email ?? '',
            $client->email_billing ?? '',
            $client->phone ?? '',
            $client->mobile ?? '',
            $client->address ?? '',
            $client->city ?? '',
            $client->department ?? '',
            $client->country ?? 'CO',
            $client->postal_code ?? '',
            $client->is_active ? 'Sí' : 'No',
            $client->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }

    public function columnFormats(): array
    {
        return [];
    }
}
