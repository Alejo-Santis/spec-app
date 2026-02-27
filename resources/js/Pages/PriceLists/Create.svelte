<script>
  import { useForm } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';

  let { previousLists } = $props();

  const form = useForm({
    year:                  new Date().getFullYear() + 1,
    name:                  '',
    adjustment_percentage: 0,
    notes:                 '',
  });

  // Sugiere nombre automáticamente al cambiar el año
  $effect(() => {
    if (!$form.name || $form.name.startsWith('Lista de Precios')) {
      $form.name = `Lista de Precios ${$form.year}`;
    }
  });

  function submit(e) {
    e.preventDefault();
    $form.post('/price-lists');
  }
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col-md-12">
          <div class="page-header-title"><h5 class="m-0">Nueva lista de precios</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/price-lists">Listas de Precios</a></li>
            <li class="breadcrumb-item active">Nueva</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="ti ti-list me-2"></i>Nueva lista de precios</h5>
        </div>
        <div class="card-body">
          <form onsubmit={submit}>
            <div class="row g-3">

              <div class="col-md-4">
                <label class="form-label" for="year">Año <span class="text-danger">*</span></label>
                <input id="year" type="number" class="form-control {$form.errors.year ? 'is-invalid' : ''}"
                  bind:value={$form.year} min="2000" max="2100" required>
                {#if $form.errors.year}
                  <div class="invalid-feedback">{$form.errors.year}</div>
                {/if}
              </div>

              <div class="col-md-8">
                <label class="form-label" for="name">Nombre <span class="text-danger">*</span></label>
                <input id="name" type="text" class="form-control {$form.errors.name ? 'is-invalid' : ''}"
                  bind:value={$form.name} required>
                {#if $form.errors.name}
                  <div class="invalid-feedback">{$form.errors.name}</div>
                {/if}
              </div>

              <div class="col-md-5">
                <label class="form-label" for="adj">Porcentaje de ajuste <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input id="adj" type="number" step="0.01" min="0" max="100"
                    class="form-control {$form.errors.adjustment_percentage ? 'is-invalid' : ''}"
                    bind:value={$form.adjustment_percentage}>
                  <span class="input-group-text">%</span>
                  {#if $form.errors.adjustment_percentage}
                    <div class="invalid-feedback">{$form.errors.adjustment_percentage}</div>
                  {/if}
                </div>
                <div class="form-text">Se aplicará al generar precios desde la lista anterior.</div>
              </div>

              <div class="col-12">
                <label class="form-label" for="notes">Notas</label>
                <textarea id="notes" class="form-control" rows="2" bind:value={$form.notes}></textarea>
              </div>

              {#if previousLists.length > 0}
                <div class="col-12">
                  <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
                    <i class="ti ti-info-circle fs-5"></i>
                    <span>
                      Después de crear la lista, podrás
                      <strong>generar los precios desde la lista anterior</strong>
                      en el detalle de la lista.
                    </span>
                  </div>
                </div>
              {/if}

              <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="/price-lists" class="btn btn-light">Cancelar</a>
                <button type="submit" class="btn btn-primary" disabled={$form.processing}>
                  {#if $form.processing}
                    <span class="spinner-border spinner-border-sm me-1"></span>
                  {/if}
                  <i class="ti ti-device-floppy me-1"></i>Crear lista
                </button>
              </div>

            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</AppLayout>
