<script>
  import { Link } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';

  let { client } = $props();

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

  const activeBundles   = $derived((client.bundles ?? []).filter(b => b.is_active));
  const inactiveBundles = $derived((client.bundles ?? []).filter(b => !b.is_active));
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-header-title"><h5 class="m-0">Bolsas de {client.business_name}</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/clients">Clientes</a></li>
            <li class="breadcrumb-item"><a href="/clients/{client.id}">{client.business_name}</a></li>
            <li class="breadcrumb-item active">Bolsas</li>
          </ul>
        </div>
        <div class="col-auto">
          <Link href="/client-bundles/create?client_id={client.id}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus me-1"></i>Nueva bolsa
          </Link>
        </div>
      </div>
    </div>
  </div>

  <!-- Bolsas activas -->
  <div class="card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0"><i class="ti ti-package me-2"></i>Bolsas activas</h5>
      <span class="badge bg-light-success text-success">{activeBundles.length}</span>
    </div>
    <div class="card-body p-0">
      {#if activeBundles.length === 0}
        <div class="text-center py-4 text-muted">
          <i class="ti ti-package-off fs-2 d-block mb-2 opacity-25"></i>
          Este cliente no tiene bolsas activas.
        </div>
      {:else}
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Tipo / Bolsa</th>
                <th>Lista</th>
                <th class="text-center">Consumo</th>
                <th class="text-end">Precio pagado</th>
                <th>Compra</th>
                <th>Vence</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {#each activeBundles as b (b.id)}
                {@const p = pct(b)}
                {@const rem = remaining(b)}
                <tr>
                  <td>
                    <span class="fw-medium small">{b.bundle_tier?.service_type?.name}</span><br>
                    <span class="text-muted" style="font-size:0.75rem">{b.bundle_tier?.name}</span>
                  </td>
                  <td><span class="badge bg-light-secondary text-secondary">{b.price_list?.name}</span></td>
                  <td style="min-width:150px">
                    <div class="d-flex justify-content-between mb-1">
                      <small class="text-muted">{b.quantity_consumed.toLocaleString('es-CO')} / {b.quantity_purchased.toLocaleString('es-CO')}</small>
                      <small class="{p >= 90 ? 'text-danger fw-bold' : p >= 70 ? 'text-warning' : 'text-muted'}">{p}%</small>
                    </div>
                    <div class="progress" style="height:6px">
                      <div class="progress-bar {p >= 90 ? 'bg-danger' : p >= 70 ? 'bg-warning' : 'bg-success'}"
                        style="width:{p}%"></div>
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
                    <Link href="/client-bundles/{b.id}" class="btn btn-icon btn-sm btn-light-primary" title="Ver detalle">
                      <i class="ti ti-eye"></i>
                    </Link>
                    <Link href="/client-bundles/{b.id}/consumptions" class="btn btn-icon btn-sm btn-light-success" title="Registrar consumo">
                      <i class="ti ti-bolt"></i>
                    </Link>
                  </td>
                </tr>
              {/each}
            </tbody>
          </table>
        </div>
      {/if}
    </div>
  </div>

  <!-- Bolsas inactivas / históricas -->
  {#if inactiveBundles.length > 0}
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0 text-muted"><i class="ti ti-archive me-2"></i>Bolsas anteriores / inactivas</h6>
        <span class="badge bg-light-secondary text-secondary">{inactiveBundles.length}</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Tipo / Bolsa</th>
                <th class="text-center">Uso</th>
                <th class="text-end">Precio</th>
                <th>Compra</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {#each inactiveBundles as b (b.id)}
                <tr class="text-muted">
                  <td>
                    <small>{b.bundle_tier?.service_type?.name} — {b.bundle_tier?.name}</small>
                  </td>
                  <td class="text-center">
                    <small>{b.quantity_consumed.toLocaleString('es-CO')} / {b.quantity_purchased.toLocaleString('es-CO')}</small>
                  </td>
                  <td class="text-end"><small>{formatCop(b.price_paid)}</small></td>
                  <td><small>{formatDate(b.purchased_at)}</small></td>
                  <td>
                    <Link href="/client-bundles/{b.id}" class="btn btn-icon btn-sm btn-light" title="Ver detalle">
                      <i class="ti ti-eye"></i>
                    </Link>
                  </td>
                </tr>
              {/each}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  {/if}
</AppLayout>
