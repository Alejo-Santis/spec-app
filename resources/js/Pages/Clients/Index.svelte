<script>
  import { router, Link, page, useForm } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import Pagination from '../../Components/Pagination.svelte';
  import ConfirmDelete from '../../Components/ConfirmDelete.svelte';

  const perms = $derived(new Set($page.props.auth?.user?.permissions ?? []));

  let { clients, filters } = $props();

  let search   = $state(filters.search ?? '');
  let type     = $state(filters.type ?? '');
  let isActive = $state(filters.is_active ?? '');

  // Import modal
  let importOpen   = $state(false);
  let importForm   = useForm({ file: null });

  let debounce;
  function applyFilters() {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
      router.get('/clients', { search, type, is_active: isActive }, { preserveState: true, replace: true });
    }, 300);
  }

  function resetFilters() {
    search = ''; type = ''; isActive = '';
    router.get('/clients');
  }

  function submitImport() {
    $importForm.post('/clients/import', {
      onSuccess: () => { importOpen = false; $importForm.reset(); },
    });
  }
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col">
          <h5 class="m-0">Clientes</h5>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Clientes</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0 fw-semibold">
        <i class="ti ti-users me-2 text-primary"></i>Clientes
        <span class="badge bg-primary ms-1">{clients.total}</span>
      </h5>
      <div class="d-flex gap-2 flex-wrap" id="tour-client-actions">
        {#if perms.has('import-export.use')}
          <button type="button" class="btn btn-sm btn-light-success" onclick={() => importOpen = true}>
            <i class="ti ti-file-import me-1"></i>Importar
          </button>
          <a href="/clients/export" class="btn btn-sm btn-light-info">
            <i class="ti ti-file-export me-1"></i>Exportar Excel
          </a>
        {/if}
        {#if perms.has('clients.create')}
          <Link href="/clients/create" class="btn btn-sm btn-primary">
            <i class="ti ti-user-plus me-1"></i>Nuevo cliente
          </Link>
        {/if}
      </div>
    </div>

    <!-- Filtros -->
    <div class="card-body border-bottom pb-3" id="tour-client-filters">
      <div class="row g-2 align-items-end">
        <div class="col-md-5">
          <input type="search" class="form-control form-control-sm"
            placeholder="Buscar por nombre, NIT o nombre comercial..."
            bind:value={search} oninput={applyFilters} />
        </div>
        <div class="col-md-3">
          <select class="form-select form-select-sm" bind:value={type} onchange={applyFilters}>
            <option value="">Todos los tipos</option>
            <option value="juridica">Persona jurídica</option>
            <option value="natural">Persona natural</option>
          </select>
        </div>
        <div class="col-md-2">
          <select class="form-select form-select-sm" bind:value={isActive} onchange={applyFilters}>
            <option value="">Todos</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-sm btn-light w-100" onclick={resetFilters}>
            <i class="ti ti-refresh me-1"></i>Limpiar
          </button>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="card-body p-0">
      <div class="table-responsive" id="tour-client-table">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Razón social / Nombre</th>
              <th>Documento</th>
              <th>Tipo</th>
              <th>Ciudad</th>
              <th>Email</th>
              <th>Estado</th>
              <th class="text-end pe-3">Acciones</th>
            </tr>
          </thead>
          <tbody>
            {#each clients.data as client}
              <tr>
                <td class="ps-3">
                  <div class="fw-medium">{client.business_name}</div>
                  {#if client.trade_name}
                    <small class="text-muted">{client.trade_name}</small>
                  {/if}
                </td>
                <td><code class="text-muted">{client.document_number}{client.dv ? '-' + client.dv : ''}</code></td>
                <td>
                  {#if client.type === 'juridica'}
                    <span class="badge bg-light-primary text-primary">
                      <i class="ti ti-building me-1"></i>Jurídica
                    </span>
                  {:else}
                    <span class="badge bg-light-secondary text-secondary">
                      <i class="ti ti-user me-1"></i>Natural
                    </span>
                  {/if}
                </td>
                <td><small class="text-muted">{client.city ?? '—'}</small></td>
                <td>
                  {#if client.email}
                    <small>{client.email}</small>
                  {:else}
                    <span class="text-muted">—</span>
                  {/if}
                </td>
                <td>
                  {#if client.is_active}
                    <span class="badge bg-light-success text-success"><i class="ti ti-check me-1"></i>Activo</span>
                  {:else}
                    <span class="badge bg-light-danger text-danger"><i class="ti ti-x me-1"></i>Inactivo</span>
                  {/if}
                </td>
                <td class="text-end pe-3">
                  <div class="d-flex gap-1 justify-content-end">
                    <Link href="/clients/{client.id}" class="btn btn-icon btn-sm btn-light-info" title="Ver detalle">
                      <i class="ti ti-eye"></i>
                    </Link>
                    {#if perms.has('clients.update')}
                      <Link href="/clients/{client.id}/edit" class="btn btn-icon btn-sm btn-light-primary" title="Editar">
                        <i class="ti ti-pencil"></i>
                      </Link>
                    {/if}
                    {#if perms.has('clients.delete')}
                      <ConfirmDelete
                        action="/clients/{client.id}"
                        title="¿Eliminar {client.business_name}?"
                        text="Se eliminarán también sus precios asociados."
                      />
                    {/if}
                  </div>
                </td>
              </tr>
            {:else}
              <tr>
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="ti ti-users-off d-block mb-2" style="font-size:2.5rem; opacity:.3;"></i>
                  No se encontraron clientes.
                  <div class="mt-3">
                    <Link href="/clients/create" class="btn btn-sm btn-primary">
                      <i class="ti ti-user-plus me-1"></i>Crear primer cliente
                    </Link>
                  </div>
                </td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    </div>

    {#if clients.links?.length > 3}
      <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
        <small class="text-muted">
          Mostrando {clients.from ?? 0}–{clients.to ?? 0} de {clients.total} clientes
        </small>
        <Pagination links={clients.links} />
      </div>
    {/if}
  </div>
</AppLayout>

<!-- Modal importar clientes -->
{#if importOpen}
  <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.4);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-bottom">
          <h5 class="modal-title fw-semibold">
            <i class="ti ti-file-import me-2 text-success"></i>Importar Clientes
          </h5>
          <button type="button" class="btn-close" onclick={() => importOpen = false}></button>
        </div>
        <div class="modal-body">
          <!-- Descarga del template -->
          <div class="d-flex align-items-center gap-3 p-3 rounded mb-3"
            style="background:#f0f7ff; border:1px solid #c8e0ff;">
            <i class="ti ti-file-spreadsheet text-primary" style="font-size:2rem; flex-shrink:0;"></i>
            <div class="flex-grow-1">
              <div class="fw-semibold" style="font-size:0.875rem;">Template de importación</div>
              <small class="text-muted">Descarga el archivo CSV con el formato correcto y las columnas requeridas.</small>
            </div>
            <a href="/clients/template" class="btn btn-sm btn-primary flex-shrink-0" download>
              <i class="ti ti-download me-1"></i>Descargar
            </a>
          </div>

          <div class="mb-3">
            <label class="form-label fw-medium">Seleccionar archivo <span class="text-danger">*</span></label>
            <input type="file" class="form-control" accept=".csv,.xlsx,.xls"
              onchange={(e) => $importForm.file = e.target.files[0]} />
            {#if $importForm.errors.file}
              <div class="text-danger small mt-1">{$importForm.errors.file}</div>
            {/if}
          </div>

          <div class="bg-light rounded p-3" style="font-size:0.8rem;">
            <p class="fw-medium mb-1">Columnas requeridas:</p>
            <code class="text-muted" style="font-size:0.75rem;">
              tipo, razon_social_nombre, documento
            </code>
            <p class="mb-0 mt-2 text-muted">Los clientes con documento ya existente serán omitidos (no duplicados).</p>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-light" onclick={() => importOpen = false}>
            Cancelar
          </button>
          <button type="button" class="btn btn-success" onclick={submitImport}
            disabled={$importForm.processing || !$importForm.file}>
            {#if $importForm.processing}
              <span class="spinner-border spinner-border-sm me-1"></span>Importando...
            {:else}
              <i class="ti ti-upload me-1"></i>Importar clientes
            {/if}
          </button>
        </div>
      </div>
    </div>
  </div>
{/if}
