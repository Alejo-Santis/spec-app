<script>
  import { router } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';

  let { logs, modules, actions, users, filters } = $props();

  // Etiquetas legibles
  const actionLabels = {
    created:   'Creado',
    updated:   'Actualizado',
    deleted:   'Eliminado',
    activated: 'Activado',
    consumed:  'Consumo',
    imported:  'Importado',
    exported:  'Exportado',
  };
  const actionColors = {
    created:   'success',
    updated:   'info',
    deleted:   'danger',
    activated: 'primary',
    consumed:  'warning',
    imported:  'secondary',
    exported:  'secondary',
  };
  const moduleLabels = {
    Client:          'Cliente',
    PriceList:       'Lista de precios',
    ClientPrice:     'Precio cliente',
    ClientBundle:    'Bolsa',
    'service-types': 'Tipos de servicio',
    'bundle-tiers':  'Bundle tiers',
    users:           'Usuarios',
    profile:         'Perfil',
  };
  const moduleIcons = {
    Client:          'ti-users',
    PriceList:       'ti-list',
    ClientPrice:     'ti-currency-dollar',
    ClientBundle:    'ti-package',
    'service-types': 'ti-briefcase',
    'bundle-tiers':  'ti-layers-intersect',
    users:           'ti-users-group',
    profile:         'ti-user-circle',
  };

  // Filtros locales
  let search    = $state(filters.search    ?? '');
  let module    = $state(filters.module    ?? '');
  let action    = $state(filters.action    ?? '');
  let userId    = $state(filters.user_id   ?? '');
  let dateFrom  = $state(filters.date_from ?? '');
  let dateTo    = $state(filters.date_to   ?? '');

  let debounceTimer;
  function applyFilters() {
    router.get('/activity-logs', {
      search:    search   || undefined,
      module:    module   || undefined,
      action:    action   || undefined,
      user_id:   userId   || undefined,
      date_from: dateFrom || undefined,
      date_to:   dateTo   || undefined,
    }, { preserveState: true, replace: true });
  }

  function handleSearchInput() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 350);
  }

  function clearFilters() {
    search = ''; module = ''; action = ''; userId = ''; dateFrom = ''; dateTo = '';
    router.get('/activity-logs', {}, { replace: true });
  }

  function formatDate(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    return d.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' })
      + ' ' + d.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
  }

  const hasFilters = $derived(search || module || action || userId || dateFrom || dateTo);
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col-md-12">
          <div class="page-header-title"><h5 class="m-0">Log de actividades</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Actividades</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card mb-3">
    <div class="card-body py-2">
      <div class="row g-2 align-items-end">

        <div class="col-md-3">
          <label class="form-label small mb-1">Buscar</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="ti ti-search"></i></span>
            <input type="text" class="form-control" placeholder="Descripción o registro..."
              bind:value={search} oninput={handleSearchInput}>
          </div>
        </div>

        <div class="col-md-2">
          <label class="form-label small mb-1">Módulo</label>
          <select class="form-select form-select-sm" bind:value={module} onchange={applyFilters}>
            <option value="">Todos</option>
            {#each modules as m}
              <option value={m}>{moduleLabels[m] ?? m}</option>
            {/each}
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small mb-1">Acción</label>
          <select class="form-select form-select-sm" bind:value={action} onchange={applyFilters}>
            <option value="">Todas</option>
            {#each actions as a}
              <option value={a}>{actionLabels[a] ?? a}</option>
            {/each}
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small mb-1">Usuario</label>
          <select class="form-select form-select-sm" bind:value={userId} onchange={applyFilters}>
            <option value="">Todos</option>
            {#each users as u}
              <option value={u.id}>{u.name}</option>
            {/each}
          </select>
        </div>

        <div class="col-md-1">
          <label class="form-label small mb-1">Desde</label>
          <input type="date" class="form-control form-control-sm"
            bind:value={dateFrom} onchange={applyFilters}>
        </div>

        <div class="col-md-1">
          <label class="form-label small mb-1">Hasta</label>
          <input type="date" class="form-control form-control-sm"
            bind:value={dateTo} onchange={applyFilters}>
        </div>

        <div class="col-md-1 d-flex gap-1">
          {#if hasFilters}
            <button class="btn btn-sm btn-light w-100" onclick={clearFilters} title="Limpiar filtros">
              <i class="ti ti-x"></i>
            </button>
          {/if}
        </div>

      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">
        <i class="ti ti-timeline me-2"></i>Historial de actividades
      </h5>
      <span class="badge bg-light-secondary text-secondary">{logs.total} registros</span>
    </div>

    <div class="card-body p-0">
      {#if logs.data.length === 0}
        <div class="text-center py-5 text-muted">
          <i class="ti ti-clipboard-list fs-1 d-block mb-2 opacity-25"></i>
          <span>No hay actividades registradas{hasFilters ? ' con los filtros aplicados' : ''}.</span>
        </div>
      {:else}
        <div class="table-responsive">
          <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:155px">Fecha / Hora</th>
                <th style="width:110px">Módulo</th>
                <th style="width:100px">Acción</th>
                <th>Descripción</th>
                <th style="width:140px">Usuario</th>
                <th style="width:115px">IP</th>
                <th style="width:40px"></th>
              </tr>
            </thead>
            <tbody>
              {#each logs.data as log (log.id)}
                <tr>
                  <td class="text-muted small text-nowrap">{formatDate(log.created_at)}</td>
                  <td>
                    <span class="badge bg-light-secondary text-secondary">
                      <i class="ti {moduleIcons[log.module] ?? 'ti-circle'} me-1"></i>
                      {moduleLabels[log.module] ?? log.module}
                    </span>
                  </td>
                  <td>
                    <span class="badge bg-light-{actionColors[log.action] ?? 'secondary'} text-{actionColors[log.action] ?? 'secondary'}">
                      {actionLabels[log.action] ?? log.action}
                    </span>
                  </td>
                  <td>
                    <span class="small">{log.description}</span>
                    {#if log.subject_label && log.subject_label !== log.description}
                      <br><span class="text-muted" style="font-size:0.75rem">{log.subject_label}</span>
                    {/if}
                  </td>
                  <td class="small">
                    {#if log.user}
                      <i class="ti ti-user me-1 text-muted"></i>{log.user.name}
                    {:else}
                      <span class="text-muted">—</span>
                    {/if}
                  </td>
                  <td class="text-muted small">{log.ip_address ?? '—'}</td>
                  <td>
                    {#if log.properties}
                      <button
                        class="btn btn-icon btn-sm btn-light"
                        title="Ver detalles"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-props-{log.id}"
                      >
                        <i class="ti ti-eye"></i>
                      </button>

                      <!-- Modal de propiedades -->
                      <div class="modal fade" id="modal-props-{log.id}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                            <div class="modal-header py-2">
                              <h6 class="modal-title">Detalle — {log.description}</h6>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                              <pre class="bg-light rounded p-3 small mb-0" style="white-space:pre-wrap;word-break:break-word">{JSON.stringify(log.properties, null, 2)}</pre>
                            </div>
                          </div>
                        </div>
                      </div>
                    {/if}
                  </td>
                </tr>
              {/each}
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        {#if logs.last_page > 1}
          <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
            <span class="text-muted small">
              Mostrando {logs.from}–{logs.to} de {logs.total}
            </span>
            <nav>
              <ul class="pagination pagination-sm mb-0">
                {#each logs.links as link}
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
