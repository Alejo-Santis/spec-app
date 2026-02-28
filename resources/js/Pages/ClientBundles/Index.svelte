<script>
  import { router, Link } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';

  let { bundles, priceLists, filters } = $props();

  let clientSearch = $state(filters.client_search ?? '');
  let isActive     = $state(filters.is_active     ?? '');
  let priceListId  = $state(filters.price_list_id ?? '');

  let debounceTimer;

  function applyFilters() {
    router.get('/client-bundles', {
      client_search:  clientSearch  || undefined,
      is_active:      isActive      !== '' ? isActive : undefined,
      price_list_id:  priceListId   || undefined,
    }, { preserveState: true, replace: true });
  }

  function handleSearch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 350);
  }

  function clearFilters() {
    clientSearch = ''; isActive = ''; priceListId = '';
    router.get('/client-bundles', {}, { replace: true });
  }

  const hasFilters = $derived(clientSearch || isActive !== '' || priceListId);

  function formatCop(v) {
    return '$ ' + Number(v).toLocaleString('es-CO');
  }
  function formatDate(d) {
    if (!d) return '—';
    return new Date(d + 'T00:00:00').toLocaleDateString('es-CO');
  }
  function pct(b) {
    return b.quantity_purchased > 0
      ? Math.round((b.quantity_consumed / b.quantity_purchased) * 100)
      : 0;
  }
  function remaining(b) {
    return b.quantity_purchased - b.quantity_consumed;
  }
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-header-title"><h5 class="m-0">Bolsas / Paquetes</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Bolsas</li>
          </ul>
        </div>
        <div class="col-auto">
          <Link href="/client-bundles/create" class="btn btn-primary btn-sm">
            <i class="ti ti-plus me-1"></i>Nueva bolsa
          </Link>
        </div>
      </div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card mb-3" id="tour-bundles-filters">
    <div class="card-body py-2">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label small mb-1">Buscar cliente</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="ti ti-search"></i></span>
            <input type="text" class="form-control" placeholder="Nombre del cliente..."
              bind:value={clientSearch} oninput={handleSearch}>
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Lista de precios</label>
          <select class="form-select form-select-sm" bind:value={priceListId} onchange={applyFilters}>
            <option value="">Todas</option>
            {#each priceLists as pl}
              <option value={pl.id}>{pl.name} {pl.is_active ? '★' : ''}</option>
            {/each}
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">Estado</label>
          <select class="form-select form-select-sm" bind:value={isActive} onchange={applyFilters}>
            <option value="">Todas</option>
            <option value="1">Activas</option>
            <option value="0">Inactivas</option>
          </select>
        </div>
        <div class="col-md-1">
          {#if hasFilters}
            <button class="btn btn-sm btn-light w-100" onclick={clearFilters} title="Limpiar">
              <i class="ti ti-x"></i>
            </button>
          {/if}
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card" id="tour-bundles-table">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0"><i class="ti ti-package me-2"></i>Bolsas</h5>
      <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-light-secondary text-secondary">{bundles.total} bolsas</span>
        <a href="/client-bundles/export" class="btn btn-sm btn-light-info">
          <i class="ti ti-file-spreadsheet me-1"></i>Exportar
        </a>
      </div>
    </div>

    <div class="card-body p-0">
      {#if bundles.data.length === 0}
        <div class="text-center py-5 text-muted">
          <i class="ti ti-package fs-1 d-block mb-2 opacity-25"></i>
          <p class="mb-3">No hay bolsas{hasFilters ? ' con los filtros aplicados' : ''}.</p>
          <Link href="/client-bundles/create" class="btn btn-primary btn-sm">
            <i class="ti ti-plus me-1"></i>Crear primera bolsa
          </Link>
        </div>
      {:else}
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Cliente</th>
                <th>Tipo / Bolsa</th>
                <th>Lista</th>
                <th class="text-center">Consumo</th>
                <th class="text-end">Precio pagado</th>
                <th>Compra</th>
                <th>Vence</th>
                <th>Estado</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {#each bundles.data as b (b.id)}
                {@const p = pct(b)}
                {@const rem = remaining(b)}
                <tr>
                  <td>
                    <Link href="/clients/{b.client_id}" class="fw-medium text-decoration-none">
                      {b.client?.business_name}
                    </Link>
                  </td>
                  <td>
                    <span class="small">{b.bundle_tier?.service_type?.name}</span><br>
                    <span class="text-muted" style="font-size:0.75rem">{b.bundle_tier?.name}</span>
                  </td>
                  <td><span class="badge bg-light-secondary text-secondary">{b.price_list?.name}</span></td>
                  <td style="min-width:160px">
                    <div class="d-flex justify-content-between mb-1">
                      <small class="text-muted">{b.quantity_consumed.toLocaleString('es-CO')} / {b.quantity_purchased.toLocaleString('es-CO')}</small>
                      <small class="{p >= 90 ? 'text-danger fw-bold' : p >= 70 ? 'text-warning' : 'text-muted'}">{p}%</small>
                    </div>
                    <div class="progress" style="height:6px">
                      <div
                        class="progress-bar {p >= 90 ? 'bg-danger' : p >= 70 ? 'bg-warning' : 'bg-success'}"
                        style="width:{p}%">
                      </div>
                    </div>
                    <small class="text-muted">{rem.toLocaleString('es-CO')} disponibles</small>
                  </td>
                  <td class="text-end fw-medium">{formatCop(b.price_paid)}</td>
                  <td class="text-muted small">{formatDate(b.purchased_at)}</td>
                  <td class="small">
                    {#if b.expires_at}
                      <span class="{new Date(b.expires_at) < new Date() ? 'text-danger' : ''}">
                        {formatDate(b.expires_at)}
                      </span>
                    {:else}
                      <span class="text-muted">—</span>
                    {/if}
                  </td>
                  <td>
                    {#if b.is_active}
                      <span class="badge bg-light-success text-success">Activa</span>
                    {:else}
                      <span class="badge bg-light-danger text-danger">Inactiva</span>
                    {/if}
                    {#if p >= 90 && b.is_active}
                      <span class="badge bg-light-danger text-danger ms-1" title="¡Saldo crítico!">
                        <i class="ti ti-alert-triangle"></i>
                      </span>
                    {/if}
                  </td>
                  <td>
                    <Link href="/client-bundles/{b.id}" class="btn btn-icon btn-sm btn-light-primary" title="Ver detalle">
                      <i class="ti ti-eye"></i>
                    </Link>
                  </td>
                </tr>
              {/each}
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        {#if bundles.last_page > 1}
          <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
            <span class="text-muted small">Mostrando {bundles.from}–{bundles.to} de {bundles.total}</span>
            <nav>
              <ul class="pagination pagination-sm mb-0">
                {#each bundles.links as link}
                  <li class="page-item {link.active ? 'active' : ''} {!link.url ? 'disabled' : ''}">
                    {#if link.url}
                      <a class="page-link" href={link.url}
                        onclick={(e) => { e.preventDefault(); router.visit(link.url); }}>
                        {@html link.label}
                      </a>
                    {:else}
                      <span class="page-link">{@html link.label}</span>
                    {/if}
                  </li>
                {/each}
              </ul>
            </nav>
          </div>
        {/if}
      {/if}
    </div>
  </div>
</AppLayout>
