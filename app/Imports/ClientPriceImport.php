<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\ClientPrice;
use App\Models\PriceList;
use App\Models\ServiceType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ClientPriceImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts, WithChunkReading, WithLimit
{
    use SkipsFailures;

    const MAX_ROWS = 2000;

    public int  $importedCount   = 0;
    public bool $rowLimitReached = false;

    private int   $processedRows = 0;
    private array $clients        = [];
    private array $serviceTypes   = [];
    private array $priceLists     = [];

    public function __construct()
    {
        // Cache lookups to avoid N+1 on every row
        Client::all(['id', 'document_number'])
            ->each(fn ($c) => $this->clients[$c->document_number] = $c->id);

        ServiceType::all(['id', 'name'])
            ->each(fn ($s) => $this->serviceTypes[strtolower(trim($s->name))] = $s->id);

        PriceList::all(['id', 'name'])
            ->each(fn ($p) => $this->priceLists[strtolower(trim($p->name))] = $p->id);
    }

    public function model(array $row): ?ClientPrice
    {
        $this->processedRows++;

        if ($this->processedRows > self::MAX_ROWS) {
            $this->rowLimitReached = true;
            return null;
        }

        $clientId      = $this->clients[(string) ($row['documento_cliente'] ?? '')] ?? null;
        $serviceTypeId = $this->serviceTypes[strtolower(trim($row['tipo_de_servicio'] ?? ''))] ?? null;
        $priceListId   = $this->priceLists[strtolower(trim($row['lista_de_precios'] ?? ''))] ?? null;

        if (! $clientId || ! $serviceTypeId || ! $priceListId) {
            return null;
        }

        // Skip if duplicate
        if (ClientPrice::where('client_id', $clientId)
            ->where('service_type_id', $serviceTypeId)
            ->where('price_list_id', $priceListId)
            ->exists()) {
            return null;
        }

        $this->importedCount++;

        $basePrice  = (float) ($row['precio_base'] ?? 0);
        $adjustment = (float) ($row['ajuste_porcentaje'] ?? 0);
        $negotiated = isset($row['precio_negociado']) && $row['precio_negociado'] !== ''
            ? (float) $row['precio_negociado'] : null;
        $discount = isset($row['descuento_porcentaje']) && $row['descuento_porcentaje'] !== ''
            ? (float) $row['descuento_porcentaje'] : null;

        if ($negotiated !== null) {
            $finalPrice = $negotiated;
        } else {
            $finalPrice = $basePrice * (1 + $adjustment / 100);
        }

        if ($discount !== null) {
            $finalPrice = $finalPrice * (1 - $discount / 100);
        }

        return new ClientPrice([
            'client_id'             => $clientId,
            'service_type_id'       => $serviceTypeId,
            'price_list_id'         => $priceListId,
            'duration_years'        => isset($row['anios_vigencia']) && $row['anios_vigencia'] !== ''
                ? (int) $row['anios_vigencia'] : null,
            'base_price'            => $basePrice,
            'adjustment_percentage' => $adjustment,
            'negotiated_price'      => $negotiated,
            'discount_percentage'   => $discount,
            'final_price'           => round($finalPrice, 2),
            'applies_iva'           => strtolower(trim($row['aplica_iva'] ?? 'no')) === 'si',
            'iva_percentage'        => (float) ($row['iva_porcentaje'] ?? 19),
            'valid_from'            => $row['valido_desde'] ?? now()->toDateString(),
            'valid_until'           => isset($row['valido_hasta']) && $row['valido_hasta'] !== ''
                ? $row['valido_hasta'] : null,
            'notes'                 => $row['notas'] ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'documento_cliente' => ['required'],
            'tipo_de_servicio'  => ['required'],
            'lista_de_precios'  => ['required'],
            'precio_base'       => ['required', 'numeric', 'min:0'],
            'valido_desde'      => ['required', 'date'],
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

    public function limit(): int
    {
        return self::MAX_ROWS;
    }
}
