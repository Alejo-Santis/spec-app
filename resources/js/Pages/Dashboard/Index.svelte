<script>
  import { page, Link } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';

  let { stats, activeBundles, bundlesAtRisk, activePriceList, clientsWithoutList, recentActivity } = $props();

  function formatCop(value) {
    return '$ ' + Number(value).toLocaleString('es-CO');
  }

  function formatDate(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    return d.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit' })
      + ' ' + d.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
  }

  const coveragePercent = $derived(
    stats.total_clients > 0
      ? Math.round((stats.clients_with_price / stats.total_clients) * 100)
      : 0
  );

  const actionColors = {
    created:   'success',
    updated:   'info',
    deleted:   'danger',
    activated: 'primary',
    consumed:  'warning',
    imported:  'secondary',
    exported:  'secondary',
  };
  const actionLabels = {
    created:   'Creado',
    updated:   'Actualizado',
    deleted:   'Eliminado',
    activated: 'Activado',
    consumed:  'Consumo',
    imported:  'Importado',
    exported:  'Exportado',
  };
</script>




<svelte:head><title>Dashboard - SPEC</title></svelte:head>
<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-header-title"><h5 class="m-0">Dashboard</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ul>
        </div>
        <div class="col-auto">
          {#if activePriceList}
            <span class="badge bg-info rounded-pill fs-6 px-3 py-2">
              <i class="ti ti-calendar me-1"></i>{activePriceList.name}
            </span>
            <span class="badge bg-success rounded-pill fs-6 px-3 py-2 ms-2" style="animation: pulse 2s infinite;">
                <i class="ti ti-circle-check me-1"></i>Activa
            </span>

            <style>
              @keyframes pulse {
                0%, 100% {
                  opacity: 1;
                  box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
                }
                50% {
                  opacity: 0.9;
                  box-shadow: 0 0 0 8px rgba(25, 135, 84, 0);
                }
              }
            </style>
          {:else}
            <span class="badge bg-warning text-dark fs-6 px-3 py-2">
              <i class="ti ti-alert-triangle me-1"></i>Sin lista activa
            </span>
          {/if}
        </div>
      </div>
    </div>
  </div>

  <!-- KPIs -->
  <div class="row g-3 mb-3" id="tour-kpis">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing:.05em">Clientes activos</p>
              <h2 class="mb-0 fw-bold">{stats.total_clients}</h2>
            </div>
            <div class="avtar avtar-m bg-light-primary rounded-3">
              <i class="ti ti-users f-24 text-primary"></i>
            </div>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-light-primary text-primary"><i class="ti ti-building me-1"></i>{stats.juridica_clients} jurídicas</span>
            <span class="badge bg-light-secondary text-secondary"><i class="ti ti-user me-1"></i>{stats.natural_clients} naturales</span>
          </div>
          <div class="mt-3 pt-2 border-top">
            <Link href="/clients" class="text-primary small fw-medium text-decoration-none">Ver todos <i class="ti ti-arrow-right ms-1"></i></Link>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing:.05em">Con precio asignado</p>
              <h2 class="mb-0 fw-bold">{stats.clients_with_price}</h2>
            </div>
            <div class="avtar avtar-m bg-light-success rounded-3">
              <i class="ti ti-currency-dollar f-24 text-success"></i>
            </div>
          </div>
          <div class="mb-1 d-flex justify-content-between">
            <small class="text-muted">Cobertura</small>
            <small class="fw-medium text-success">{coveragePercent}%</small>
          </div>
          <div class="progress" style="height:6px"><div class="progress-bar bg-success" style="width:{coveragePercent}%"></div></div>
          <div class="mt-3 pt-2 border-top">
            <Link href="/client-prices" class="text-success small fw-medium text-decoration-none">Ver precios <i class="ti ti-arrow-right ms-1"></i></Link>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm {stats.clients_without > 0 ? 'border-start border-warning border-3' : ''}">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing:.05em">Sin precio asignado</p>
              <h2 class="mb-0 fw-bold {stats.clients_without > 0 ? 'text-warning' : ''}">{stats.clients_without}</h2>
            </div>
            <div class="avtar avtar-m {stats.clients_without > 0 ? 'bg-light-warning' : 'bg-light-success'} rounded-3">
              <i class="ti ti-alert-triangle f-24 {stats.clients_without > 0 ? 'text-warning' : 'text-success'}"></i>
            </div>
          </div>
          {#if stats.clients_without > 0}
            <p class="text-muted small mb-3">Clientes sin precio en la lista activa</p>
            <Link href="/client-prices/create" class="btn btn-sm btn-warning w-100"><i class="ti ti-plus me-1"></i>Asignar precios</Link>
          {:else}
            <p class="text-success small mb-0"><i class="ti ti-check me-1"></i>Todos los clientes tienen precio</p>
          {/if}
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm {bundlesAtRisk.length > 0 ? 'border-start border-danger border-3' : ''}">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <p class="text-muted mb-1 small fw-medium text-uppercase" style="letter-spacing:.05em">Bolsas activas</p>
              <h2 class="mb-0 fw-bold">{stats.active_bundles}</h2>
            </div>
            <div class="avtar avtar-m {bundlesAtRisk.length > 0 ? 'bg-light-danger' : 'bg-light-info'} rounded-3">
              <i class="ti ti-package f-24 {bundlesAtRisk.length > 0 ? 'text-danger' : 'text-info'}"></i>
            </div>
          </div>
          <p class="text-muted small mb-1">Valor total pagado</p>
          <p class="fw-bold text-info mb-0">{formatCop(stats.total_bundle_value)}</p>
          {#if bundlesAtRisk.length > 0}
            <p class="text-danger small mt-1 mb-0"><i class="ti ti-alert-triangle me-1"></i>{bundlesAtRisk.length} con saldo crítico</p>
          {/if}
          <div class="mt-2 pt-2 border-top">
            <Link href="/client-bundles" class="text-info small fw-medium text-decoration-none">Ver bolsas <i class="ti ti-arrow-right ms-1"></i></Link>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Alertas -->
  {#if clientsWithoutList.length > 0 || bundlesAtRisk.length > 0}
    <div class="row g-3 mb-3">
      {#if clientsWithoutList.length > 0}
        <div class="col-md-6">
          <div class="card border-0 shadow-sm border-start border-warning border-3">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
              <h6 class="mb-0 text-warning fw-semibold"><i class="ti ti-alert-circle me-2"></i>Sin precio en lista activa</h6>
              <span class="badge bg-warning text-dark">{stats.clients_without}</span>
            </div>
            <div class="list-group list-group-flush">
              {#each clientsWithoutList as c}
                <Link href="/client-prices/create?client_id={c.uuid}"
                  class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3">
                  <div>
                    <span class="small fw-medium">{c.business_name}</span>
                    <small class="text-muted ms-2">{c.document_number}</small>
                  </div>
                  <span class="badge bg-light-warning text-warning">Asignar precio</span>
                </Link>
              {/each}
            </div>
          </div>
        </div>
      {/if}
      {#if bundlesAtRisk.length > 0}
        <div class="col-md-6">
          <div class="card border-0 shadow-sm border-start border-danger border-3">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
              <h6 class="mb-0 text-danger fw-semibold"><i class="ti ti-alert-triangle me-2"></i>Bolsas en saldo crítico</h6>
              <span class="badge bg-danger">{bundlesAtRisk.length}</span>
            </div>
            <div class="list-group list-group-flush">
              {#each bundlesAtRisk as b}
                <Link href="/client-bundles/{b.uuid}"
                  class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3">
                  <div>
                    <span class="small fw-medium">{b.client_name}</span>
                    <small class="text-muted ms-2">{b.tier_name}</small>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">{b.quantity_remaining.toLocaleString('es-CO')} ud.</small>
                    <span class="badge bg-danger">{b.consumption_percent}%</span>
                  </div>
                </Link>
              {/each}
            </div>
          </div>
        </div>
      {/if}
    </div>
  {/if}

  <!-- Bolsas + Actividad reciente -->
  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm h-100" id="tour-active-bundles">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-semibold"><i class="ti ti-package me-2 text-primary"></i>Bolsas activas <span class="badge bg-primary ms-1">{activeBundles.length}</span></h5>
          <div class="d-flex gap-2">
            <a href="/client-bundles/export" class="btn btn-sm btn-light-info"><i class="ti ti-file-export me-1"></i>Exportar</a>
            <Link href="/client-bundles" class="btn btn-sm btn-light">Ver todas <i class="ti ti-arrow-right ms-1"></i></Link>
          </div>
        </div>
        <div class="card-body p-0">
          {#if activeBundles.length > 0}
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-3">Cliente</th>
                    <th>Bolsa</th>
                    <th>Saldo</th>
                    <th style="min-width:120px">Consumo</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  {#each activeBundles as bundle}
                    {@const pct = bundle.consumption_percent}
                    <tr class="{pct >= 90 ? 'table-danger' : pct >= 70 ? 'table-warning' : ''}">
                      <td class="ps-3">
                        <div class="fw-medium small">{bundle.client_name}</div>
                        <small class="text-muted">{bundle.service_name}</small>
                      </td>
                      <td><small>{bundle.tier_name}</small></td>
                      <td>
                        <span class="fw-bold {bundle.quantity_remaining < bundle.quantity_purchased * 0.1 ? 'text-danger' : 'text-success'}">
                          {bundle.quantity_remaining.toLocaleString('es-CO')}
                        </span>
                        <small class="text-muted d-block">de {bundle.quantity_purchased.toLocaleString('es-CO')}</small>
                      </td>
                      <td>
                        <div class="progress mb-1" style="height:6px">
                          <div class="progress-bar {pct >= 90 ? 'bg-danger' : pct >= 70 ? 'bg-warning' : 'bg-success'}" style="width:{pct}%"></div>
                        </div>
                        <small class="text-muted">{pct}%</small>
                      </td>
                      <td><Link href="/client-bundles/{bundle.uuid}" class="btn btn-xs btn-light-primary"><i class="ti ti-eye"></i></Link></td>
                    </tr>
                  {/each}
                </tbody>
              </table>
            </div>
          {:else}
            <div class="text-center py-5">
              <i class="ti ti-packages text-muted" style="font-size:3rem; opacity:.3"></i>
              <p class="text-muted mt-2 mb-3">No hay bolsas activas registradas</p>
              <Link href="/client-bundles/create" class="btn btn-primary btn-sm"><i class="ti ti-plus me-1"></i>Crear primera bolsa</Link>
            </div>
          {/if}
        </div>
      </div>
    </div>

    <div class="col-lg-5 d-flex flex-column gap-3">
      <!-- Acciones rápidas -->
      <div class="card border-0 shadow-sm" id="tour-quick-actions">
        <div class="card-header bg-transparent border-bottom">
          <h6 class="mb-0 fw-semibold"><i class="ti ti-bolt me-2 text-warning"></i>Acciones rápidas</h6>
        </div>
        <div class="card-body p-2">
          <div class="list-group list-group-flush">
            <Link href="/clients/create" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-3 border-0 rounded mb-1">
              <div class="avtar avtar-s bg-light-primary rounded-2"><i class="ti ti-user-plus text-primary"></i></div>
              <div><div class="fw-medium small">Nuevo cliente</div><small class="text-muted">Registrar empresa o persona</small></div>
            </Link>
            <Link href="/client-prices/create" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-3 border-0 rounded mb-1">
              <div class="avtar avtar-s bg-light-success rounded-2"><i class="ti ti-currency-dollar text-success"></i></div>
              <div><div class="fw-medium small">Asignar precio</div><small class="text-muted">Crear precio para un cliente</small></div>
            </Link>
            <Link href="/client-bundles/create" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-3 border-0 rounded mb-1">
              <div class="avtar avtar-s bg-light-info rounded-2"><i class="ti ti-package text-info"></i></div>
              <div><div class="fw-medium small">Nueva bolsa</div><small class="text-muted">Asignar bolsa prepagada</small></div>
            </Link>
            <Link href="/price-lists/create" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-2 px-3 border-0 rounded">
              <div class="avtar avtar-s bg-light-warning rounded-2"><i class="ti ti-list text-warning"></i></div>
              <div><div class="fw-medium small">Nueva lista de precios</div><small class="text-muted">Crear lista para un nuevo año</small></div>
            </Link>
          </div>
        </div>
      </div>

      <!-- Actividad reciente -->
      <div class="card border-0 shadow-sm flex-grow-1" id="tour-recent-activity">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
          <h6 class="mb-0 fw-semibold"><i class="ti ti-timeline me-2 text-info"></i>Actividad reciente</h6>
          <Link href="/activity-logs" class="small text-info text-decoration-none">Ver todo <i class="ti ti-arrow-right ms-1"></i></Link>
        </div>
        <div class="card-body p-0">
          {#if recentActivity.length === 0}
            <div class="text-center py-4 text-muted small">
              <i class="ti ti-timeline-event-exclamation d-block mb-1 opacity-25 fs-2"></i>
              Sin actividad registrada aún
            </div>
          {:else}
            <div class="list-group list-group-flush">
              {#each recentActivity as log}
                <div class="list-group-item border-0 py-2 px-3">
                  <div class="d-flex align-items-start gap-2">
                    <span class="badge bg-light-{actionColors[log.action] ?? 'secondary'} text-{actionColors[log.action] ?? 'secondary'} mt-1" style="min-width:72px;text-align:center">
                      {actionLabels[log.action] ?? log.action}
                    </span>
                    <div class="flex-grow-1 overflow-hidden">
                      <p class="mb-0 small text-truncate" title={log.description}>{log.description}</p>
                      <small class="text-muted">{log.user?.name ?? 'Sistema'} · {formatDate(log.created_at)}</small>
                    </div>
                  </div>
                </div>
              {/each}
            </div>
          {/if}
        </div>
      </div>
    </div>
  </div>
</AppLayout>
