<script>
  import { useForm, Link } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';

  let { bundle } = $props();

  const remaining = $derived(bundle.quantity_remaining ?? (bundle.quantity_purchased - bundle.quantity_consumed));
  const pct = $derived(
    bundle.quantity_purchased > 0
      ? Math.round((bundle.quantity_consumed / bundle.quantity_purchased) * 100)
      : 0
  );

  function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleString('es-CO', {
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit',
    });
  }

  const form = useForm({
    quantity:    1,
    description: '',
    reference:   '',
  });

  function submit(e) {
    e.preventDefault();
    $form.post(`/client-bundles/${bundle.id}/consume`, {
      onSuccess: () => {
        $form.reset();
      },
    });
  }
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-header-title">
            <h5 class="m-0">Consumos — {bundle.bundle_tier?.name}</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/clients">Clientes</a></li>
            <li class="breadcrumb-item">
              <a href="/clients/{bundle.client_id}">{bundle.client?.business_name}</a>
            </li>
            <li class="breadcrumb-item">
              <Link href="/client-bundles/{bundle.id}">Bolsa</Link>
            </li>
            <li class="breadcrumb-item active">Consumos</li>
          </ul>
        </div>
        <div class="col-auto">
          <Link href="/client-bundles/{bundle.id}" class="btn btn-light btn-sm">
            <i class="ti ti-arrow-left me-1"></i>Volver a la bolsa
          </Link>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Formulario de nuevo consumo -->
    <div class="col-md-4">
      <div class="card {!bundle.is_active ? 'border-danger' : ''}">
        <div class="card-header {!bundle.is_active ? 'bg-light-danger' : 'bg-light-primary'}">
          <h5 class="mb-0 {!bundle.is_active ? 'text-danger' : 'text-primary'}">
            <i class="ti ti-bolt me-2"></i>Registrar consumo
          </h5>
        </div>
        <div class="card-body">

          <!-- Saldo actual -->
          <div class="alert {pct >= 90 ? 'alert-danger' : pct >= 70 ? 'alert-warning' : 'alert-info'} py-2 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <small class="fw-medium">Saldo disponible</small>
              <span class="fw-bold fs-5">{remaining.toLocaleString('es-CO')}</span>
            </div>
            <div class="progress" style="height:6px">
              <div
                class="progress-bar {pct >= 90 ? 'bg-danger' : pct >= 70 ? 'bg-warning' : 'bg-success'}"
                style="width:{pct}%">
              </div>
            </div>
            <div class="d-flex justify-content-between mt-1">
              <small class="text-muted">0</small>
              <small class="text-muted">{pct}% consumido</small>
              <small class="text-muted">{bundle.quantity_purchased.toLocaleString('es-CO')}</small>
            </div>
          </div>

          {#if !bundle.is_active}
            <div class="alert alert-danger">
              <i class="ti ti-lock me-1"></i>
              Esta bolsa está <strong>inactiva</strong> y no admite nuevos consumos.
            </div>
          {:else}
            <form onsubmit={submit}>
              <div class="mb-3">
                <label class="form-label" for="qty">
                  Cantidad a consumir <span class="text-danger">*</span>
                </label>
                <input
                  id="qty"
                  type="number"
                  min="1"
                  max={remaining}
                  class="form-control {$form.errors.quantity ? 'is-invalid' : ''}"
                  bind:value={$form.quantity}
                  required
                >
                {#if $form.errors.quantity}
                  <div class="invalid-feedback">{$form.errors.quantity}</div>
                {/if}
                <div class="form-text">Máximo disponible: {remaining.toLocaleString('es-CO')}</div>
              </div>

              <div class="mb-3">
                <label class="form-label" for="desc">Descripción</label>
                <input
                  id="desc"
                  type="text"
                  class="form-control {$form.errors.description ? 'is-invalid' : ''}"
                  bind:value={$form.description}
                  placeholder="Ej: Certificados emitidos lote 123"
                  maxlength="255"
                >
                {#if $form.errors.description}
                  <div class="invalid-feedback">{$form.errors.description}</div>
                {/if}
              </div>

              <div class="mb-3">
                <label class="form-label" for="ref">Referencia</label>
                <input
                  id="ref"
                  type="text"
                  class="form-control {$form.errors.reference ? 'is-invalid' : ''}"
                  bind:value={$form.reference}
                  placeholder="Nro. factura, orden, etc."
                  maxlength="255"
                >
                {#if $form.errors.reference}
                  <div class="invalid-feedback">{$form.errors.reference}</div>
                {/if}
              </div>

              <div class="d-grid">
                <button
                  type="submit"
                  class="btn btn-primary"
                  disabled={$form.processing || $form.quantity < 1 || $form.quantity > remaining}
                >
                  {#if $form.processing}
                    <span class="spinner-border spinner-border-sm me-1"></span>
                  {/if}
                  <i class="ti ti-bolt me-1"></i>Registrar consumo
                </button>
              </div>
            </form>
          {/if}
        </div>
      </div>

      <!-- Info del tier -->
      <div class="card mt-3">
        <div class="card-header">
          <h6 class="mb-0"><i class="ti ti-package me-2"></i>Detalle del tier</h6>
        </div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0">
            <tbody>
              <tr>
                <td class="text-muted small ps-3">Tier</td>
                <td class="pe-3">{bundle.bundle_tier?.name}</td>
              </tr>
              <tr>
                <td class="text-muted small ps-3">Servicio</td>
                <td class="pe-3">{bundle.bundle_tier?.service_type?.name}</td>
              </tr>
              <tr>
                <td class="text-muted small ps-3">Cliente</td>
                <td class="pe-3">
                  <Link href="/clients/{bundle.client_id}">{bundle.client?.business_name}</Link>
                </td>
              </tr>
              <tr>
                <td class="text-muted small ps-3">Vence</td>
                <td class="pe-3">
                  {bundle.expires_at
                    ? new Date(bundle.expires_at).toLocaleDateString('es-CO')
                    : 'Sin vencimiento'}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Historial de consumos -->
    <div class="col-md-8">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="ti ti-history me-2"></i>Historial de consumos
          </h5>
          <span class="badge bg-light-secondary text-secondary">
            {(bundle.consumptions ?? []).length} registros
          </span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>Fecha y hora</th>
                  <th class="text-end">Cantidad</th>
                  <th>Descripción</th>
                  <th>Referencia</th>
                  <th>Registró</th>
                </tr>
              </thead>
              <tbody>
                {#each bundle.consumptions ?? [] as c}
                  <tr>
                    <td>
                      <small class="font-monospace text-muted">{formatDate(c.consumed_at)}</small>
                    </td>
                    <td class="text-end">
                      <span class="badge bg-light-primary text-primary fw-bold">
                        {c.quantity.toLocaleString('es-CO')}
                      </span>
                    </td>
                    <td><small>{c.description ?? '—'}</small></td>
                    <td>
                      {#if c.reference}
                        <code class="small">{c.reference}</code>
                      {:else}
                        <span class="text-muted small">—</span>
                      {/if}
                    </td>
                    <td><small class="text-muted">{c.creator?.name ?? '—'}</small></td>
                  </tr>
                {:else}
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                      <i class="ti ti-inbox me-2"></i>No hay consumos registrados para esta bolsa.
                    </td>
                  </tr>
                {/each}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</AppLayout>
