<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #333; }

    .header { background: #1e3a5f; color: white; padding: 20px 24px; margin-bottom: 24px; }
    .header h1 { font-size: 18pt; font-weight: bold; }
    .header p  { font-size: 9pt; opacity: .8; margin-top: 4px; }

    .section-title {
      font-size: 9pt; font-weight: bold; text-transform: uppercase;
      letter-spacing: .05em; color: #1e3a5f;
      border-bottom: 2px solid #1e3a5f;
      padding-bottom: 4px; margin-bottom: 10px; margin-top: 18px;
    }

    .info-grid { display: table; width: 100%; }
    .info-row  { display: table-row; }
    .info-label { display: table-cell; width: 130px; color: #666; font-size: 9pt; padding: 2px 0; }
    .info-value { display: table-cell; font-size: 9pt; padding: 2px 0; }

    table.prices {
      width: 100%; border-collapse: collapse; margin-top: 4px;
    }
    table.prices th {
      background: #1e3a5f; color: white; padding: 7px 10px;
      text-align: left; font-size: 8.5pt;
    }
    table.prices th.right { text-align: right; }
    table.prices td {
      padding: 7px 10px; border-bottom: 1px solid #e8e8e8; font-size: 9pt;
    }
    table.prices td.right { text-align: right; }
    table.prices tr:nth-child(even) td { background: #f8f9fa; }
    table.prices tfoot td {
      padding: 8px 10px; font-weight: bold; border-top: 2px solid #1e3a5f;
      font-size: 9.5pt;
    }

    .badge {
      display: inline-block; padding: 2px 8px; border-radius: 10px;
      font-size: 8pt; font-weight: bold;
    }
    .badge-success { background: #d4edda; color: #155724; }
    .badge-secondary { background: #e2e3e5; color: #383d41; }

    .footer {
      margin-top: 32px; padding-top: 12px; border-top: 1px solid #ddd;
      font-size: 8pt; color: #999; text-align: center;
    }
    .text-muted { color: #666; }
    .fw-bold { font-weight: bold; }
  </style>
</head>
<body>

  <!-- Cabecera -->
  <div class="header">
    <h1>Cotización de precios</h1>
    <p>{{ $activePriceList?->name ?? 'Lista activa' }} &nbsp;·&nbsp; Generado el {{ $generatedAt }}</p>
  </div>

  <!-- Datos del cliente -->
  <div class="section-title">Datos del cliente</div>
  <div class="info-grid">
    <div class="info-row">
      <div class="info-label">Razón social</div>
      <div class="info-value fw-bold">{{ $client->business_name }}</div>
    </div>
    @if($client->trade_name)
    <div class="info-row">
      <div class="info-label">Nombre comercial</div>
      <div class="info-value">{{ $client->trade_name }}</div>
    </div>
    @endif
    <div class="info-row">
      <div class="info-label">{{ $client->type === 'juridica' ? 'NIT' : 'Cédula' }}</div>
      <div class="info-value">{{ $client->document_number }}{{ $client->dv ? '-' . $client->dv : '' }}</div>
    </div>
    <div class="info-row">
      <div class="info-label">Régimen tributario</div>
      <div class="info-value">{{ ucfirst($client->tax_regime) }}</div>
    </div>
    @if($client->city)
    <div class="info-row">
      <div class="info-label">Ciudad</div>
      <div class="info-value">{{ $client->city }}{{ $client->department ? ', ' . $client->department : '' }}</div>
    </div>
    @endif
    @if($client->email_billing ?? $client->email)
    <div class="info-row">
      <div class="info-label">Email facturación</div>
      <div class="info-value">{{ $client->email_billing ?? $client->email }}</div>
    </div>
    @endif
  </div>

  <!-- Tabla de precios -->
  <div class="section-title" style="margin-top: 22px">Precios vigentes</div>

  @if($prices->isEmpty())
    <p class="text-muted" style="font-size:9pt; margin-top:8px">Este cliente no tiene precios asignados en la lista activa.</p>
  @else
    <table class="prices">
      <thead>
        <tr>
          <th>Servicio</th>
          <th>Vigencia</th>
          <th class="right">Precio neto</th>
          <th style="text-align:center">IVA</th>
          <th class="right">Total con IVA</th>
        </tr>
      </thead>
      <tbody>
        @foreach($prices as $price)
          @php
            $withIva = $price->applies_iva
              ? round($price->final_price * (1 + $price->iva_percentage / 100), 2)
              : $price->final_price;
          @endphp
          <tr>
            <td>
              <strong>{{ $price->service_type?->name }}</strong>
              @if($price->duration_years)
                <br><span class="text-muted" style="font-size:8pt">{{ $price->duration_years }} año{{ $price->duration_years > 1 ? 's' : '' }}</span>
              @endif
            </td>
            <td class="text-muted" style="font-size:8.5pt">
              {{ \Carbon\Carbon::parse($price->valid_from)->format('d/m/Y') }}
              @if($price->valid_until)
                → {{ \Carbon\Carbon::parse($price->valid_until)->format('d/m/Y') }}
              @endif
            </td>
            <td class="right">$ {{ number_format($price->final_price, 0, ',', '.') }}</td>
            <td style="text-align:center">
              @if($price->applies_iva)
                <span class="badge badge-success">{{ $price->iva_percentage }}%</span>
              @else
                <span class="badge badge-secondary">No aplica</span>
              @endif
            </td>
            <td class="right fw-bold">$ {{ number_format($withIva, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  <!-- Nota -->
  <div style="margin-top: 20px; padding: 10px 14px; background: #f8f9fa; border-left: 3px solid #1e3a5f; font-size: 8.5pt; color: #555;">
    <strong>Nota:</strong> Los precios están expresados en pesos colombianos (COP).
    Esta cotización es informativa y está sujeta a los términos y condiciones del contrato vigente.
  </div>

  <!-- Pie de página -->
  <div class="footer">
    Documento generado por SPEC — Sistema de Gestión de Precios y Servicios &nbsp;·&nbsp; {{ $generatedAt }}
  </div>

</body>
</html>
