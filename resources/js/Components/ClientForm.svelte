<script>
  /**
   * Formulario compartido para Crear y Editar cliente.
   */
  let { form, onsubmit } = $props();

  const TAX_RESPONSIBILITIES = [
    { code: 'O-13',    label: 'O-13 — Gran contribuyente' },
    { code: 'O-15',    label: 'O-15 — Autorretenedor' },
    { code: 'O-23',    label: 'O-23 — Agente de retención IVA' },
    { code: 'O-47',    label: 'O-47 — Régimen simple de tributación' },
    { code: 'R-99-PN', label: 'R-99-PN — No aplica (PN no responsable)' },
  ];

  const DEPARTMENTS = [
    'Amazonas','Antioquia','Arauca','Atlántico','Bolívar','Boyacá','Caldas',
    'Caquetá','Casanare','Cauca','Cesar','Chocó','Córdoba','Cundinamarca',
    'Guainía','Guaviare','Huila','La Guajira','Magdalena','Meta','Nariño',
    'Norte de Santander','Putumayo','Quindío','Risaralda','San Andrés',
    'Santander','Sucre','Tolima','Valle del Cauca','Vaupés','Vichada',
  ];

  function toggleResponsibility(code) {
    const current = $form.tax_responsibilities ?? [];
    if (current.includes(code)) {
      $form.tax_responsibilities = current.filter(c => c !== code);
    } else {
      $form.tax_responsibilities = [...current, code];
    }
  }
</script>

<form {onsubmit}>
  <div class="row g-3">

    <!-- Tipo de persona -->
    <div class="col-12">
      <label class="form-label">Tipo de persona <span class="text-danger">*</span></label>
      <div class="d-flex gap-3">
        <div class="form-check">
          <input class="form-check-input" type="radio" id="juridica" value="juridica"
            bind:group={$form.type}>
          <label class="form-check-label" for="juridica">Jurídica (empresa)</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" id="natural" value="natural"
            bind:group={$form.type}>
          <label class="form-check-label" for="natural">Natural (persona)</label>
        </div>
      </div>
    </div>

    <!-- Razón social -->
    <div class="col-md-6">
      <label class="form-label" for="business_name">
        {$form.type === 'natural' ? 'Nombre completo' : 'Razón social'} <span class="text-danger">*</span>
      </label>
      <input id="business_name" type="text" class="form-control {$form.errors.business_name ? 'is-invalid' : ''}"
        bind:value={$form.business_name} required>
      {#if $form.errors.business_name}
        <div class="invalid-feedback">{$form.errors.business_name}</div>
      {/if}
    </div>

    <!-- Nombre comercial -->
    <div class="col-md-6">
      <label class="form-label" for="trade_name">Nombre comercial</label>
      <input id="trade_name" type="text" class="form-control {$form.errors.trade_name ? 'is-invalid' : ''}"
        bind:value={$form.trade_name}>
    </div>

    <!-- Documento -->
    <div class="col-md-4">
      <label class="form-label" for="document_number">
        {$form.type === 'natural' ? 'Cédula' : 'NIT'} <span class="text-danger">*</span>
      </label>
      <input id="document_number" type="text" class="form-control {$form.errors.document_number ? 'is-invalid' : ''}"
        bind:value={$form.document_number} required>
      {#if $form.errors.document_number}
        <div class="invalid-feedback">{$form.errors.document_number}</div>
      {/if}
    </div>

    <!-- DV (solo jurídica) -->
    {#if $form.type === 'juridica'}
      <div class="col-md-2">
        <label class="form-label" for="dv">DV</label>
        <input id="dv" type="text" maxlength="1" class="form-control {$form.errors.dv ? 'is-invalid' : ''}"
          bind:value={$form.dv} placeholder="0">
      </div>
    {/if}

    <!-- Régimen tributario -->
    <div class="col-md-3">
      <label class="form-label" for="tax_regime">Régimen tributario <span class="text-danger">*</span></label>
      <select id="tax_regime" class="form-select {$form.errors.tax_regime ? 'is-invalid' : ''}"
        bind:value={$form.tax_regime}>
        <option value="ordinario">Ordinario</option>
        <option value="simple">Simple</option>
      </select>
    </div>

    <!-- CIIU -->
    <div class="col-md-3">
      <label class="form-label" for="ciiu_code">Código CIIU</label>
      <input id="ciiu_code" type="text" class="form-control" bind:value={$form.ciiu_code} placeholder="Ej: 6201">
    </div>

    <!-- Responsabilidades fiscales -->
    <div class="col-12">
      <label class="form-label">Responsabilidades fiscales DIAN</label>
      <div class="d-flex flex-wrap gap-2">
        {#each TAX_RESPONSIBILITIES as resp}
          {@const checked = ($form.tax_responsibilities ?? []).includes(resp.code)}
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="resp_{resp.code}"
              {checked} onchange={() => toggleResponsibility(resp.code)}>
            <label class="form-check-label small" for="resp_{resp.code}">{resp.label}</label>
          </div>
        {/each}
      </div>
    </div>

    <div class="col-12"><hr class="my-0"></div>

    <!-- Email -->
    <div class="col-md-4">
      <label class="form-label" for="email">Email</label>
      <input id="email" type="email" class="form-control {$form.errors.email ? 'is-invalid' : ''}"
        bind:value={$form.email}>
      {#if $form.errors.email}
        <div class="invalid-feedback">{$form.errors.email}</div>
      {/if}
    </div>

    <!-- Email facturación -->
    <div class="col-md-4">
      <label class="form-label" for="email_billing">Email facturación</label>
      <input id="email_billing" type="email" class="form-control {$form.errors.email_billing ? 'is-invalid' : ''}"
        bind:value={$form.email_billing}>
    </div>

    <!-- Teléfono -->
    <div class="col-md-2">
      <label class="form-label" for="phone">Teléfono</label>
      <input id="phone" type="text" class="form-control" bind:value={$form.phone}>
    </div>

    <!-- Móvil -->
    <div class="col-md-2">
      <label class="form-label" for="mobile">Móvil</label>
      <input id="mobile" type="text" class="form-control" bind:value={$form.mobile}>
    </div>

    <!-- Dirección -->
    <div class="col-12">
      <label class="form-label" for="address">Dirección</label>
      <input id="address" type="text" class="form-control" bind:value={$form.address}>
    </div>

    <!-- Ciudad -->
    <div class="col-md-4">
      <label class="form-label" for="city">Ciudad</label>
      <input id="city" type="text" class="form-control" bind:value={$form.city}>
    </div>

    <!-- Departamento -->
    <div class="col-md-4">
      <label class="form-label" for="department">Departamento</label>
      <select id="department" class="form-select" bind:value={$form.department}>
        <option value="">Seleccionar...</option>
        {#each DEPARTMENTS as dep}
          <option value={dep}>{dep}</option>
        {/each}
      </select>
    </div>

    <!-- Código postal -->
    <div class="col-md-2">
      <label class="form-label" for="postal_code">Código postal</label>
      <input id="postal_code" type="text" class="form-control" bind:value={$form.postal_code}>
    </div>

    <!-- País -->
    <div class="col-md-2">
      <label class="form-label" for="country">País</label>
      <input id="country" type="text" maxlength="2" class="form-control" bind:value={$form.country}>
    </div>

    <!-- Estado activo -->
    <div class="col-md-3">
      <div class="form-check form-switch mt-4">
        <input class="form-check-input" type="checkbox" id="is_active" bind:checked={$form.is_active}>
        <label class="form-check-label" for="is_active">Cliente activo</label>
      </div>
    </div>

    <!-- Notas -->
    <div class="col-12">
      <label class="form-label" for="notes">Notas internas</label>
      <textarea id="notes" class="form-control" rows="3" bind:value={$form.notes}></textarea>
    </div>

    <!-- Botones -->
    <div class="col-12 d-flex gap-2 justify-content-end">
      <a href="/clients" class="btn btn-light">Cancelar</a>
      <button type="submit" class="btn btn-primary" disabled={$form.processing}>
        {#if $form.processing}
          <span class="spinner-border spinner-border-sm me-2"></span>Guardando...
        {:else}
          <i class="ti ti-device-floppy me-1"></i>Guardar
        {/if}
      </button>
    </div>

  </div>
</form>
