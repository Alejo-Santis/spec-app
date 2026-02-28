<script>
  import { useForm, Link, page } from '@inertiajs/svelte';

  const perms = $derived(new Set($page.props.auth?.user?.permissions ?? []));
  import AppLayout from '../../Layouts/AppLayout.svelte';

  let { priceLists } = $props();

  function activateList(id) {
    if (typeof Swal === 'undefined') return;
    Swal.fire({
      title: '¿Activar esta lista?',
      text: 'La lista activa actual quedará desactivada.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Sí, activar',
      cancelButtonText: 'Cancelar',
    }).then((result) => {
      if (result.isConfirmed) {
        // Usar form de Inertia para el POST
        const f = useForm({});
        f.post(`/price-lists/${id}/activate`);
      }
    });
  }
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col-md-12">
          <div class="page-header-title"><h5 class="m-0">Listas de Precios</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Listas de Precios</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center" id="tour-pl-header">
      <h5 class="mb-0"><i class="ti ti-list me-2"></i>Listas de Precios</h5>
      {#if perms.has('price-lists.create')}
        <Link href="/price-lists/create" class="btn btn-primary btn-sm">
          <i class="ti ti-plus me-1"></i>Nueva lista
        </Link>
      {/if}
    </div>
    <div class="card-body p-0">
      <div class="table-responsive" id="tour-pl-table">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Año</th>
              <th>Nombre</th>
              <th class="text-end">Ajuste %</th>
              <th class="text-end">Precios asignados</th>
              <th>Estado</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            {#each priceLists as pl}
              <tr class="{pl.is_active ? 'table-light bg-opacity-10' : ''}">
                <td class="fw-bold fs-5">{pl.year}</td>
                <td>
                  {pl.name}
                  {#if pl.is_active}
                    <span class="badge bg-success ms-2">ACTIVA</span>
                  {/if}
                </td>
                <td class="text-end">
                  <span class="badge bg-light-info text-info">{pl.adjustment_percentage}%</span>
                </td>
                <td class="text-end">
                  <span class="badge bg-light-secondary text-secondary">{pl.client_prices_count ?? 0}</span>
                </td>
                <td>
                  {#if pl.is_active}
                    <span class="badge bg-success">Activa</span>
                  {:else}
                    <span class="badge bg-light-secondary text-secondary">Inactiva</span>
                  {/if}
                </td>
                <td class="text-end">
                  <div class="d-flex gap-1 justify-content-end">
                    <Link href="/price-lists/{pl.id}" class="btn btn-sm btn-light-info" title="Ver detalle">
                      <i class="ti ti-eye"></i>
                    </Link>
                    {#if !pl.is_active && perms.has('price-lists.activate')}
                      <button class="btn btn-sm btn-light-success" onclick={() => activateList(pl.id)} title="Activar">
                        <i class="ti ti-check"></i>
                      </button>
                    {/if}
                  </div>
                </td>
              </tr>
            {:else}
              <tr>
                <td colspan="6" class="text-center text-muted py-4">No hay listas de precios.</td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</AppLayout>
