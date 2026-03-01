<script>
  import { useForm, page, router } from '@inertiajs/svelte';
  import AppLayout from '../../Layouts/AppLayout.svelte';

  let { users, roles } = $props();

  const currentUserId = $derived($page.props.auth?.user?.id);

  // ── Modal crear usuario ──────────────────────────
  let createOpen = $state(false);
  const createForm = useForm({ name: '', email: '', password: '', role: 'operator' });

  function submitCreate(e) {
    e.preventDefault();
    $createForm.post('/users', {
      onSuccess: () => {
        createOpen = false;
        $createForm.reset();
      },
    });
  }

  // ── Cambiar rol ──────────────────────────────────
  let editingRole = $state({});
  const roleForm = useForm({ role: '' });

  function startEdit(user) {
    editingRole[user.id] = user.roles[0] ?? 'viewer';
  }

  function cancelEdit(userId) {
    delete editingRole[userId];
    editingRole = { ...editingRole };
  }

  function saveRole(user) {
    $roleForm.role = editingRole[user.id];
    $roleForm.put(`/users/${user.uuid}/role`, {
      onSuccess: () => {
        delete editingRole[user.id];
        editingRole = { ...editingRole };
      },
    });
  }

  // ── Toggle activo ────────────────────────────────
  function toggleActive(user) {
    const msg = user.is_active
      ? `¿Desactivar a ${user.name}? No podrá iniciar sesión.`
      : `¿Reactivar a ${user.name}?`;
    if (!confirm(msg)) return;
    router.patch(`/users/${user.uuid}/toggle-active`, {}, { preserveScroll: true });
  }

  // ── Eliminar ─────────────────────────────────────
  function deleteUser(user) {
    if (!confirm(`¿Eliminar permanentemente a ${user.name}? Esta acción no se puede deshacer.`)) return;
    router.delete(`/users/${user.uuid}`, { preserveScroll: true });
  }

  const roleColors = { admin: 'danger', operator: 'primary', viewer: 'secondary' };
  const roleLabels = { admin: 'Administrador', operator: 'Operador', viewer: 'Solo lectura' };
</script>




<svelte:head><title>Usuarios - SPEC</title></svelte:head>
<AppLayout>
  <div class="page-header">
    <div class="page-block">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-header-title"><h5 class="m-0">Gestión de Usuarios</h5></div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Usuarios</li>
          </ul>
        </div>
        <div class="col-auto">
          <button class="btn btn-primary btn-sm" onclick={() => createOpen = true}>
            <i class="ti ti-user-plus me-1"></i>Nuevo usuario
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Info de roles -->
  <div class="row mb-4">
    {#each [
      { color: 'danger',    icon: 'ti-shield-check', label: 'Administrador', desc: 'Acceso total. Puede gestionar usuarios, activar listas, eliminar datos.' },
      { color: 'primary',   icon: 'ti-user-check',   label: 'Operador',      desc: 'Puede ver y editar clientes, precios, bolsas y registrar consumos.' },
      { color: 'secondary', icon: 'ti-eye',          label: 'Solo lectura',  desc: 'Solo puede consultar información. No puede modificar nada.' },
    ] as r}
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex gap-3 align-items-start">
            <div class="avtar avtar-s bg-light-{r.color} flex-shrink-0">
              <i class="ti {r.icon} text-{r.color}"></i>
            </div>
            <div>
              <div class="fw-semibold text-{r.color}">{r.label}</div>
              <small class="text-muted">{r.desc}</small>
            </div>
          </div>
        </div>
      </div>
    {/each}
  </div>

  <!-- Tabla de usuarios -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-semibold">
        <i class="ti ti-users-group me-2 text-primary"></i>Usuarios del sistema
        <span class="badge bg-primary ms-1">{users.length}</span>
      </h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Usuario</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Registrado</th>
              <th class="text-end pe-3">Acciones</th>
            </tr>
          </thead>
          <tbody>
            {#each users as user}
              <tr class="{!user.is_active ? 'opacity-50' : ''}{user.id === currentUserId ? ' table-light' : ''}">
                <td class="ps-3">
                  <div class="d-flex align-items-center gap-2">
                    <div class="avtar avtar-s bg-{user.is_active ? 'primary' : 'secondary'} text-white rounded-circle fw-bold"
                      style="font-size:0.8rem; width:32px; height:32px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                      {user.name[0].toUpperCase()}
                    </div>
                    <div>
                      <div class="fw-medium">{user.name}</div>
                      {#if user.id === currentUserId}
                        <small class="badge bg-light-info text-info" style="font-size:0.65rem;">tú</small>
                      {/if}
                    </div>
                  </div>
                </td>
                <td><small class="text-muted">{user.email}</small></td>
                <td>
                  {#if editingRole[user.id] !== undefined}
                    <div class="d-flex gap-1 align-items-center">
                      <select class="form-select form-select-sm" style="width:auto;"
                        bind:value={editingRole[user.id]}>
                        {#each roles as role}
                          <option value={role}>{roleLabels[role] ?? role}</option>
                        {/each}
                      </select>
                      <button class="btn btn-sm btn-primary" onclick={() => saveRole(user)}
                        disabled={$roleForm.processing}>
                        {#if $roleForm.processing}
                          <span class="spinner-border spinner-border-sm"></span>
                        {:else}
                          <i class="ti ti-check"></i>
                        {/if}
                      </button>
                      <button class="btn btn-sm btn-light" onclick={() => cancelEdit(user.id)}>
                        <i class="ti ti-x"></i>
                      </button>
                    </div>
                  {:else}
                    <span class="badge bg-light-{roleColors[user.roles[0]] ?? 'secondary'} text-{roleColors[user.roles[0]] ?? 'secondary'}">
                      {roleLabels[user.roles[0]] ?? (user.roles[0] ?? 'sin rol')}
                    </span>
                  {/if}
                </td>
                <td>
                  {#if user.is_active}
                    <span class="badge bg-light-success text-success"><i class="ti ti-check me-1"></i>Activo</span>
                  {:else}
                    <span class="badge bg-light-danger text-danger"><i class="ti ti-ban me-1"></i>Inactivo</span>
                  {/if}
                </td>
                <td><small class="text-muted">{user.created_at}</small></td>
                <td class="text-end pe-3">
                  <div class="d-flex gap-1 justify-content-end">
                    {#if editingRole[user.id] === undefined}
                      <button class="btn btn-sm btn-light-primary"
                        onclick={() => startEdit(user)}
                        disabled={user.id === currentUserId && user.roles[0] === 'admin'}
                        title="Cambiar rol">
                        <i class="ti ti-exchange"></i>
                      </button>
                    {/if}
                    {#if user.id !== currentUserId}
                      <button
                        class="btn btn-sm {user.is_active ? 'btn-light-warning' : 'btn-light-success'}"
                        onclick={() => toggleActive(user)}
                        title={user.is_active ? 'Desactivar usuario' : 'Activar usuario'}>
                        <i class="ti {user.is_active ? 'ti-user-off' : 'ti-user-check'}"></i>
                      </button>
                      <button class="btn btn-sm btn-light-danger"
                        onclick={() => deleteUser(user)}
                        title="Eliminar usuario">
                        <i class="ti ti-trash"></i>
                      </button>
                    {/if}
                  </div>
                </td>
              </tr>
            {:else}
              <tr>
                <td colspan="6" class="text-center text-muted py-5">No hay usuarios registrados.</td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Tabla de permisos por rol -->
  <div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-transparent">
      <h5 class="mb-0 fw-semibold">
        <i class="ti ti-shield me-2 text-secondary"></i>Permisos por rol
      </h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Módulo / Acción</th>
              <th class="text-center">Admin</th>
              <th class="text-center">Operador</th>
              <th class="text-center">Solo lectura</th>
            </tr>
          </thead>
          <tbody>
            {#each [
              ['Clientes — Ver',                true,  true,  true  ],
              ['Clientes — Crear',              true,  true,  false ],
              ['Clientes — Editar',             true,  true,  false ],
              ['Clientes — Eliminar',           true,  false, false ],
              ['Tipos de servicio — Ver',       true,  true,  true  ],
              ['Tipos de servicio — Gestionar', true,  false, false ],
              ['Listas de precios — Ver',       true,  true,  true  ],
              ['Listas de precios — Crear',     true,  false, false ],
              ['Listas de precios — Activar',   true,  false, false ],
              ['Listas de precios — Generar',   true,  false, false ],
              ['Bundle tiers — Gestionar',      true,  false, false ],
              ['Precios cliente — Ver',         true,  true,  true  ],
              ['Precios cliente — Crear/Editar',true,  true,  false ],
              ['Precios cliente — Eliminar',    true,  false, false ],
              ['Bolsas — Ver',                  true,  true,  true  ],
              ['Bolsas — Crear/Editar',         true,  true,  false ],
              ['Bolsas — Registrar consumo',    true,  true,  false ],
              ['Log de actividades — Ver',      true,  true,  true  ],
              ['Importar / Exportar',           true,  true,  false ],
              ['Usuarios — Gestionar',          true,  false, false ],
            ] as [label, adm, ope, vie]}
              <tr>
                <td class="ps-3 text-muted small">{label}</td>
                <td class="text-center">
                  {#if adm}<i class="ti ti-check text-success"></i>{:else}<i class="ti ti-x text-danger opacity-25"></i>{/if}
                </td>
                <td class="text-center">
                  {#if ope}<i class="ti ti-check text-success"></i>{:else}<i class="ti ti-x text-danger opacity-25"></i>{/if}
                </td>
                <td class="text-center">
                  {#if vie}<i class="ti ti-check text-success"></i>{:else}<i class="ti ti-x text-danger opacity-25"></i>{/if}
                </td>
              </tr>
            {/each}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</AppLayout>

<!-- Modal: Crear usuario -->
{#if createOpen}
  <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.4);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <form onsubmit={submitCreate}>
          <div class="modal-header border-bottom">
            <h5 class="modal-title fw-semibold">
              <i class="ti ti-user-plus me-2 text-primary"></i>Nuevo usuario
            </h5>
            <button type="button" class="btn-close" onclick={() => { createOpen = false; $createForm.reset(); }}></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-medium" for="cu_name">Nombre completo <span class="text-danger">*</span></label>
              <input id="cu_name" type="text" class="form-control {$createForm.errors.name ? 'is-invalid' : ''}"
                bind:value={$createForm.name} required>
              {#if $createForm.errors.name}
                <div class="invalid-feedback">{$createForm.errors.name}</div>
              {/if}
            </div>

            <div class="mb-3">
              <label class="form-label fw-medium" for="cu_email">Email <span class="text-danger">*</span></label>
              <input id="cu_email" type="email" class="form-control {$createForm.errors.email ? 'is-invalid' : ''}"
                bind:value={$createForm.email} required>
              {#if $createForm.errors.email}
                <div class="invalid-feedback">{$createForm.errors.email}</div>
              {/if}
            </div>

            <div class="mb-3">
              <label class="form-label fw-medium" for="cu_pass">Contraseña <span class="text-danger">*</span></label>
              <input id="cu_pass" type="password" class="form-control {$createForm.errors.password ? 'is-invalid' : ''}"
                bind:value={$createForm.password} required minlength="8">
              {#if $createForm.errors.password}
                <div class="invalid-feedback">{$createForm.errors.password}</div>
              {:else}
                <div class="form-text">Mínimo 8 caracteres.</div>
              {/if}
            </div>

            <div class="mb-1">
              <label class="form-label fw-medium" for="cu_role">Rol <span class="text-danger">*</span></label>
              <select id="cu_role" class="form-select {$createForm.errors.role ? 'is-invalid' : ''}"
                bind:value={$createForm.role}>
                {#each roles as role}
                  <option value={role}>{roleLabels[role] ?? role}</option>
                {/each}
              </select>
              {#if $createForm.errors.role}
                <div class="invalid-feedback">{$createForm.errors.role}</div>
              {/if}
            </div>
          </div>
          <div class="modal-footer border-top">
            <button type="button" class="btn btn-light"
              onclick={() => { createOpen = false; $createForm.reset(); }}>
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary" disabled={$createForm.processing}>
              {#if $createForm.processing}
                <span class="spinner-border spinner-border-sm me-1"></span>Creando...
              {:else}
                <i class="ti ti-user-plus me-1"></i>Crear usuario
              {/if}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
{/if}
