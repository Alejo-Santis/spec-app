<script>
  import { router, page } from '@inertiajs/svelte';
  import { onMount } from 'svelte';

  let { open = $bindable(false) } = $props();

  const perms = $derived(new Set($page.props.auth?.user?.permissions ?? []));

  let query       = $state('');
  let results     = $state([]);
  let loading     = $state(false);
  let activeIndex = $state(-1);
  let inputEl;

  let debounceTimer;

  // Agrupa los resultados planos por su campo `group`, preservando el índice flat
  const groupedResults = $derived(
    results.reduce((acc, result, i) => {
      const existing = acc.find(g => g.group === result.group);
      if (existing) {
        existing.items.push({ ...result, flatIndex: i });
      } else {
        acc.push({ group: result.group, items: [{ ...result, flatIndex: i }] });
      }
      return acc;
    }, [])
  );

  function close() {
    open        = false;
    query       = '';
    results     = [];
    activeIndex = -1;
  }

  function handleKeydown(e) {
    if (!open) return;

    if (e.key === 'Escape') {
      close();
    } else if (e.key === 'ArrowDown') {
      e.preventDefault();
      activeIndex = Math.min(activeIndex + 1, results.length - 1);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      activeIndex = Math.max(activeIndex - 1, -1);
    } else if (e.key === 'Enter' && activeIndex >= 0) {
      e.preventDefault();
      navigateTo(results[activeIndex].url);
    }
  }

  function navigateTo(url) {
    close();
    router.visit(url);
  }

  $effect(() => {
    if (open) {
      setTimeout(() => inputEl?.focus(), 50);
    }
  });

  $effect(() => {
    if (!query.trim() || query.trim().length < 2) {
      results     = [];
      activeIndex = -1;
      return;
    }

    clearTimeout(debounceTimer);
    loading = true;

    debounceTimer = setTimeout(async () => {
      try {
        const res = await fetch(`/search?q=${encodeURIComponent(query.trim())}`, {
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        results     = data.results ?? [];
        activeIndex = -1;
      } catch {
        results = [];
      } finally {
        loading = false;
      }
    }, 220);
  });

  onMount(() => {
    document.addEventListener('keydown', handleKeydown);
    return () => document.removeEventListener('keydown', handleKeydown);
  });
</script>

{#if open}
  <!-- Backdrop -->
  <button
    type="button"
    class="search-modal-backdrop"
    onclick={close}
    aria-label="Cerrar búsqueda"
    style="border:none; width:100%;"
  >
    <!-- svelte-ignore a11y_click_events_have_key_events a11y_no_static_element_interactions -->
    <div class="search-modal-box" onclick={(e) => e.stopPropagation()} role="dialog" aria-modal="true" aria-label="Búsqueda global">

      <!-- Input -->
      <div class="d-flex align-items-center px-3" style="border-bottom:1px solid #eee;">
        <i class="ti ti-search text-muted me-2" style="font-size:1.1rem;"></i>
        <input
          bind:this={inputEl}
          bind:value={query}
          type="text"
          class="search-modal-input"
          placeholder="Buscar clientes, usuarios, actividad..."
          autocomplete="off"
          style="border:none; flex:1;"
        >
        {#if loading}
          <div class="spinner-border spinner-border-sm text-muted ms-2" role="status"></div>
        {/if}
        <kbd class="ms-2 text-muted" style="font-size:0.7rem; background:#f3f4f6; border:1px solid #ddd; border-radius:4px; padding:2px 6px;">Esc</kbd>
      </div>

      <!-- Results -->
      <div style="max-height:400px; overflow-y:auto;">
        {#if results.length > 0}
          {#each groupedResults as group}
            <div class="py-1">
              <!-- Encabezado de grupo -->
              <div class="px-3 pt-2 pb-1">
                <small class="text-muted fw-medium text-uppercase" style="font-size:0.68rem; letter-spacing:.06em;">
                  {group.group}
                </small>
              </div>
              {#each group.items as result}
                <button
                  type="button"
                  class="search-result-item w-100 text-start {activeIndex === result.flatIndex ? 'bg-light-primary' : ''}"
                  onclick={() => navigateTo(result.url)}
                  onmouseenter={() => activeIndex = result.flatIndex}
                >
                  <div class="avtar avtar-xs bg-light-primary rounded-2 flex-shrink-0">
                    <i class="{result.icon} text-primary" style="font-size:0.9rem;"></i>
                  </div>
                  <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-medium text-truncate" style="font-size:0.875rem;">{result.label}</div>
                    <small class="text-muted text-truncate d-block">{result.sublabel}</small>
                  </div>
                  <span class="badge {result.badgeClass} flex-shrink-0">{result.badge}</span>
                  <i class="ti ti-arrow-right text-muted" style="font-size:0.8rem;"></i>
                </button>
              {/each}
            </div>
          {/each}
        {:else if query.trim().length >= 2 && !loading}
          <div class="search-no-results">
            <i class="ti ti-search-off d-block mb-2" style="font-size:2rem; opacity:.3;"></i>
            Sin resultados para "<strong>{query}</strong>"
          </div>
        {:else if query.trim().length < 2 && query.trim().length > 0}
          <div class="search-no-results text-muted">Escribe al menos 2 caracteres...</div>
        {:else}
          <!-- Accesos rápidos filtrados por permisos -->
          <div class="px-3 py-3">
            <p class="text-muted small mb-2 fw-medium">Accesos rápidos</p>
            <div class="d-flex flex-wrap gap-2">
              {#if perms.has('clients.view')}
                <a href="/clients" onclick={close} class="badge bg-light-primary text-primary text-decoration-none px-3 py-2">
                  <i class="ti ti-users me-1"></i>Clientes
                </a>
              {/if}
              {#if perms.has('client-prices.view')}
                <a href="/client-prices" onclick={close} class="badge bg-light-success text-success text-decoration-none px-3 py-2">
                  <i class="ti ti-currency-dollar me-1"></i>Precios
                </a>
              {/if}
              {#if perms.has('client-bundles.view')}
                <a href="/client-bundles" onclick={close} class="badge bg-light-info text-info text-decoration-none px-3 py-2">
                  <i class="ti ti-package me-1"></i>Bolsas
                </a>
              {/if}
              {#if perms.has('price-lists.view')}
                <a href="/price-lists" onclick={close} class="badge bg-light-warning text-warning text-decoration-none px-3 py-2">
                  <i class="ti ti-list-check me-1"></i>Listas de precios
                </a>
              {/if}
              {#if perms.has('users.manage')}
                <a href="/users" onclick={close} class="badge bg-light-secondary text-secondary text-decoration-none px-3 py-2">
                  <i class="ti ti-users-group me-1"></i>Usuarios
                </a>
              {/if}
              {#if perms.has('activity-logs.view')}
                <a href="/activity-logs" onclick={close} class="badge bg-light-danger text-danger text-decoration-none px-3 py-2">
                  <i class="ti ti-tool me-1"></i>Log de actividad
                </a>
              {/if}
            </div>
          </div>
        {/if}
      </div>

      <!-- Footer hint -->
      <div class="d-flex align-items-center gap-3 px-3 py-2 border-top" style="font-size:0.72rem; color:#aaa;">
        <span><kbd style="background:#f3f4f6;border:1px solid #ddd;border-radius:3px;padding:1px 5px;color:#333;">↑↓</kbd> Navegar</span>
        <span><kbd style="background:#f3f4f6;border:1px solid #ddd;border-radius:3px;padding:1px 5px;color:#333;">↵</kbd> Ir</span>
        <span><kbd style="background:#f3f4f6;border:1px solid #ddd;border-radius:3px;padding:1px 5px;color:#333;">Esc</kbd> Cerrar</span>
      </div>

    </div>
  </button>
{/if}
