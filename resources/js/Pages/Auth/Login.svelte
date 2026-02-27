<script>
  import { useForm } from '@inertiajs/svelte';
  import AuthLayout from '../../Layouts/AuthLayout.svelte';

  const form = useForm({
    email: '',
    password: '',
    remember: false,
  });

  function submit(e) {
    e.preventDefault();
    $form.post('/login');
  }
</script>

<AuthLayout>
  <div class="card shadow-sm">
    <div class="card-body p-4 p-sm-5">

      <div class="mb-4">
        <h3 class="mb-1 fw-bold">Bienvenido</h3>
        <p class="text-muted mb-0">Ingresa tus credenciales para continuar</p>
      </div>

      {#if $form.errors.email && !$form.errors.password}
        <div class="alert alert-danger py-2 d-flex align-items-center gap-2">
          <i class="ti ti-alert-circle fs-5"></i>
          <span>{$form.errors.email}</span>
        </div>
      {/if}

      <form onsubmit={submit}>
        <!-- Email -->
        <div class="form-group mb-3">
          <label class="form-label fw-medium" for="email">Correo electrónico</label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-mail"></i></span>
            <input
              id="email"
              type="email"
              class="form-control {$form.errors.email ? 'is-invalid' : ''}"
              placeholder="correo@empresa.co"
              bind:value={$form.email}
              autocomplete="email"
              required
            >
            {#if $form.errors.email}
              <div class="invalid-feedback">{$form.errors.email}</div>
            {/if}
          </div>
        </div>

        <!-- Password -->
        <div class="form-group mb-3">
          <label class="form-label fw-medium" for="password">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text"><i class="ti ti-lock"></i></span>
            <input
              id="password"
              type="password"
              class="form-control {$form.errors.password ? 'is-invalid' : ''}"
              placeholder="••••••••"
              bind:value={$form.password}
              autocomplete="current-password"
              required
            >
            {#if $form.errors.password}
              <div class="invalid-feedback">{$form.errors.password}</div>
            {/if}
          </div>
        </div>

        <!-- Remember me -->
        <div class="d-flex mt-1 justify-content-between align-items-center mb-4">
          <div class="form-check">
            <input
              class="form-check-input input-primary"
              type="checkbox"
              id="remember"
              bind:checked={$form.remember}
            >
            <label class="form-check-label text-muted" for="remember">
              Mantenerme conectado
            </label>
          </div>
        </div>

        <!-- Submit -->
        <div class="d-grid">
          <button
            type="submit"
            class="btn btn-primary btn-lg"
            disabled={$form.processing}
          >
            {#if $form.processing}
              <span class="spinner-border spinner-border-sm me-2" role="status"></span>
              Verificando...
            {:else}
              <i class="ti ti-login me-2"></i>Iniciar sesión
            {/if}
          </button>
        </div>
      </form>

      <!-- Info -->
      <div class="mt-4 pt-3 border-top text-center">
        <small class="text-muted">
          <i class="ti ti-shield-lock me-1"></i>
          Acceso restringido al personal autorizado
        </small>
      </div>

    </div>
  </div>
</AuthLayout>
