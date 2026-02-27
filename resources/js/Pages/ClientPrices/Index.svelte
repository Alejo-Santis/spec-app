<script>
  import { router, Link } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import Pagination from '../../Components/Pagination.svelte';
  import ConfirmDelete from '../../Components/ConfirmDelete.svelte';

  let { prices, priceLists, serviceTypes, filters } = $props();

  let priceListId   = $state(filters.price_list_id ?? '');
  let clientSearch  = $state(filters.client_search ?? '');
  let serviceTypeId = $state(filters.service_type_id ?? '');

  let debounce;
  function applyFilters() {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
      router.get('/client-prices', {
        price_list_id: priceListId,
        client_search: clientSearch,
        service_type_id: serviceTypeId,
      }, { preserveState: true, replace: true });
    }, 300);
  }

  function formatCop(v) {
    return '$ ' + Number(v).toLocaleString('es-CO');
  }
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col-md-12">
          <div class="page-header-title"><h5 class="m-0">Precios por Cliente</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Precios por Cliente</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0">
        <i class="ti ti-currency-dollar me-2"></i>Precios por Cliente
        <span class="badge bg-primary ms-2">{prices.total}</span>
      </h5>
      <Link href="/client-prices/create" class="btn btn-primary btn-sm">
        <i class="ti ti-plus me-1"></i>Asignar precio
      </Link>
    </div>

    <!-- Filtros -->
    <div class="card-body border-bottom pb-3">
      <div class="row g-2">
        <!-- Selector de lista de precios -->
        <div class="col-md-3">
          <select class="form-select form-select-sm" bind:value={priceListId} onchange={applyFilters}>
            <option value="">Todas las listas</option>
            {#each priceLists as pl}
              <option value={pl.id}>
                {pl.name} {pl.is_active ? '★' : ''}
              </option>
            {/each}
          </select>
        </div>
        <!-- Búsqueda cliente -->
        <div class="col-md-4">
          <input type="search" class="form-control form-control-sm"
            placeholder="Buscar cliente..."
            bind:value={clientSearch}
            oninput={applyFilters}>
        </div>
        <!-- Tipo de servicio -->
        <div class="col-md-3">
          <select class="form-select form-select-sm" bind:value={serviceTypeId} onchange={applyFilters}>
            <option value="">Todos los servicios</option>
            {#each serviceTypes as st}
              <option value={st.id}>{st.name}</option>
            {/each}
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-sm btn-light w-100" onclick={() => {
            priceListId = ''; clientSearch = ''; serviceTypeId = '';
            router.get('/client-prices');
          }}>
            <i class="ti ti-x me-1"></i>Limpiar
          </button>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Servicio</th>
              <th>Lista</th>
              <th class="text-end">Precio base</th>
              <th class="text-end">Precio final</th>
              <th>IVA</th>
              <th>Negociado</th>
              <th>Vigencia</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            {#each prices.data as cp}
              <tr>
                <td>
                  <Link href="/clients/{cp.client?.id}" class="fw-medium text-decoration-none">
                    {cp.client?.business_name}
                  </Link>
                </td>
                <td>
                  <small>{cp.service_type?.name}</small>
                  {#if cp.duration_years}
                    <small class="text-muted ms-1">({cp.duration_years}a)</small>
                  {/if}
                </td>
                <td><small class="text-muted">{cp.price_list?.name}</small></td>
                <td class="text-end text-muted small">{formatCop(cp.base_price)}</td>
                <td class="text-end fw-bold price-cop">
                  {formatCop(cp.final_price)}
                  {#if cp.negotiated_price}
                    <i class="ti ti-pencil text-warning ms-1" title="Precio negociado"></i>
                  {/if}
                </td>
                <td>
                  {#if cp.applies_iva}
                    <span class="badge bg-light-warning text-warning">{cp.iva_percentage}%</span>
                  {:else}
                    <span class="text-muted small">—</span>
                  {/if}
                </td>
                <td>
                  {#if cp.discount_percentage}
                    <span class="badge bg-light-success text-success">-{cp.discount_percentage}%</span>
                  {:else}
                    <span class="text-muted small">—</span>
                  {/if}
                </td>
                <td>
                  <small class="text-muted">
                    {new Date(cp.valid_from).toLocaleDateString('es-CO')}
                  </small>
                </td>
                <td class="text-end">
                  <div class="d-flex gap-1 justify-content-end">
                    <Link href="/client-prices/{cp.id}/edit" class="btn btn-xs btn-light-primary">
                      <i class="ti ti-pencil"></i>
                    </Link>
                    <ConfirmDelete action="/client-prices/{cp.id}" title="¿Eliminar este precio?" />
                  </div>
                </td>
              </tr>
            {:else}
              <tr>
                <td colspan="9" class="text-center text-muted py-4">
                  No se encontraron precios con los filtros seleccionados.
                </td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    </div>

    {#if prices.links?.length > 3}
      <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted">
            Mostrando {prices.from ?? 0}–{prices.to ?? 0} de {prices.total}
          </small>
          <Pagination links={prices.links} />
        </div>
      </div>
    {/if}
  </div>
</AppLayout>
