<script>
  import FlashNotification from '../Components/FlashNotification.svelte';

  let { children } = $props();

  const year = new Date().getFullYear();
</script>

<div class="spec-auth-bg min-vh-100 d-flex align-items-center justify-content-center py-4">
  <div class="w-100 px-3" style="max-width: 460px;">

    <!-- Logo encima de la card -->
    <div class="text-center mb-4">
      <a href="/">
        <img
          src="/assets/images/logo_spec_app-removebg.png"
          alt="SPEC"
          style="height:150px; object-fit:contain;"
        >
      </a>
    </div>

    <!-- Card del formulario -->
    {@render children()}

    <!-- Footer -->
    <div class="text-center mt-3">
      <p class="m-0 text-muted small">© {year} SPEC — Todos los derechos reservados</p>
    </div>

  </div>
</div>

<FlashNotification />
