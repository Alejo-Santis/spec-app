<script>
  import { useForm } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import PriceInput from '../../Components/PriceInput.svelte';

  let { clients, priceLists, serviceTypes, selectedClientId } = $props();

  // Pre-seleccionar lista activa
  const activePriceList = priceLists.find(pl => pl.is_active);

  const form = useForm({
    client_id:             selectedClientId ?? '',
    service_type_id:       '',
    price_list_id:         activePriceList?.id ?? '',
    duration_years:        '',
    base_price:            0,
    adjustment_percentage: activePriceList?.adjustment_percentage ?? 0,
    negotiated_price:      null,
    discount_percentage:   null,
    applies_iva:           false,
    iva_percentage:        19,
    valid_from:            new Date().toISOString().split('T')[0],
    valid_until:           '',
    notes:                 '',
  });

  // Calcular precio final en tiempo real
  const finalPrice = $derived(() => {
    let price = $form.negotiated_price
      ? Number($form.negotiated_price)
      : Number($form.base_price) * (1 + Number($form.adjustment_percentage) / 100);

    if ($form.discount_percentage) {
      price = price * (1 - Number($form.discount_percentage) / 100);
    }
    return Math.round(price * 100) / 100;
  });

  const finalPriceWithIva = $derived(() => {
    if (!$form.applies_iva) return finalPrice();
    return Math.round(finalPrice() * (1 + Number($form.iva_percentage) / 100) * 100) / 100;
  });

  function formatCop(v) {
    return '$ ' + Number(v).toLocaleString('es-CO');
  }

  // Al cambiar servicio, sincronizar applies_iva del servicio
  function onServiceChange() {
    const st = serviceTypes.find(s => s.id == $form.service_type_id);
    if (st) {
      $form.applies_iva    = st.applies_iva;
      $form.iva_percentage = st.iva_percentage;
    }
  }

  function submit(e) {
    e.preventDefault();
    $form.post('/client-prices');
  }
</script>




<svelte:head><title>Asignar precio - SPEC</title></svelte:head>
<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col-md-12">
          <div class="page-header-title"><h5 class="m-0">Asignar precio a cliente</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/client-prices">Precios</a></li>
            <li class="breadcrumb-item active">Nuevo</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="ti ti-currency-dollar me-2"></i>Nuevo precio</h5>
        </div>
        <div class="card-body">
          <form onsubmit={submit}>
            <div class="row g-3">

              <!-- Cliente -->
              <div class="col-12">
                <label class="form-label" for="client_id">Cliente <span class="text-danger">*</span></label>
                <select id="client_id"
                  class="form-select {$form.errors.client_id ? 'is-invalid' : ''}"
                  bind:value={$form.client_id} required>
                  <option value="">Seleccionar cliente...</option>
                  {#each clients as c}
                    <option value={c.id}>{c.business_name} ({c.document_number})</option>
                  {/each}
                </select>
                {#if $form.errors.client_id}
                  <div class="invalid-feedback">{$form.errors.client_id}</div>
                {/if}
              </div>

              <!-- Lista de precios -->
              <div class="col-md-6">
                <label class="form-label" for="price_list_id">Lista de precios <span class="text-danger">*</span></label>
                <select id="price_list_id"
                  class="form-select {$form.errors.price_list_id ? 'is-invalid' : ''}"
                  bind:value={$form.price_list_id} required>
                  <option value="">Seleccionar lista...</option>
                  {#each priceLists as pl}
                    <option value={pl.id}>{pl.name} {pl.is_active ? '★' : ''}</option>
                  {/each}
                </select>
                {#if $form.errors.price_list_id}
                  <div class="invalid-feedback">{$form.errors.price_list_id}</div>
                {/if}
              </div>

              <!-- Tipo de servicio -->
              <div class="col-md-6">
                <label class="form-label" for="service_type_id">Tipo de servicio <span class="text-danger">*</span></label>
                <select id="service_type_id"
                  class="form-select {$form.errors.service_type_id ? 'is-invalid' : ''}"
                  bind:value={$form.service_type_id}
                  onchange={onServiceChange} required>
                  <option value="">Seleccionar servicio...</option>
                  {#each serviceTypes as st}
                    <option value={st.id}>{st.name}</option>
                  {/each}
                </select>
                {#if $form.errors.service_type_id}
                  <div class="invalid-feedback">{$form.errors.service_type_id}</div>
                {/if}
              </div>

              <!-- Duración (para certificados) -->
              <div class="col-md-3">
                <label class="form-label" for="duration_years">Duración (años)</label>
                <select id="duration_years" class="form-select" bind:value={$form.duration_years}>
                  <option value="">N/A</option>
                  <option value="1">1 año</option>
                  <option value="2">2 años</option>
                </select>
              </div>

              <!-- Precio base -->
              <div class="col-md-4">
                <label class="form-label">Precio base <span class="text-danger">*</span></label>
                <PriceInput
                  bind:value={$form.base_price}
                  class={$form.errors.base_price ? 'is-invalid' : ''}
                  required
                />
                {#if $form.errors.base_price}
                  <div class="invalid-feedback d-block">{$form.errors.base_price}</div>
                {/if}
              </div>

              <!-- % Ajuste -->
              <div class="col-md-2">
                <label class="form-label">Ajuste %</label>
                <div class="input-group">
                  <input type="number" step="0.01" class="form-control" bind:value={$form.adjustment_percentage} min="0">
                  <span class="input-group-text">%</span>
                </div>
              </div>

              <!-- Precio negociado -->
              <div class="col-md-4">
                <label class="form-label">Precio negociado</label>
                <PriceInput bind:value={$form.negotiated_price} placeholder="$ (opcional)" />
                <div class="form-text">Sobreescribe el cálculo base+ajuste.</div>
              </div>

              <!-- % Descuento -->
              <div class="col-md-2">
                <label class="form-label">Descuento %</label>
                <div class="input-group">
                  <input type="number" step="0.01" class="form-control" bind:value={$form.discount_percentage} min="0" max="100">
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

              <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="/client-prices" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-primary" disabled={$form.processing}>
                  {#if $form.processing}
                    <span class="spinner-border spinner-border-sm me-1"></span>
                  {/if}
                  <i class="ti ti-device-floppy me-1"></i>Guardar precio
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Preview del precio calculado -->
    <div class="col-md-4">
      <div class="card sticky-top" style="top: 80px">
        <div class="card-header">
          <h6 class="mb-0"><i class="ti ti-calculator me-2"></i>Resumen de precio</h6>
        </div>
        <div class="card-body">
          <table class="table table-sm mb-0">
            <tbody>
              <tr>
                <td class="text-muted small">Precio base</td>
                <td class="text-end">{formatCop($form.base_price)}</td>
              </tr>
              {#if !$form.negotiated_price}
                <tr>
                  <td class="text-muted small">+ Ajuste ({$form.adjustment_percentage}%)</td>
                  <td class="text-end text-info">
                    {formatCop(Number($form.base_price) * Number($form.adjustment_percentage) / 100)}
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
                  <td class="text-end text-danger">
                    -{formatCop(finalPrice() * Number($form.discount_percentage) / 100)}
                  </td>
                </tr>
              {/if}
              <tr class="border-top">
                <td class="fw-bold">Precio final</td>
                <td class="text-end fw-bold price-cop fs-5 text-primary">
                  {formatCop(finalPrice())}
                </td>
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
                  <td class="text-end fw-bold price-cop fs-5">{formatCop(finalPriceWithIva())}</td>
                </tr>
              {/if}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</AppLayout>
