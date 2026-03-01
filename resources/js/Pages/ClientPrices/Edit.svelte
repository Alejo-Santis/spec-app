<script>
  import { useForm } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import PriceInput from '../../Components/PriceInput.svelte';

  let { clientPrice, priceLists, serviceTypes } = $props();

  const form = useForm({
    adjustment_percentage: clientPrice.adjustment_percentage ?? '',
    negotiated_price:      clientPrice.negotiated_price ?? null,
    discount_percentage:   clientPrice.discount_percentage ?? null,
    applies_iva:           clientPrice.applies_iva,
    iva_percentage:        clientPrice.iva_percentage,
    valid_from:            clientPrice.valid_from,
    valid_until:           clientPrice.valid_until ?? '',
    notes:                 clientPrice.notes ?? '',
  });

  const finalPrice = $derived(() => {
    let price = $form.negotiated_price
      ? Number($form.negotiated_price)
      : Number(clientPrice.base_price) * (1 + Number($form.adjustment_percentage) / 100);

    if ($form.discount_percentage) {
      price = price * (1 - Number($form.discount_percentage) / 100);
    }
    return Math.round(price * 100) / 100;
  });

  function formatCop(v) {
    return '$ ' + Number(v).toLocaleString('es-CO');
  }

  function submit(e) {
    e.preventDefault();
    $form.put(`/client-prices/${clientPrice.uuid}`);
  }
</script>




<svelte:head><title>Editar precio - SPEC</title></svelte:head>
<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col-md-12">
          <div class="page-header-title"><h5 class="m-0">Editar precio</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/client-prices">Precios</a></li>
            <li class="breadcrumb-item active">Editar</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="ti ti-pencil me-2"></i>
            {clientPrice.client?.business_name} — {clientPrice.service_type?.name}
          </h5>
          <small class="text-muted">{clientPrice.price_list?.name}</small>
        </div>
        <div class="card-body">

          <!-- Info de solo lectura -->
          <div class="alert alert-light d-flex gap-4 mb-3 flex-wrap">
            <div>
              <small class="text-muted d-block">Precio base</small>
              <strong class="price-cop">{formatCop(clientPrice.base_price)}</strong>
            </div>
            <div>
              <small class="text-muted d-block">Precio original</small>
              <strong class="price-cop">{formatCop(clientPrice.final_price)}</strong>
            </div>
            {#if clientPrice.negotiated_price}
              <div>
                <small class="text-muted d-block">Negociado previo</small>
                <strong class="price-cop text-warning">{formatCop(clientPrice.negotiated_price)}</strong>
              </div>
            {/if}
          </div>

          <form onsubmit={submit}>
            <div class="row g-3">

              <!-- Precio negociado -->
              <div class="col-md-5">
                <label class="form-label">Precio negociado</label>
                <PriceInput bind:value={$form.negotiated_price} placeholder="$ (dejar vacío para calcular)" />
                <div class="form-text">Sobreescribe el cálculo con ajuste.</div>
              </div>

              <!-- % Ajuste -->
              <div class="col-md-3">
                <label class="form-label">Ajuste %</label>
                <div class="input-group">
                  <input type="number" step="0.01" class="form-control"
                    bind:value={$form.adjustment_percentage} min="0">
                  <span class="input-group-text">%</span>
                </div>
              </div>

              <!-- % Descuento -->
              <div class="col-md-3">
                <label class="form-label">Descuento %</label>
                <div class="input-group">
                  <input type="number" step="0.01" class="form-control"
                    bind:value={$form.discount_percentage} min="0" max="100">
                  <span class="input-group-text">%</span>
                </div>
              </div>

              <!-- IVA -->
              <div class="col-md-3">
                <div class="form-check form-switch mt-4">
                  <input class="form-check-input" type="checkbox" id="applies_iva" bind:checked={$form.applies_iva}>
                  <label class="form-check-label" for="applies_iva">Aplica IVA</label>
                </div>
              </div>
              {#if $form.applies_iva}
                <div class="col-md-2">
                  <label class="form-label">% IVA</label>
                  <div class="input-group">
                    <input type="number" class="form-control" bind:value={$form.iva_percentage} min="0">
                    <span class="input-group-text">%</span>
                  </div>
                </div>
              {/if}

              <!-- Vigencia -->
              <div class="col-md-3">
                <label class="form-label" for="valid_from">Vigente desde <span class="text-danger">*</span></label>
                <input id="valid_from" type="date"
                  class="form-control {$form.errors.valid_from ? 'is-invalid' : ''}"
                  bind:value={$form.valid_from} required>
              </div>
              <div class="col-md-3">
                <label class="form-label" for="valid_until">Vigente hasta</label>
                <input id="valid_until" type="date" class="form-control" bind:value={$form.valid_until}>
              </div>

              <!-- Notas -->
              <div class="col-12">
                <label class="form-label" for="notes">Notas</label>
                <textarea id="notes" class="form-control" rows="2" bind:value={$form.notes}></textarea>
              </div>

              <!-- Historial de ajustes -->
              {#if (clientPrice.adjustments ?? []).length > 0}
                <div class="col-12">
                  <label class="form-label">Historial de cambios</label>
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                      <thead class="table-light">
                        <tr>
                          <th>Fecha</th>
                          <th>Razón</th>
                          <th class="text-end">Precio anterior</th>
                          <th class="text-end">Precio nuevo</th>
                        </tr>
                      </thead>
                      <tbody>
                        {#each clientPrice.adjustments as adj}
                          <tr>
                            <td><small>{new Date(adj.created_at).toLocaleDateString('es-CO')}</small></td>
                            <td>
                              <span class="badge bg-light-info text-info small">
                                {adj.reason === 'annual_adjust' ? 'Ajuste anual' :
                                  adj.reason === 'negotiation' ? 'Negociación' : 'Corrección'}
                              </span>
                            </td>
                            <td class="text-end text-muted small">{formatCop(adj.old_price)}</td>
                            <td class="text-end fw-medium">{formatCop(adj.new_price)}</td>
                          </tr>
                        {/each}
                      </tbody>
                    </table>
                  </div>
                </div>
              {/if}

              <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="/clients/{clientPrice.client_uuid}" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-primary" disabled={$form.processing}>
                  {#if $form.processing}
                    <span class="spinner-border spinner-border-sm me-1"></span>
                  {/if}
                  <i class="ti ti-device-floppy me-1"></i>Guardar cambios
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Precio calculado en tiempo real -->
    <div class="col-md-4">
      <div class="card sticky-top" style="top: 80px">
        <div class="card-header">
          <h6 class="mb-0"><i class="ti ti-calculator me-2"></i>Precio calculado</h6>
        </div>
        <div class="card-body">
          <table class="table table-sm mb-0">
            <tbody>
              <tr>
                <td class="text-muted small">Precio base</td>
                <td class="text-end">{formatCop(clientPrice.base_price)}</td>
              </tr>
              {#if !$form.negotiated_price}
                <tr>
                  <td class="text-muted small">+ Ajuste ({$form.adjustment_percentage}%)</td>
                  <td class="text-end text-info">
                    {formatCop(Number(clientPrice.base_price) * Number($form.adjustment_percentage) / 100)}
                  </td>
                </tr>
              {:else}
                <tr>
                  <td class="text-muted small">Precio negociado</td>
                  <td class="text-end text-warning">{formatCop($form.negotiated_price)}</td>
                </tr>
              {/if}
              {#if $form.discount_percentage}
                <tr>
                  <td class="text-muted small">- Descuento ({$form.discount_percentage}%)</td>
                  <td class="text-end text-danger">-{formatCop(finalPrice() * Number($form.discount_percentage) / 100)}</td>
                </tr>
              {/if}
              <tr class="border-top">
                <td class="fw-bold">Nuevo precio final</td>
                <td class="text-end fw-bold price-cop fs-5 text-primary">{formatCop(finalPrice())}</td>
              </tr>
              {#if $form.applies_iva}
                <tr>
                  <td class="text-muted small">+ IVA ({$form.iva_percentage}%)</td>
                  <td class="text-end text-warning">
                    {formatCop(finalPrice() * Number($form.iva_percentage) / 100)}
                  </td>
                </tr>
                <tr class="table-warning">
                  <td class="fw-bold">Total con IVA</td>
                  <td class="text-end fw-bold price-cop fs-5">
                    {formatCop(finalPrice() * (1 + Number($form.iva_percentage) / 100))}
                  </td>
                </tr>
              {/if}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</AppLayout>
