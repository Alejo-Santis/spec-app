<script>
  import { useForm } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';

  let { user } = $props();

  const roleLabels = { admin: 'Administrador', operator: 'Operador', viewer: 'Solo lectura' };
  const roleColors = { admin: 'danger', operator: 'primary', viewer: 'secondary' };
  const roleName   = user.roles[0] ?? '';

  // Formulario: datos personales
  const infoForm = useForm({
    name:  user.name,
    email: user.email,
  });

  function submitInfo(e) {
    e.preventDefault();
    $infoForm.put('/profile/info', { preserveScroll: true });
  }

  // Formulario: contraseña
  const pwForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
  });

  function submitPassword(e) {
    e.preventDefault();
    $pwForm.put('/profile/password', {
      preserveScroll: true,
      onSuccess: () => $pwForm.reset(),
    });
  }

  // Fortaleza de la contraseña nueva
  const pwStrength = $derived(() => {
    const pw = $pwForm.password;
    if (!pw) return null;
    let score = 0;
    if (pw.length >= 8)  score++;
    if (pw.length >= 12) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    if (score <= 1) return { label: 'Débil',   color: 'danger',  pct: 25  };
    if (score <= 2) return { label: 'Regular',  color: 'warning', pct: 50  };
    if (score <= 3) return { label: 'Buena',    color: 'info',    pct: 75  };
    return               { label: 'Fuerte',   color: 'success', pct: 100 };
  });
</script>

<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-header-title"><h5 class="m-0">Mi perfil</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Perfil</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Avatar / info general -->
    <div class="col-md-3">
      <div class="card border-0 shadow-sm text-center">
        <div class="card-body py-4">
          <div class="avtar avtar-xl bg-primary text-white rounded-circle fw-bold mx-auto mb-3"
            style="font-size:2rem; width:80px; height:80px; display:flex; align-items:center; justify-content:center;">
            {user.name[0].toUpperCase()}
          </div>
          <h5 class="mb-1 fw-semibold">{user.name}</h5>
          <div class="text-muted small mb-2">{user.email}</div>
          {#if roleName}
            <span class="badge bg-light-{roleColors[roleName] ?? 'secondary'} text-{roleColors[roleName] ?? 'secondary'}">
              {roleLabels[roleName] ?? roleName}
            </span>
          {/if}
        </div>
      </div>
    </div>

    <!-- Formularios -->
    <div class="col-md-9">

      <!-- Datos personales -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-bottom">
          <h5 class="mb-0 fw-semibold">
            <i class="ti ti-user me-2 text-primary"></i>Datos personales
          </h5>
        </div>
        <div class="card-body">
          <form onsubmit={submitInfo}>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-medium" for="p_name">Nombre completo</label>
                <input id="p_name" type="text"
                  class="form-control {$infoForm.errors.name ? 'is-invalid' : ''}"
                  bind:value={$infoForm.name} required>
                {#if $infoForm.errors.name}
                  <div class="invalid-feedback">{$infoForm.errors.name}</div>
                {/if}
              </div>
              <div class="col-md-6">
                <label class="form-label fw-medium" for="p_email">Email</label>
                <input id="p_email" type="email"
                  class="form-control {$infoForm.errors.email ? 'is-invalid' : ''}"
                  bind:value={$infoForm.email} required>
                {#if $infoForm.errors.email}
                  <div class="invalid-feedback">{$infoForm.errors.email}</div>
                {/if}
              </div>
            </div>
            <div class="mt-3 d-flex justify-content-end">
              <button type="submit" class="btn btn-primary" disabled={$infoForm.processing}>
                {#if $infoForm.processing}
                  <span class="spinner-border spinner-border-sm me-1"></span>Guardando...
                {:else}
                  <i class="ti ti-device-floppy me-1"></i>Guardar cambios
                {/if}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Cambiar contraseña -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom">
          <h5 class="mb-0 fw-semibold">
            <i class="ti ti-lock me-2 text-warning"></i>Cambiar contraseña
          </h5>
        </div>
        <div class="card-body">
          <form onsubmit={submitPassword}>
            <div class="mb-3">
              <label class="form-label fw-medium" for="p_cur_pw">Contraseña actual</label>
              <input id="p_cur_pw" type="password"
                class="form-control {$pwForm.errors.current_password ? 'is-invalid' : ''}"
                bind:value={$pwForm.current_password} required autocomplete="current-password">
              {#if $pwForm.errors.current_password}
                <div class="invalid-feedback">{$pwForm.errors.current_password}</div>
              {/if}
            </div>

            <div class="mb-3">
              <label class="form-label fw-medium" for="p_new_pw">Nueva contraseña</label>
              <input id="p_new_pw" type="password"
                class="form-control {$pwForm.errors.password ? 'is-invalid' : ''}"
                bind:value={$pwForm.password} required minlength="8" autocomplete="new-password">
              {#if $pwForm.errors.password}
                <div class="invalid-feedback">{$pwForm.errors.password}</div>
              {/if}
              {#if pwStrength()}
                {@const s = pwStrength()}
                <div class="mt-2">
                  <div class="progress" style="height:4px;">
                    <div class="progress-bar bg-{s.color}" style="width:{s.pct}%;"></div>
                  </div>
                  <small class="text-{s.color} mt-1 d-block">Fortaleza: {s.label}</small>
                </div>
              {/if}
            </div>

            <div class="mb-3">
              <label class="form-label fw-medium" for="p_conf_pw">Confirmar nueva contraseña</label>
              <input id="p_conf_pw" type="password"
                class="form-control {$pwForm.errors.password_confirmation ? 'is-invalid' : ''}"
                bind:value={$pwForm.password_confirmation} required autocomplete="new-password">
              {#if $pwForm.errors.password_confirmation}
                <div class="invalid-feedback">{$pwForm.errors.password_confirmation}</div>
              {/if}
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-warning" disabled={$pwForm.processing}>
                {#if $pwForm.processing}
                  <span class="spinner-border spinner-border-sm me-1"></span>Actualizando...
                {:else}
                  <i class="ti ti-lock me-1"></i>Actualizar contraseña
                {/if}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</AppLayout>
