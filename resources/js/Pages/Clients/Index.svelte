<script>
  import { router, Link } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import Pagination from '../../Components/Pagination.svelte';
  import ConfirmDelete from '../../Components/ConfirmDelete.svelte';

  let { clients, filters } = $props();

  let search    = $state(filters.search ?? '');
  let type      = $state(filters.type ?? '');
  let isActive  = $state(filters.is_active ?? '');

  let debounce;
  function applyFilters() {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
      router.get('/clients', { search, type, is_active: isActive }, {
        preserveState: true,
        replace: true,
      });
    }, 300);
  }

  function resetFilters() {
    search = ''; type = ''; isActive = '';
    router.get('/clients');
  }
</script>

<AppLayout>
  <!-- Breadcrumb -->
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col-md-12">
          <div class="page-header-title">
            <h5 class="m-0">Clientes</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Clientes</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0">
        <i class="ti ti-users me-2"></i>Listado de Clientes
        <span class="badge bg-primary ms-2">{clients.total}</span>
      </h5>
      <Link href="/clients/create" class="btn btn-primary btn-sm">
        <i class="ti ti-plus me-1"></i>Nuevo cliente
      </Link>
    </div>

    <!-- Filtros -->
    <div class="card-body border-bottom pb-3">
      <div class="row g-2">
        <div class="col-md-5">
          <input
            type="search"
            class="form-control form-control-sm"
            placeholder="Buscar por nombre, NIT o nombre comercial..."
            bind:value={search}
            oninput={applyFilters}
          />
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
              <th>Razón social / Nombre</th>
              <th>Documento</th>
              <th>Tipo</th>
              <th>Ciudad</th>
              <th>Email</th>
              <th>Estado</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            {#each clients.data as client}
              <tr>
                <td>
                  <div class="fw-medium">{client.business_name}</div>
                  {#if client.trade_name}
                    <small class="text-muted">{client.trade_name}</small>
                  {/if}
                </td>
                <td>
                  <code>{client.document_number}{client.dv ? '-' + client.dv : ''}</code>
                </td>
                <td>
                  {#if client.type === 'juridica'}
                    <span class="badge bg-light-primary text-primary">Jurídica</span>
                  {:else}
                    <span class="badge bg-light-secondary text-secondary">Natural</span>
                  {/if}
                </td>
                <td>{client.city ?? '—'}</td>
                <td>
                  {#if client.email}
                    <small>{client.email}</small>
                  {:else}
                    <span class="text-muted">—</span>
                  {/if}
                </td>
                <td>
                  {#if client.is_active}
                    <span class="badge bg-light-success text-success">Activo</span>
                  {:else}
                    <span class="badge bg-light-danger text-danger">Inactivo</span>
                  {/if}
                </td>
                <td class="text-end">
                  <div class="d-flex gap-1 justify-content-end">
                    <Link href="/clients/{client.id}" class="btn btn-sm btn-light-info" title="Ver detalle">
                      <i class="ti ti-eye"></i>
                    </Link>
                    <Link href="/clients/{client.id}/edit" class="btn btn-sm btn-light-primary" title="Editar">
                      <i class="ti ti-pencil"></i>
                    </Link>
                    <ConfirmDelete
                      action="/clients/{client.id}"
                      title="¿Eliminar {client.business_name}?"
                      text="Se eliminarán también sus precios asociados."
                    />
                  </div>
                </td>
              </tr>
            {:else}
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="ti ti-users-off fs-3 d-block mb-2"></i>
                  No se encontraron clientes.
                </td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    </div>

    {#if clients.links?.length > 3}
      <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted">
            Mostrando {clients.from ?? 0}–{clients.to ?? 0} de {clients.total} clientes
          </small>
          <Pagination links={clients.links} />
        </div>
      </div>
    {/if}
  </div>
</AppLayout>
