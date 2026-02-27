<script>
  import { router } from '@inertiajs/svelte';

  let { links = [], meta = null } = $props();

  function goTo(url) {
    if (!url) return;
    router.visit(url, { preserveScroll: true, preserveState: true });
  }
</script>

{#if links.length > 3}
  <nav aria-label="Paginación">
    <ul class="pagination pagination-sm justify-content-end mb-0">
      {#each links as link}
        <li class="page-item {link.active ? 'active' : ''} {!link.url ? 'disabled' : ''}">
          <button
            class="page-link"
            onclick={() => goTo(link.url)}
            disabled={!link.url}
          >
            {@html link.label}
          </button>
        </li>
      {/each}
    </ul>
  </nav>
{/if}
