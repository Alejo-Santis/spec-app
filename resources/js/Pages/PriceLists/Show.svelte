<script>
  import { useForm, Link } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import ConfirmDelete from '../../Components/ConfirmDelete.svelte';

  let { priceList, previousLists = [], bundleServiceTypes = [] } = $props();

  function formatCop(v) {
    return '$ ' + Number(v).toLocaleString('es-CO');
  }

  // Formulario para generar desde lista anterior
  const genForm = useForm({ previous_price_list_id: '' });
  let generateModalEl;

  function openGenerateModal() {
    bootstrap.Modal.getOrCreateInstance(generateModalEl).show();
  }

  function submitGenerate(e) {
    e.preventDefault();
    genForm.post(`/price-lists/${priceList.uuid}/generate-from-previous`, {
      onSuccess: () => bootstrap.Modal.getInstance(generateModalEl)?.hide(),
    });
  }

  // Formulario para nuevo bundle tier
  const tierForm = useForm({
    service_type_id: '',
    name: '',
    quantity: '',
    price: '',
    is_active: true,
  });
  let tierModalEl;


  function openTierModal() {
    tierForm.reset();
    tierForm.clearErrors();
    bootstrap.Modal.getOrCreateInstance(tierModalEl).show();
  }

  function submitTier(e) {
    e.preventDefault();
    tierForm.post(`/price-lists/${priceList.uuid}/bundle-tiers`, {
      onSuccess: () => bootstrap.Modal.getInstance(tierModalEl)?.hide(),
    });
  }

  function activateList() {
    Swal.fire({
      title: '¿Activar esta lista?',
      text: 'La lista activa actual quedará desactivada.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, activar',
      cancelButtonText: 'Cancelar',
    }).then((result) => {
      if (result.isConfirmed) {
        const f = useForm({});
        f.post(`/price-lists/${priceList.uuid}/activate`);
      }
    });
  }
</script>




<svelte:head><title>{priceList.name} - SPEC</title></svelte:head>
<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-header-title"><h5 class="m-0">{priceList.name}</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/price-lists">Listas de Precios</a></li>
            <li class="breadcrumb-item active">{priceList.name}</li>
          </ul>
        </div>
        <div class="col-auto d-flex gap-2 flex-wrap">
          {#if !priceList.is_active}
            <button class="btn btn-success btn-sm" onclick={activateList}>
              <i class="ti ti-check me-1"></i>Activar lista
            </button>
          {/if}
          <button class="btn btn-outline-primary btn-sm" onclick={openGenerateModal}>
            <i class="ti ti-refresh me-1"></i>Generar desde anterior
          </button>
          <button class="btn btn-primary btn-sm" onclick={openTierModal}>
            <i class="ti ti-plus me-1"></i>Agregar tier de bolsa
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Info card -->
  <div class="row mb-3">
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h1 class="text-primary mb-0">{priceList.year}</h1>
          <p class="text-muted mb-0">Año</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h2 class="text-info mb-0">{priceList.adjustment_percentage}%</h2>
          <p class="text-muted mb-0">Ajuste anual</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h2 class="mb-0">{priceList.client_prices?.length ?? 0}</h2>
          <p class="text-muted mb-0">Precios asignados</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          {#if priceList.is_active}
            <h2 class="text-success mb-0"><i class="ti ti-check-circle"></i></h2>
            <p class="text-success mb-0 fw-bold">Lista activa</p>
          {:else}
            <h2 class="text-muted mb-0"><i class="ti ti-circle"></i></h2>
            <p class="text-muted mb-0">Inactiva</p>
          {/if}
        </div>
      </div>
    </div>
  </div>

  <!-- Bundle Tiers -->
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="ti ti-packages me-2"></i>Tiers de Bolsas</h5>
      <button class="btn btn-sm btn-primary" onclick={openTierModal}>
        <i class="ti ti-plus me-1"></i>Agregar
      </button>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Servicio</th>
              <th class="text-end">Cantidad</th>
              <th class="text-end">Precio total</th>
              <th class="text-end">Precio unitario</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {#each priceList.bundle_tiers ?? [] as tier}
              <tr>
                <td class="fw-medium">{tier.name}</td>
                <td><small class="text-muted">{tier.service_type?.name}</small></td>
                <td class="text-end">{Number(tier.quantity).toLocaleString('es-CO')}</td>
                <td class="text-end price-cop fw-bold">{formatCop(tier.price)}</td>
                <td class="text-end">
                  <small class="text-muted">{formatCop(tier.unit_price)} / u</small>
                </td>
                <td>
                  {#if tier.is_active}
                    <span class="badge bg-light-success text-success">Activo</span>
                  {:else}
                    <span class="badge bg-light-danger text-danger">Inactivo</span>
                  {/if}
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <Link href="/bundle-tiers/{tier.uuid}/edit" class="btn btn-xs btn-light-primary">
                      <i class="ti ti-pencil"></i>
                    </Link>
                    <ConfirmDelete action="/bundle-tiers/{tier.uuid}" title="¿Eliminar {tier.name}?" />
                  </div>
                </td>
              </tr>
            {:else}
              <tr>
                <td colspan="7" class="text-center text-muted py-3">Sin tiers de bolsa configurados.</td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Client Prices preview (primeros 10) -->
  {#if (priceList.client_prices ?? []).length > 0}
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="ti ti-currency-dollar me-2"></i>Precios de clientes</h5>
        <Link href="/client-prices?price_list_id={priceList.uuid}" class="btn btn-sm btn-light-primary">
          Ver todos <i class="ti ti-arrow-right ms-1"></i>
        </Link>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>Servicio</th>
                <th class="text-end">Precio final</th>
                <th>IVA</th>
              </tr>
            </thead>
            <tbody>
              {#each (priceList.client_prices ?? []).slice(0, 10) as cp}
                <tr>
                  <td>{cp.client?.business_name}</td>
                  <td><small class="text-muted">{cp.service_type?.name}</small></td>
                  <td class="text-end fw-bold price-cop">{formatCop(cp.final_price)}</td>
                  <td>
                    {#if cp.applies_iva}
                      <span class="badge bg-light-warning text-warning">{cp.iva_percentage}%</span>
                    {:else}
                      <span class="text-muted small">—</span>
                    {/if}
                  </td>
                </tr>
              {/each}
              {#if (priceList.client_prices ?? []).length > 10}
                <tr>
                  <td colspan="4" class="text-center text-muted small py-2">
                    ... y {priceList.client_prices.length - 10} más
                  </td>
                </tr>
              {/if}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  {/if}
</AppLayout>

<!-- Modal: Generar desde anterior -->
<div class="modal fade" tabindex="-1" bind:this={generateModalEl}>
  <div class="modal-dialog">
    <div class="modal-content">
      <form onsubmit={submitGenerate}>
        <div class="modal-header">
          <h5 class="modal-title"><i class="ti ti-refresh me-2"></i>Generar desde lista anterior</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="ti ti-info-circle me-1"></i>
            Se copiarán todos los precios de clientes y tiers de bolsa de la lista seleccionada,
            aplicando el ajuste del <strong>{priceList.adjustment_percentage}%</strong>.
          </div>
          <div class="mb-3">
            <label class="form-label">Lista de precios origen <span class="text-danger">*</span></label>
            <select class="form-select {$genForm.errors.previous_price_list_id ? 'is-invalid' : ''}"
              bind:value={$genForm.previous_price_list_id} required>
              <option value="">Seleccionar lista...</option>
              {#each previousLists as pl}
                <option value={pl.id}>{pl.name} ({pl.year})</option>
              {/each}
            </select>
            {#if $genForm.errors.previous_price_list_id}
              <div class="invalid-feedback">{$genForm.errors.previous_price_list_id}</div>
            {/if}
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" disabled={$genForm.processing}>
            {#if $genForm.processing}
              <span class="spinner-border spinner-border-sm me-1"></span>
            {/if}
            Generar precios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Nuevo tier -->
<div class="modal fade" tabindex="-1" bind:this={tierModalEl}>
  <div class="modal-dialog">
    <div class="modal-content">
      <form onsubmit={submitTier}>
        <div class="modal-header">
          <h5 class="modal-title"><i class="ti ti-packages me-2"></i>Nuevo tier de bolsa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Tipo de servicio <span class="text-danger">*</span></label>
              <select class="form-select {$tierForm.errors.service_type_id ? 'is-invalid' : ''}"
                bind:value={$tierForm.service_type_id} required>
                <option value="">Seleccionar...</option>
                {#each bundleServiceTypes as st}
                  {#if st}
                    <option value={st.id}>{st.name}</option>
                  {/if}
                {/each}
              </select>
              {#if $tierForm.errors.service_type_id}
                <div class="invalid-feedback">{$tierForm.errors.service_type_id}</div>
              {/if}
            </div>
            <div class="col-12">
              <label class="form-label">Nombre <span class="text-danger">*</span></label>
              <input type="text" class="form-control {$tierForm.errors.name ? 'is-invalid' : ''}"
                bind:value={$tierForm.name} placeholder="Ej: Bolsa 500" required>
              {#if $tierForm.errors.name}
                <div class="invalid-feedback">{$tierForm.errors.name}</div>
              {/if}
            </div>
            <div class="col-6">
              <label class="form-label">Cantidad <span class="text-danger">*</span></label>
              <input type="number" class="form-control {$tierForm.errors.quantity ? 'is-invalid' : ''}"
                bind:value={$tierForm.quantity} min="1" required>
              {#if $tierForm.errors.quantity}
                <div class="invalid-feedback">{$tierForm.errors.quantity}</div>
              {/if}
            </div>
            <div class="col-6">
              <label class="form-label">Precio total (COP) <span class="text-danger">*</span></label>
              <input type="number" class="form-control {$tierForm.errors.price ? 'is-invalid' : ''}"
                bind:value={$tierForm.price} min="0" required>
              {#if $tierForm.errors.price}
                <div class="invalid-feedback">{$tierForm.errors.price}</div>
              {/if}
            </div>
            {#if $tierForm.quantity > 0 && $tierForm.price > 0}
              <div class="col-12">
                <div class="alert alert-success py-2 mb-0">
                  Precio unitario: <strong>
                    {formatCop(($tierForm.price / $tierForm.quantity).toFixed(4))}
                  </strong> / unidad
                </div>
              </div>
            {/if}
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" disabled={$tierForm.processing}>
            {#if $tierForm.processing}
              <span class="spinner-border spinner-border-sm me-1"></span>
            {/if}
            Crear tier
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
