<script>
  import { router, page, Link } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import ConfirmDelete from '../../Components/ConfirmDelete.svelte';

  const perms = $derived(new Set($page.props.auth?.user?.permissions ?? []));

  let { usages, filters } = $props();

  let clientSearch = $state(filters.client_search ?? '');
  let periodYear   = $state(filters.period_year ?? '');
  let periodMonth  = $state(filters.period_month ?? '');

  let searchTimer;
  function applyFilters() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      router.get('/monthly-usages', {
        client_search: clientSearch || undefined,
        period_year:   periodYear   || undefined,
        period_month:  periodMonth  || undefined,
      }, { preserveState: true, replace: true });
    }, 300);
  }

  function formatCop(v) {
    return '$ ' + Number(v).toLocaleString('es-CO');
  }

  const months = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

  const totalAmount = $derived(
    usages.data.reduce((s, u) => s + parseFloat(u.total_amount ?? 0), 0)
  );
</script>

<svelte:head><title>Usos Mensuales - SPEC</title></svelte:head>
<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-header-title"><h5 class="m-0">Usos Mensuales (Facturación Ilimitada)</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Usos Mensuales</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-md-5">
          <label class="form-label small mb-1">Buscar cliente</label>
          <input type="text" class="form-control form-control-sm" placeholder="Nombre del cliente..."
            bind:value={clientSearch} oninput={applyFilters}>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Año</label>
          <select class="form-select form-select-sm" bind:value={periodYear} onchange={applyFilters}>
            <option value="">Todos los años</option>
            {#each [2026, 2025, 2024, 2023] as y}
              <option value={y}>{y}</option>
            {/each}
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Mes</label>
          <select class="form-select form-select-sm" bind:value={periodMonth} onchange={applyFilters}>
            <option value="">Todos los meses</option>
            {#each months.slice(1) as m, i}
              <option value={i + 1}>{m}</option>
            {/each}
          </select>
        </div>
        <div class="col-md-1">
          <button class="btn btn-sm btn-outline-secondary w-100" onclick={() => {
            clientSearch = ''; periodYear = ''; periodMonth = '';
            router.get('/monthly-usages', {}, { replace: true });
          }}>
            <i class="ti ti-x"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="ti ti-calendar-stats me-2"></i>Registros</h5>
      <span class="badge bg-light-primary text-primary">{usages.total} registros</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Servicio</th>
              <th>Período</th>
              <th class="text-end">Documentos</th>
              <th class="text-end">Precio unitario</th>
              <th class="text-end">Total</th>
              <th>Registrado por</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {#each usages.data as usage}
              <tr>
                <td>
                  <Link href="/clients/{usage.client?.uuid}" class="fw-medium text-decoration-none">
                    {usage.client?.business_name}
                  </Link>
                </td>
                <td><small class="text-muted">{usage.client_price?.service_type?.name ?? '—'}</small></td>
                <td>
                  <span class="badge bg-light-secondary text-secondary">
                    {months[usage.period_month] ?? usage.period_month} {usage.period_year}
                  </span>
                </td>
                <td class="text-end fw-bold">{Number(usage.document_count).toLocaleString('es-CO')}</td>
                <td class="text-end text-muted small">{formatCop(usage.unit_price)}</td>
                <td class="text-end fw-bold price-cop">{formatCop(usage.total_amount)}</td>
                <td><small class="text-muted">{usage.creator?.name ?? '—'}</small></td>
                <td>
                  {#if perms.has('monthly-usages.manage')}
                    <ConfirmDelete
                      action="/monthly-usages/{usage.uuid}"
                      title="¿Eliminar este registro?"
                      text="Se eliminará el uso de {months[usage.period_month]} {usage.period_year}."
                    />
                  {/if}
                </td>
              </tr>
            {:else}
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  No hay registros de uso mensual.
                </td>
              </tr>
            {/each}
          </tbody>
          {#if usages.data.length > 0}
            <tfoot class="table-light">
              <tr>
                <td colspan="5" class="text-end fw-bold text-muted small">Total página:</td>
                <td class="text-end fw-bold price-cop">{formatCop(totalAmount)}</td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
          {/if}
        </table>
      </div>
    </div>

    <!-- Paginación -->
    {#if usages.last_page > 1}
      <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
          Mostrando {usages.from}–{usages.to} de {usages.total}
        </small>
        <div class="d-flex gap-1">
          {#each usages.links as link}
            {#if link.url}
              <button
                class="btn btn-xs {link.active ? 'btn-primary' : 'btn-light'}"
                onclick={() => router.get(link.url)}
                disabled={!link.url}>
                {@html link.label}
              </button>
            {:else}
              <span class="btn btn-xs btn-light disabled">{@html link.label}</span>
            {/if}
          {/each}
        </div>
      </div>
    {/if}
  </div>
</AppLayout>
