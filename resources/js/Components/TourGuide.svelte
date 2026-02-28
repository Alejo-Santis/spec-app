<script>
  import { page } from '@inertiajs/svelte';
  import { driver } from 'driver.js';
  import 'driver.js/dist/driver.css';
  import { getTourForUrl, STORAGE_PREFIX } from '../tours/tours.js';
  import { onMount } from 'svelte';

  // Parent binds this to get access to the startTour function
  let { startTour = $bindable(null) } = $props();

  let currentDriver = null;

  function wasCompleted(key) {
    return !!localStorage.getItem(STORAGE_PREFIX + key);
  }

  function markCompleted(key) {
    localStorage.setItem(STORAGE_PREFIX + key, '1');
  }

  function getCurrentTour() {
    return getTourForUrl($page.url);
  }

  function launchTour(force = false) {
    const tour = getCurrentTour();
    if (!tour) return;
    if (!force && wasCompleted(tour.key)) return;

    // Destroy any running tour before starting new
    if (currentDriver) {
      try { currentDriver.destroy(); } catch (_) {}
      currentDriver = null;
    }

    currentDriver = driver({
      showProgress: true,
      progressText: '{{current}} de {{total}}',
      nextBtnText: 'Siguiente →',
      prevBtnText: '← Anterior',
      doneBtnText: '¡Entendido!',
      smoothScroll: true,
      allowClose: true,
      steps: tour.steps,
      onDestroyed: () => {
        markCompleted(tour.key);
        currentDriver = null;
      },
      // Botón "No mostrar más" — se inyecta en el footer después de que el popover esté en el DOM
      onHighlighted: (_el, _step, opts) => {
        // Limpiar el botón si ya existe (por si se navega entre pasos)
        document.querySelector('.spec-skip-forever')?.remove();

        // Solo mostrarlo en el primer paso
        if (opts.state.activeIndex === 0) {
          const footer = document.querySelector('.driver-popover-footer');
          if (footer) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'spec-skip-forever';
            btn.textContent = 'No mostrar más';
            btn.onclick = () => {
              markCompleted(tour.key);
              currentDriver?.destroy();
            };
            footer.prepend(btn);
          }
        }
      },
    });

    currentDriver.drive();
  }

  // Expose function to parent via $bindable
  startTour = (force = true) => launchTour(force);

  // Track URL changes for Inertia SPA navigation (AppLayout doesn't remount)
  let lastUrl = $state($page.url);

  $effect(() => {
    const url = $page.url;
    if (url !== lastUrl) {
      lastUrl = url;
      // Small delay to let the new page render its DOM
      setTimeout(() => {
        const tour = getCurrentTour();
        if (tour && !wasCompleted(tour.key)) launchTour(false);
      }, 700);
    }
  });

  onMount(() => {
    // Auto-start on first visit to this page
    const tour = getCurrentTour();
    if (tour && !wasCompleted(tour.key)) {
      setTimeout(() => launchTour(false), 700);
    }
  });
</script>

<style>
  /* Ajustes visuales para que driver.js combine con Mantis Bootstrap 5 */
  :global(.driver-popover) {
    border-radius: 12px !important;
    font-family: inherit !important;
    box-shadow: 0 8px 32px rgba(0,0,0,.18) !important;
    max-width: 340px !important;
  }
  :global(.driver-popover-title) {
    font-size: 1rem !important;
    font-weight: 600 !important;
    color: #1a1a2e !important;
    margin-bottom: 6px !important;
  }
  :global(.driver-popover-description) {
    font-size: 0.875rem !important;
    color: #555 !important;
    line-height: 1.55 !important;
  }
  :global(.driver-popover-footer) {
    margin-top: 14px !important;
    gap: 6px !important;
    flex-wrap: wrap !important;
    align-items: center !important;
  }
  :global(.driver-popover-next-btn) {
    background: #4680ff !important;
    border: none !important;
    border-radius: 6px !important;
    font-size: 0.8rem !important;
    padding: 5px 14px !important;
  }
  :global(.driver-popover-next-btn:hover) {
    background: #3366dd !important;
  }
  :global(.driver-popover-prev-btn) {
    border-radius: 6px !important;
    font-size: 0.8rem !important;
    padding: 5px 14px !important;
    color: #555 !important;
    border-color: #ddd !important;
  }
  :global(.driver-popover-close-btn) {
    color: #999 !important;
    font-size: 1rem !important;
  }
  :global(.driver-popover-progress-text) {
    font-size: 0.75rem !important;
    color: #aaa !important;
  }
  :global(.driver-overlay) {
    background: rgba(0,0,0,.55) !important;
  }
  :global(.spec-skip-forever) {
    background: none !important;
    border: none !important;
    border-top: 1px solid #eee !important;
    color: #bbb !important;
    font-size: 0.72rem !important;
    cursor: pointer !important;
    padding: 8px 0 0 0 !important;
    text-decoration: none !important;
    width: 100% !important;
    text-align: center !important;
    order: 99 !important;
    margin-top: 4px !important;
    letter-spacing: 0.01em !important;
  }
  :global(.spec-skip-forever:hover) {
    color: #999 !important;
    text-decoration: underline !important;
  }
</style>
