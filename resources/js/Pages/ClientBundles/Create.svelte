<script>
  import { useForm } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import PriceInput from '../../Components/PriceInput.svelte';

  let { clients, priceLists, bundleTiers, selectedClientId } = $props();

  const activePriceList = priceLists.find(pl => pl.is_active);

  const form = useForm({
    client_id:          selectedClientId ?? '',
    bundle_tier_id:     '',
    price_list_id:      activePriceList?.id ?? '',
    quantity_purchased: 1,
    price_paid:         0,
    purchased_at:       new Date().toISOString().split('T')[0],
    expires_at:         '',
    notes:              '',
  });

  // Filtrar tiers según la lista seleccionada
  const filteredTiers = $derived(
    bundleTiers.filter(t => !$form.price_list_id || t.price_list_id == $form.price_list_id)
  );

  // Auto-completar precio al seleccionar tier
  function onTierChange() {
    const tier = bundleTiers.find(t => t.id == $form.bundle_tier_id);
    if (tier) {
      $form.price_paid = Number(tier.price);
    }
  }

  const selectedTier = $derived(bundleTiers.find(t => t.id == $form.bundle_tier_id));

  function formatCop(v) {
    return '$ ' + Number(v).toLocaleString('es-CO');
  }

  function submit(e) {
    e.preventDefault();
    $form.post('/client-bundles');
  }
</script>




<svelte:head><title>Nueva bolsa - SPEC</title></svelte:head>
<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col-md-12">
          <div class="page-header-title"><h5 class="m-0">Nueva bolsa / paquete</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Nueva bolsa</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-7">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="ti ti-packages me-2"></i>Asignar bolsa a cliente</h5>
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
                    <option value={c.id}>{c.business_name}</option>
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
                  <option value="">Seleccionar...</option>
                  {#each priceLists as pl}
                    <option value={pl.id}>{pl.name} {pl.is_active ? '★' : ''}</option>
                  {/each}
                </select>
              </div>

              <!-- Tier de bolsa -->
              <div class="col-md-6">
                <label class="form-label" for="bundle_tier_id">Tipo de bolsa <span class="text-danger">*</span></label>
                <select id="bundle_tier_id"
                  class="form-select {$form.errors.bundle_tier_id ? 'is-invalid' : ''}"
                  bind:value={$form.bundle_tier_id}
                  onchange={onTierChange} required>
                  <option value="">Seleccionar tier...</option>
                  {#each filteredTiers as tier}
                    <option value={tier.id}>
                      {tier.name} — {tier.service_type?.name} ({Number(tier.quantity).toLocaleString('es-CO')} u)
                    </option>
                  {/each}
                </select>
                {#if $form.errors.bundle_tier_id}
                  <div class="invalid-feedback">{$form.errors.bundle_tier_id}</div>
                {/if}
              </div>

              <!-- Cantidad -->
              <div class="col-md-4">
                <label class="form-label" for="qty">Cantidad comprada <span class="text-danger">*</span></label>
                <input id="qty" type="number" min="1"
                  class="form-control {$form.errors.quantity_purchased ? 'is-invalid' : ''}"
                  bind:value={$form.quantity_purchased} required>
                {#if $form.errors.quantity_purchased}
                  <div class="invalid-feedback">{$form.errors.quantity_purchased}</div>
                {/if}
              </div>

              <!-- Precio pagado -->
              <div class="col-md-5">
                <label class="form-label">Precio pagado <span class="text-danger">*</span></label>
                <PriceInput
                  bind:value={$form.price_paid}
                  class={$form.errors.price_paid ? 'is-invalid' : ''}
                  required
                />
                {#if $form.errors.price_paid}
                  <div class="invalid-feedback d-block">{$form.errors.price_paid}</div>
                {/if}
              </div>

              <!-- Fecha de compra -->
              <div class="col-md-3">
                <label class="form-label" for="purchased_at">Fecha de compra <span class="text-danger">*</span></label>
                <input id="purchased_at" type="date"
                  class="form-control {$form.errors.purchased_at ? 'is-invalid' : ''}"
                  bind:value={$form.purchased_at} required>
              </div>

              <!-- Fecha de vencimiento -->
              <div class="col-md-4">
                <label class="form-label" for="expires_at">Vence el</label>
                <input id="expires_at" type="date" class="form-control" bind:value={$form.expires_at}>
                <div class="form-text">Dejar vacío si no vence.</div>
              </div>

              <!-- Notas -->
              <div class="col-12">
                <label class="form-label" for="notes">Notas</label>
                <textarea id="notes" class="form-control" rows="2" bind:value={$form.notes}></textarea>
              </div>

              <div class="col-12 d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-light" onclick={() => history.back()}>Cancelar</button>
                <button type="submit" class="btn btn-primary" disabled={$form.processing}>
                  {#if $form.processing}
                    <span class="spinner-border spinner-border-sm me-1"></span>
                  {/if}
                  <i class="ti ti-device-floppy me-1"></i>Crear bolsa
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Info del tier seleccionado -->
    {#if selectedTier}
      <div class="col-md-5">
        <div class="card border-primary">
          <div class="card-header bg-light-primary">
            <h6 class="mb-0 text-primary"><i class="ti ti-package me-2"></i>{selectedTier.name}</h6>
          </div>
          <div class="card-body">
            <table class="table table-sm mb-0">
              <tbody>
                <tr>
                  <td class="text-muted">Servicio</td>
                  <td class="fw-medium">{selectedTier.service_type?.name}</td>
                </tr>
                <tr>
                  <td class="text-muted">Unidades</td>
                  <td class="fw-bold">{Number(selectedTier.quantity).toLocaleString('es-CO')}</td>
                </tr>
                <tr>
                  <td class="text-muted">Precio de lista</td>
                  <td class="fw-bold price-cop">{formatCop(selectedTier.price)}</td>
                </tr>
                <tr>
                  <td class="text-muted">Precio unitario</td>
                  <td class="text-info">{formatCop(selectedTier.unit_price)} / u</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    {/if}
  </div>
</AppLayout>
