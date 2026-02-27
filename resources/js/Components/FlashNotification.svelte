<script>
  import { usePage } from '@inertiajs/svelte';
  import { onMount } from 'svelte';

  const page = usePage();

  $effect(() => {
    const flash = $page.props.flash;
    if (!flash) return;

    // Pequeño delay para que SweetAlert2 esté cargado en el DOM
    setTimeout(() => {
      if (typeof Swal === 'undefined') return;

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: flash.success ? 'success' : 'error',
        title: flash.message,
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        customClass: {
          popup: flash.success ? 'swal-toast-success' : 'swal-toast-error',
        },
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer);
          toast.addEventListener('mouseleave', Swal.resumeTimer);
        },
      });
    }, 100);
  });
</script>
