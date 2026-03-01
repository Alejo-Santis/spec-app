<script>
  import { useForm, router, page } from '@inertiajs/svelte';

  const perms = $derived(new Set($page.props.auth?.user?.permissions ?? []));
  import AppLayout from '../../Layouts/AppLayout.svelte';
  import ConfirmDelete from '../../Components/ConfirmDelete.svelte';

  let { serviceTypes } = $props();

  // Modal state
  let modalMode  = $state('create');  // 'create' | 'edit'
  let editTarget = $state(null);
  let modalEl;

  const form = useForm({
    name:           '',
    billing_type:   'unit',
    applies_iva:    false,
    iva_percentage: 19,
    description:    '',
    is_active:      true,
  });

  function openCreate() {
    modalMode = 'create';
    editTarget = null;
    $form.reset();
    $form.clearErrors();
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }

  function openEdit(st) {
    modalMode  = 'edit';
    editTarget = st;
    $form.name           = st.name;
    $form.billing_type   = st.billing_type;
    $form.applies_iva    = st.applies_iva;
    $form.iva_percentage = st.iva_percentage;
    $form.description    = st.description ?? '';
    $form.is_active      = st.is_active;
    $form.clearErrors();
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }

  function submit(e) {
    e.preventDefault();
    if (modalMode === 'create') {
      $form.post('/service-types', {
        onSuccess: () => {
          bootstrap.Modal.getInstance(modalEl)?.hide();
          $form.reset();
        },
      });
    } else {
      $form.put(`/service-types/${editTarget.uuid}`, {
        onSuccess: () => {
          bootstrap.Modal.getInstance(modalEl)?.hide();
        },
      });
    }
  }
</script>




<svelte:head><title>Tipos de servicio - SPEC</title></svelte:head>
<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col-md-12">
          <div class="page-header-title"><h5 class="m-0">Tipos de Servicio</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Tipos de Servicio</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="ti ti-settings me-2"></i>Tipos de Servicio</h5>
      {#if perms.has('service-types.manage')}
        <button class="btn btn-primary btn-sm" onclick={openCreate}>
          <i class="ti ti-plus me-1"></i>Nuevo tipo
        </button>
      {/if}
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Tipo facturación</th>
              <th>IVA</th>
              <th>Descripción</th>
              <th>Estado</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            {#each serviceTypes as st}
              <tr>
                <td class="fw-medium">{st.name}</td>
                <td>
                  {#if st.billing_type === 'bundle'}
                    <span class="badge bg-light-primary text-primary">Bolsa</span>
                  {:else}
                    <span class="badge bg-light-secondary text-secondary">Unitario</span>
                  {/if}
                </td>
                <td>
                  {#if st.applies_iva}
                    <span class="badge bg-light-warning text-warning">{st.iva_percentage}%</span>
                  {:else}
                    <span class="text-muted">No aplica</span>
                  {/if}
                </td>
                <td><small class="text-muted">{st.description ?? '—'}</small></td>
                <td>
                  {#if st.is_active}
                    <span class="badge bg-light-success text-success">Activo</span>
                  {:else}
                    <span class="badge bg-light-danger text-danger">Inactivo</span>
                  {/if}
                </td>
                <td class="text-end">
                  {#if perms.has('service-types.manage')}
                    <button class="btn btn-sm btn-light-primary" onclick={() => openEdit(st)}>
                      <i class="ti ti-pencil"></i>
                    </button>
                    <ConfirmDelete
                      action="/service-types/{st.uuid}"
                      title="¿Desactivar {st.name}?"
                      text="El tipo de servicio quedará inactivo."
                      label="Desactivar"
                    />
                  {/if}
                </td>
              </tr>
            {:else}
              <tr>
                <td colspan="6" class="text-center text-muted py-4">No hay tipos de servicio registrados.</td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</AppLayout>

<!-- Modal create/edit -->
<div class="modal fade" id="stModal" tabindex="-1" bind:this={modalEl}>
  <div class="modal-dialog">
    <div class="modal-content">
      <form onsubmit={submit}>
        <div class="modal-header">
          <h5 class="modal-title">
            {modalMode === 'create' ? 'Nuevo tipo de servicio' : 'Editar tipo de servicio'}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" for="st_name">Nombre <span class="text-danger">*</span></label>
            <input id="st_name" type="text" class="form-control {$form.errors.name ? 'is-invalid' : ''}"
              bind:value={$form.name} required>
            {#if $form.errors.name}
              <div class="invalid-feedback">{$form.errors.name}</div>
            {/if}
          </div>

          <div class="mb-3">
            <label class="form-label">Tipo de facturación</label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" id="bt_unit" value="unit"
                  bind:group={$form.billing_type}>
                <label class="form-check-label" for="bt_unit">Unitario</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" id="bt_bundle" value="bundle"
                  bind:group={$form.billing_type}>
                <label class="form-check-label" for="bt_bundle">Bolsa / Paquete</label>
              </div>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="st_iva" bind:checked={$form.applies_iva}>
                <label class="form-check-label" for="st_iva">Aplica IVA</label>
              </div>
            </div>
            {#if $form.applies_iva}
              <div class="col-6">
                <div class="input-group input-group-sm">
                  <input type="number" class="form-control" bind:value={$form.iva_percentage}
                    min="0" max="100" step="0.01">
                  <span class="input-group-text">%</span>
                </div>
              </div>
            {/if}
          </div>

          <div class="mb-3">
            <label class="form-label" for="st_desc">Descripción</label>
            <textarea id="st_desc" class="form-control" rows="2" bind:value={$form.description}></textarea>
          </div>

          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="st_active" bind:checked={$form.is_active}>
            <label class="form-check-label" for="st_active">Activo</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" disabled={$form.processing}>
            {#if $form.processing}
              <span class="spinner-border spinner-border-sm me-1"></span>
            {/if}
            {modalMode === 'create' ? 'Crear' : 'Guardar'}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
