<script>
  import { Link } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import ConfirmDelete from '../../Components/ConfirmDelete.svelte';

  let { client } = $props();

  function formatCop(v) {
    return '$ ' + Number(v).toLocaleString('es-CO');
  }

  const prices       = $derived(client.current_prices ?? []);
  const bundles      = $derived(client.active_bundles ?? []);
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-header-title">
            <h5 class="m-0">{client.business_name}</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/clients">Clientes</a></li>
            <li class="breadcrumb-item active">{client.business_name}</li>
          </ul>
        </div>
        <div class="col-auto d-flex gap-2">
          <Link href="/clients/{client.id}/edit" class="btn btn-primary btn-sm">
            <i class="ti ti-pencil me-1"></i>Editar
          </Link>
          <ConfirmDelete
            action="/clients/{client.id}"
            title="¿Eliminar {client.business_name}?"
            text="Se eliminarán también sus precios asociados."
            class="btn btn-sm btn-danger"
          />
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Datos del cliente -->
    <div class="col-md-4">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="ti ti-user me-2"></i>Datos generales</h5>
          {#if client.is_active}
            <span class="badge bg-light-success text-success">Activo</span>
          {:else}
            <span class="badge bg-light-danger text-danger">Inactivo</span>
          {/if}
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-5 text-muted small">Tipo</dt>
            <dd class="col-sm-7">
              {#if client.type === 'juridica'}
                <span class="badge bg-light-primary text-primary">Jurídica</span>
              {:else}
                <span class="badge bg-light-secondary text-secondary">Natural</span>
              {/if}
            </dd>

            <dt class="col-sm-5 text-muted small">Documento</dt>
            <dd class="col-sm-7">
              <code>{client.document_number}{client.dv ? '-' + client.dv : ''}</code>
            </dd>

            {#if client.trade_name}
              <dt class="col-sm-5 text-muted small">Nombre comercial</dt>
              <dd class="col-sm-7">{client.trade_name}</dd>
            {/if}

            <dt class="col-sm-5 text-muted small">Régimen</dt>
            <dd class="col-sm-7 text-capitalize">{client.tax_regime}</dd>

            {#if client.ciiu_code}
              <dt class="col-sm-5 text-muted small">CIIU</dt>
              <dd class="col-sm-7">{client.ciiu_code}</dd>
            {/if}
          </dl>

          {#if (client.tax_responsibilities ?? []).length > 0}
            <hr>
            <p class="text-muted small mb-1">Responsabilidades DIAN:</p>
            <div class="d-flex flex-wrap gap-1">
              {#each client.tax_responsibilities as resp}
                <span class="badge bg-light-warning text-warning">{resp}</span>
              {/each}
            </div>
          {/if}
        </div>
      </div>

      <!-- Contacto -->
      <div class="card mt-3">
        <div class="card-header">
          <h5 class="mb-0"><i class="ti ti-phone me-2"></i>Contacto</h5>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            {#if client.email}
              <dt class="col-sm-4 text-muted small">Email</dt>
              <dd class="col-sm-8"><small>{client.email}</small></dd>
            {/if}
            {#if client.email_billing}
              <dt class="col-sm-4 text-muted small">Facturación</dt>
              <dd class="col-sm-8"><small>{client.email_billing}</small></dd>
            {/if}
            {#if client.phone}
              <dt class="col-sm-4 text-muted small">Teléfono</dt>
              <dd class="col-sm-8">{client.phone}</dd>
            {/if}
            {#if client.mobile}
              <dt class="col-sm-4 text-muted small">Móvil</dt>
              <dd class="col-sm-8">{client.mobile}</dd>
            {/if}
            {#if client.city}
              <dt class="col-sm-4 text-muted small">Ciudad</dt>
              <dd class="col-sm-8">{client.city}{client.department ? ', ' + client.department : ''}</dd>
            {/if}
            {#if client.address}
              <dt class="col-sm-4 text-muted small">Dirección</dt>
              <dd class="col-sm-8"><small>{client.address}</small></dd>
            {/if}
          </dl>
        </div>
      </div>

      {#if client.notes}
        <div class="card mt-3">
          <div class="card-header"><h5 class="mb-0"><i class="ti ti-notes me-2"></i>Notas</h5></div>
          <div class="card-body"><p class="mb-0 small text-muted">{client.notes}</p></div>
        </div>
      {/if}
    </div>

    <!-- Precios y Bolsas -->
    <div class="col-md-8">

      <!-- Precios vigentes -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="ti ti-currency-dollar me-2"></i>Precios vigentes</h5>
          <Link href="/client-prices/create?client_id={client.id}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus me-1"></i>Agregar precio
          </Link>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th>Servicio</th>
                  <th>Lista</th>
                  <th class="text-end">Precio final</th>
                  <th class="text-end">IVA</th>
                  <th>Vigencia</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {#each prices as price}
                  <tr>
                    <td>
                      <span class="fw-medium">{price.service_type?.name}</span>
                      {#if price.duration_years}
                        <small class="text-muted ms-1">({price.duration_years} año{price.duration_years > 1 ? 's' : ''})</small>
                      {/if}
                    </td>
                    <td><small class="text-muted">{price.price_list?.name}</small></td>
                    <td class="text-end fw-bold price-cop">{formatCop(price.final_price)}</td>
                    <td class="text-end">
                      {#if price.applies_iva}
                        <span class="badge bg-light-warning text-warning">{price.iva_percentage}%</span>
                      {:else}
                        <span class="text-muted small">—</span>
                      {/if}
                    </td>
                    <td>
                      <small class="text-muted">
                        {new Date(price.valid_from).toLocaleDateString('es-CO')}
                        {price.valid_until ? '→ ' + new Date(price.valid_until).toLocaleDateString('es-CO') : ''}
                      </small>
                    </td>
                    <td>
                      <Link href="/client-prices/{price.id}/edit" class="btn btn-xs btn-light-primary">
                        <i class="ti ti-pencil"></i>
                      </Link>
                    </td>
                  </tr>
                {:else}
                  <tr>
                    <td colspan="6" class="text-center text-muted py-3">
                      Sin precios asignados en la lista activa.
                    </td>
                  </tr>
                {/each}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Bolsas activas -->
      <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="ti ti-packages me-2"></i>Bolsas activas</h5>
          <Link href="/client-bundles/create?client_id={client.id}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus me-1"></i>Nueva bolsa
          </Link>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th>Bolsa</th>
                  <th>Saldo</th>
                  <th>Consumo</th>
                  <th>Vence</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {#each bundles as bundle}
                  {@const pct = Math.round((bundle.quantity_consumed / bundle.quantity_purchased) * 100)}
                  <tr class="{pct >= 90 ? 'table-danger' : pct >= 70 ? 'table-warning' : ''}">
                    <td>
                      <div class="fw-medium">{bundle.bundle_tier?.name}</div>
                      <small class="text-muted">{bundle.bundle_tier?.service_type?.name}</small>
                    </td>
                    <td>
                      <span class="fw-bold {bundle.quantity_remaining < bundle.quantity_purchased * 0.1 ? 'text-danger' : 'text-success'}">
                        {(bundle.quantity_remaining ?? 0).toLocaleString('es-CO')}
                      </span>
                      <small class="text-muted"> / {bundle.quantity_purchased.toLocaleString('es-CO')}</small>
                    </td>
                    <td style="min-width:120px">
                      <div class="progress" style="height:6px">
                        <div class="progress-bar {pct >= 90 ? 'bg-danger' : pct >= 70 ? 'bg-warning' : 'bg-success'}"
                          style="width:{pct}%"></div>
                      </div>
                      <small class="text-muted">{pct}%</small>
                    </td>
                    <td>
                      {#if bundle.expires_at}
                        <small>{new Date(bundle.expires_at).toLocaleDateString('es-CO')}</small>
                      {:else}
                        <small class="text-muted">Sin vencimiento</small>
                      {/if}
                    </td>
                    <td>
                      <div class="d-flex gap-1">
                        <Link href="/client-bundles/{bundle.id}" class="btn btn-xs btn-light-info">
                          <i class="ti ti-eye"></i>
                        </Link>
                        <Link href="/client-bundles/{bundle.id}/consumptions" class="btn btn-xs btn-light-success">
                          <i class="ti ti-bolt"></i>
                        </Link>
                      </div>
                    </td>
                  </tr>
                {:else}
                  <tr>
                    <td colspan="5" class="text-center text-muted py-3">Sin bolsas activas.</td>
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
