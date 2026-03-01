<script>
  import { useForm, Link } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import ConfirmDelete from '../../Components/ConfirmDelete.svelte';

  let { bundle } = $props();

  const pct = $derived(
    bundle.quantity_purchased > 0
      ? Math.round((bundle.quantity_consumed / bundle.quantity_purchased) * 100)
      : 0
  );

  const remaining = $derived(bundle.quantity_remaining ?? (bundle.quantity_purchased - bundle.quantity_consumed));

  function formatCop(v) {
    return '$ ' + Number(v).toLocaleString('es-CO');
  }

  function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('es-CO');
  }

  // Edit form (expires_at, is_active, notes)
  const form = useForm({
    expires_at: bundle.expires_at ?? '',
    is_active:  bundle.is_active,
    notes:      bundle.notes ?? '',
  });

  let editMode = $state(false);

  function submit(e) {
    e.preventDefault();
    $form.put(`/client-bundles/${bundle.uuid}`, {
      onSuccess: () => { editMode = false; },
    });
  }
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-header-title">
            <h5 class="m-0">{bundle.bundle_tier?.name}</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/clients">Clientes</a></li>
            <li class="breadcrumb-item">
              <a href="/clients/{bundle.client?.uuid}">{bundle.client?.business_name}</a>
            </li>
            <li class="breadcrumb-item active">Bolsa</li>
          </ul>
        </div>
        <div class="col-auto d-flex gap-2">
          <Link href="/client-bundles/{bundle.uuid}/consumptions" class="btn btn-success btn-sm">
            <i class="ti ti-bolt me-1"></i>Registrar consumo
          </Link>
          <button class="btn btn-outline-primary btn-sm" onclick={() => editMode = !editMode}>
            <i class="ti ti-pencil me-1"></i>{editMode ? 'Cancelar' : 'Editar'}
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Columna principal -->
    <div class="col-md-8">

      <!-- Estado de la bolsa -->
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="ti ti-packages me-2"></i>Estado de la bolsa</h5>
          {#if bundle.is_active}
            <span class="badge bg-light-success text-success">Activa</span>
          {:else}
            <span class="badge bg-light-danger text-danger">Inactiva</span>
          {/if}
        </div>
        <div class="card-body">
          <div class="row g-3 mb-3">
            <div class="col-4 text-center">
              <div class="p-3 rounded bg-light">
                <h3 class="mb-0 text-primary">{bundle.quantity_purchased.toLocaleString('es-CO')}</h3>
                <small class="text-muted">Compradas</small>
              </div>
            </div>
            <div class="col-4 text-center">
              <div class="p-3 rounded bg-light">
                <h3 class="mb-0 text-warning">{bundle.quantity_consumed.toLocaleString('es-CO')}</h3>
                <small class="text-muted">Consumidas</small>
              </div>
            </div>
            <div class="col-4 text-center">
              <div class="p-3 rounded {remaining < bundle.quantity_purchased * 0.1 ? 'bg-danger bg-opacity-10' : 'bg-light'}">
                <h3 class="mb-0 {remaining < bundle.quantity_purchased * 0.1 ? 'text-danger' : 'text-success'}">
                  {remaining.toLocaleString('es-CO')}
                </h3>
                <small class="text-muted">Disponibles</small>
              </div>
            </div>
          </div>

          <!-- Barra de progreso -->
          <div class="mb-1 d-flex justify-content-between">
            <small class="text-muted">Consumo</small>
            <small class="fw-medium">{pct}%</small>
          </div>
          <div class="progress mb-2" style="height:12px">
            <div
              class="progress-bar {pct >= 90 ? 'bg-danger' : pct >= 70 ? 'bg-warning' : 'bg-success'}"
              style="width:{pct}%"
              role="progressbar"
              aria-valuenow={pct}
              aria-valuemin="0"
              aria-valuemax="100">
            </div>
          </div>
          {#if pct >= 90}
            <div class="alert alert-danger py-2 mb-0">
              <i class="ti ti-alert-triangle me-1"></i>
              <strong>¡Atención!</strong> La bolsa está casi agotada. Solo quedan {remaining.toLocaleString('es-CO')} unidades.
            </div>
          {:else if pct >= 70}
            <div class="alert alert-warning py-2 mb-0">
              <i class="ti ti-alert-circle me-1"></i>
              La bolsa supera el 70% de consumo.
            </div>
          {/if}
        </div>
      </div>

      <!-- Últimos consumos -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="ti ti-history me-2"></i>Últimos consumos</h5>
          <Link href="/client-bundles/{bundle.uuid}/consumptions" class="btn btn-sm btn-light-primary">
            Ver todos <i class="ti ti-arrow-right ms-1"></i>
          </Link>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th class="text-end">Cantidad</th>
                  <th>Descripción</th>
                  <th>Referencia</th>
                  <th>Registró</th>
                </tr>
              </thead>
              <tbody>
                {#each (bundle.consumptions ?? []).slice(0, 10) as c}
                  <tr>
                    <td><small>{formatDate(c.consumed_at)}</small></td>
                    <td class="text-end fw-medium">{c.quantity.toLocaleString('es-CO')}</td>
                    <td><small class="text-muted">{c.description ?? '—'}</small></td>
                    <td><small class="text-muted font-monospace">{c.reference ?? '—'}</small></td>
                    <td><small class="text-muted">{c.creator?.name ?? '—'}</small></td>
                  </tr>
                {:else}
                  <tr>
                    <td colspan="5" class="text-center text-muted py-3">Sin consumos registrados.</td>
                  </tr>
                {/each}
                {#if (bundle.consumptions ?? []).length > 10}
                  <tr>
                    <td colspan="5" class="text-center text-muted small py-2">
                      ... y {bundle.consumptions.length - 10} más —
                      <Link href="/client-bundles/{bundle.uuid}/consumptions">ver todos</Link>
                    </td>
                  </tr>
                {/if}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Columna lateral -->
    <div class="col-md-4">

      <!-- Info general -->
      <div class="card mb-3">
        <div class="card-header">
          <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Información</h6>
        </div>
        <div class="card-body">
          <table class="table table-sm mb-0">
            <tbody>
              <tr>
                <td class="text-muted small">Cliente</td>
                <td class="fw-medium">
                  <Link href="/clients/{bundle.client?.uuid}">{bundle.client?.business_name}</Link>
                </td>
              </tr>
              <tr>
                <td class="text-muted small">Servicio</td>
                <td>{bundle.bundle_tier?.service_type?.name}</td>
              </tr>
              <tr>
                <td class="text-muted small">Lista de precios</td>
                <td>{bundle.price_list?.name}</td>
              </tr>
              <tr>
                <td class="text-muted small">Precio pagado</td>
                <td class="fw-bold price-cop">{formatCop(bundle.price_paid)}</td>
              </tr>
              <tr>
                <td class="text-muted small">Precio por unidad</td>
                <td class="text-info">
                  {formatCop(bundle.price_paid / bundle.quantity_purchased)} / u
                </td>
              </tr>
              <tr>
                <td class="text-muted small">Fecha de compra</td>
                <td>{formatDate(bundle.purchased_at)}</td>
              </tr>
              <tr>
                <td class="text-muted small">Vencimiento</td>
                <td>
                  {#if bundle.expires_at}
                    <span class="{new Date(bundle.expires_at) < new Date() ? 'text-danger' : ''}">
                      {formatDate(bundle.expires_at)}
                    </span>
                  {:else}
                    <span class="text-muted">Sin vencimiento</span>
                  {/if}
                </td>
              </tr>
            </tbody>
          </table>
          {#if bundle.notes}
            <hr class="my-2">
            <small class="text-muted">{bundle.notes}</small>
          {/if}
        </div>
      </div>

      <!-- Formulario edición -->
      {#if editMode}
        <div class="card border-primary">
          <div class="card-header bg-light-primary">
            <h6 class="mb-0 text-primary"><i class="ti ti-pencil me-2"></i>Editar bolsa</h6>
          </div>
          <div class="card-body">
            <form onsubmit={submit}>
              <div class="mb-3">
                <label class="form-label" for="expires_at">Fecha de vencimiento</label>
                <input id="expires_at" type="date" class="form-control"
                  bind:value={$form.expires_at}>
                <div class="form-text">Dejar vacío si no vence.</div>
              </div>
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="is_active"
                    bind:checked={$form.is_active}>
                  <label class="form-check-label" for="is_active">Bolsa activa</label>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label" for="notes">Notas</label>
                <textarea id="notes" class="form-control" rows="2" bind:value={$form.notes}></textarea>
              </div>
              <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-sm btn-light" onclick={() => editMode = false}>
                  Cancelar
                </button>
                <button type="submit" class="btn btn-sm btn-primary" disabled={$form.processing}>
                  {#if $form.processing}
                    <span class="spinner-border spinner-border-sm me-1"></span>
                  {/if}
                  <i class="ti ti-device-floppy me-1"></i>Guardar
                </button>
              </div>
            </form>
          </div>
        </div>
      {/if}
    </div>
  </div>
</AppLayout>
