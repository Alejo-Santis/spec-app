<script>
  /**
   * Botón que pide confirmación con SweetAlert2 antes de ejecutar la acción.
   * Uso: <ConfirmDelete action="/clients/1" label="Eliminar" />
   */
  import { router } from '@inertiajs/svelte';

  let {
    action,
    label = 'Eliminar',
    title = '¿Está seguro?',
    text = 'Esta acción no se puede deshacer.',
    class: cls = 'btn btn-sm btn-light-danger',
    method = 'delete',
  } = $props();

  function confirm() {
    if (typeof Swal === 'undefined') {
      router.visit(action, { method });
      return;
    }

    Swal.fire({
      title,
      text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
    }).then((result) => {
      if (result.isConfirmed) {
        router.visit(action, { method, preserveScroll: true });
      }
    });
  }
</script>

<button type="button" class={cls} onclick={confirm}>
  <i class="ti ti-trash me-1"></i>{label}
</button>
