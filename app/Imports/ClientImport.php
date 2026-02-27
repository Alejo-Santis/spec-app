<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ClientImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    use SkipsFailures;

    public int $importedCount = 0;

    public function model(array $row): ?Client
    {
        // Skip if document already exists
        if (Client::where('document_number', (string) ($row['documento'] ?? ''))->exists()) {
            return null;
        }

        $this->importedCount++;

        return new Client([
            'type'             => in_array($row['tipo'] ?? '', ['natural', 'juridica']) ? $row['tipo'] : 'juridica',
            'business_name'    => $row['razon_social_nombre'],
            'trade_name'       => $row['nombre_comercial'] ?: null,
            'document_number'  => (string) $row['documento'],
            'dv'               => $row['dv'] ?: null,
            'tax_regime'       => in_array($row['regimen'] ?? '', ['simple', 'ordinario']) ? $row['regimen'] : 'ordinario',
            'email'            => $row['email'] ?: null,
            'email_billing'    => $row['email_facturacion'] ?: null,
            'phone'            => $row['telefono'] ?: null,
            'mobile'           => $row['celular'] ?: null,
            'address'          => $row['direccion'] ?: null,
            'city'             => $row['ciudad'] ?: null,
            'department'       => $row['departamento'] ?: null,
            'country'          => $row['pais'] ?: 'CO',
            'postal_code'      => $row['cod_postal'] ?: null,
            'is_active'        => true,
            'notes'            => $row['notas'] ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'razon_social_nombre' => ['required', 'string', 'max:255'],
            'documento'           => ['required'],
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
