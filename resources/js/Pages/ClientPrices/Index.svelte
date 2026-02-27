<script>
  import { router, Link, useForm } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import Pagination from '../../Components/Pagination.svelte';
  import ConfirmDelete from '../../Components/ConfirmDelete.svelte';

  let { prices, priceLists, serviceTypes, filters } = $props();

  let priceListId   = $state(filters.price_list_id ?? '');
  let clientSearch  = $state(filters.client_search ?? '');
  let serviceTypeId = $state(filters.service_type_id ?? '');
  let importOpen    = $state(false);
  let importForm    = useForm({ file: null });

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

  const exportUrl = $derived(
    '/client-prices/export' + (priceListId ? '?price_list_id=' + priceListId : '')
  );

  function submitImport() {
    importForm.post('/client-prices/import', {
      onSuccess: () => { importOpen = false; importForm.reset(); },
    });
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
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0 fw-semibold">
        <i class="ti ti-currency-dollar me-2 text-primary"></i>Precios por Cliente
        <span class="badge bg-primary ms-1">{prices.total}</span>
      </h5>
      <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-light-success" onclick={() => importOpen = true}>
          <i class="ti ti-file-import me-1"></i>Importar
        </button>
        <a href={exportUrl} class="btn btn-sm btn-light-info">
          <i class="ti ti-file-export me-1"></i>Exportar Excel
        </a>
        <Link href="/client-prices/create" class="btn btn-sm btn-primary">
          <i class="ti ti-plus me-1"></i>Asignar precio
        </Link>
      </div>
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
            <i class="ti ti-refresh me-1"></i>Limpiar
          </button>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Cliente</th>
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
      <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
        <small class="text-muted">Mostrando {prices.from ?? 0}–{prices.to ?? 0} de {prices.total}</small>
        <Pagination links={prices.links} />
      </div>
    {/if}
  </div>
</AppLayout>

<!-- Modal importar precios -->
{#if importOpen}
  <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.4);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold">
            <i class="ti ti-file-import me-2 text-success"></i>Importar Precios
          </h5>
          <button type="button" class="btn-close" onclick={() => importOpen = false}></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info d-flex align-items-start gap-2 py-2 mb-3">
            <i class="ti ti-info-circle mt-1 flex-shrink-0"></i>
            <div>
              Usa el template oficial para importar precios masivamente.
              <a href="/client-prices/template" class="alert-link ms-1">
                <i class="ti ti-download me-1"></i>Descargar template
              </a>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Archivo CSV o Excel</label>
            <input type="file" class="form-control" accept=".csv,.xlsx,.xls"
              onchange={(e) => importForm.file = e.target.files[0]} />
            {#if importForm.errors.file}
              <div class="text-danger small mt-1">{importForm.errors.file}</div>
            {/if}
          </div>
          <div class="bg-light rounded p-3" style="font-size:0.8rem;">
            <p class="fw-medium mb-1">Columnas requeridas:</p>
            <code class="text-muted" style="font-size:0.75rem;">
              documento_cliente, tipo_de_servicio, lista_de_precios, precio_base, valido_desde
            </code>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" onclick={() => importOpen = false}>
            <i class="ti ti-x me-1"></i>Cancelar
          </button>
          <button type="button" class="btn btn-success" onclick={submitImport}
            disabled={importForm.processing || !importForm.file}>
            {#if importForm.processing}
              <span class="spinner-border spinner-border-sm me-1"></span>Importando...
            {:else}
              <i class="ti ti-upload me-1"></i>Importar
            {/if}
          </button>
        </div>
      </div>
    </div>
  </div>
{/if}
