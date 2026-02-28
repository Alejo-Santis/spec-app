<script>
  import { page, Link } from '@inertiajs/svelte';
  import FlashNotification from '../Components/FlashNotification.svelte';
  import SearchModal from '../Components/SearchModal.svelte';
  import { onMount } from 'svelte';

  let { children } = $props();

  const auth            = $derived($page.props.auth);
  const activePriceList = $derived($page.props.activePriceList);
  const currentUrl      = $derived($page.url);

  // Sidebar state
  let sidebarHidden = $state(false);
  let mobileOpen    = $state(false);
  let dropdownOpen  = $state(false);
  let searchOpen    = $state(false);

  function toggleSidebar()       { sidebarHidden = !sidebarHidden; }
  function toggleMobileSidebar() { mobileOpen    = !mobileOpen;    }
  function closeMobile()         { mobileOpen    = false;          }
  function toggleDropdown(e)     { e.stopPropagation(); dropdownOpen = !dropdownOpen; }
  function openSearch()          { searchOpen    = true;           }

  const navGroups = [
    {
      caption: 'Principal',
      items: [
        { label: 'Dashboard',         href: '/',               icon: 'ti ti-dashboard',       exact: true  },
        { label: 'Clientes',          href: '/clients',        icon: 'ti ti-users',           exact: false },
      ],
    },
    {
      caption: 'Precios y Servicios',
      items: [
        { label: 'Precios clientes',  href: '/client-prices',  icon: 'ti ti-currency-dollar', exact: false },
        { label: 'Listas de precios', href: '/price-lists',    icon: 'ti ti-list-check',      exact: false },
        { label: 'Tipos de servicio', href: '/service-types',  icon: 'ti ti-briefcase',       exact: false },
      ],
    },
    {
      caption: 'Bolsas',
      items: [
        { label: 'Bolsas / Paquetes', href: '/client-bundles', icon: 'ti ti-package', exact: false },
      ],
    },
    {
      caption: 'Sistema',
      items: [
        { label: 'Log de actividades', href: '/activity-logs', icon: 'ti ti-timeline', exact: false },
      ],
    },
  ];

  function isActive(href, exact) {
    if (exact) return currentUrl === href;
    return currentUrl === href || currentUrl.startsWith(href + '/') || currentUrl.startsWith(href + '?');
  }

  onMount(() => {
    if (typeof feather !== 'undefined') feather.replace();

    // Ctrl+K / Cmd+K → abrir modal de búsqueda
    function handleKeydown(e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        searchOpen = true;
      }
    }

    // Click fuera → cerrar dropdown
    function handleClickOutside() {
      dropdownOpen = false;
    }

    document.addEventListener('keydown', handleKeydown);
    document.addEventListener('click', handleClickOutside);

    return () => {
      document.removeEventListener('keydown', handleKeydown);
      document.removeEventListener('click', handleClickOutside);
    };
  });
</script>

<!-- Modal de búsqueda global -->
<SearchModal bind:open={searchOpen} />

<!-- Pre-loader -->
<div class="loader-bg">
  <div class="loader-track">
    <div class="loader-fill"></div>
  </div>
</div>

<!-- Overlay móvil -->
{#if mobileOpen}
  <button type="button" class="pc-menu-overlay" onclick={closeMobile} aria-label="Cerrar menú"
    style="position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:1049;border:none;padding:0;cursor:default;"></button>
{/if}

<!-- Sidebar -->
<nav class="pc-sidebar {sidebarHidden ? 'pc-sidebar-mini' : ''} {mobileOpen ? 'mob-sidebar-active' : ''}">
  <div class="navbar-wrapper">

    <!-- Logo: full / mini -->
    <div class="m-header">
      <Link href="/" class="b-brand sidebar-logo-full">
        <img src="/assets/images/logo_spec_app-removebg.png" alt="SPEC" style="height:90px; width:150px; object-fit:contain;">
      </Link>
      <Link href="/" class="b-brand sidebar-logo-mini">
        <img src="/assets/images/favicon.svg" alt="S" style="height:34px; width:34px;">
      </Link>
    </div>

    <!-- Nav items -->
    <div class="navbar-content">
      <ul class="pc-navbar">
        {#each navGroups as group}
          <li class="pc-item pc-caption">
            <label>{group.caption}</label>
          </li>
          {#each group.items as item}
            <li class="pc-item {isActive(item.href, item.exact) ? 'active' : ''}">
              <Link href={item.href} class="pc-link" onclick={closeMobile} data-label={item.label}>
                <span class="pc-micon"><i class={item.icon}></i></span>
                <span class="pc-mtext">{item.label}</span>
              </Link>
            </li>
          {/each}
        {/each}
      </ul>
    </div>

    <!-- Sidebar Footer -->
    <div class="m-header border-top" style="padding: 12px 16px; min-height: auto;">
      <div class="sidebar-footer-info d-flex align-items-center gap-2">
        <div class="avtar avtar-s bg-primary text-white rounded-circle fw-bold flex-shrink-0"
          style="font-size:0.8rem; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">
          {(auth?.user?.name ?? 'U')[0].toUpperCase()}
        </div>
        <div class="flex-grow-1 overflow-hidden sidebar-user-text">
          <div class="fw-semibold text-truncate" style="font-size:0.8rem; line-height:1.2;">{auth?.user?.name ?? ''}</div>
          <small class="text-muted text-capitalize" style="font-size:0.7rem;">{auth?.user?.roles?.[0] ?? ''}</small>
        </div>
        <Link href="/logout" method="post" as="button"
          class="btn btn-xs btn-light-danger p-1 flex-shrink-0" title="Cerrar sesión">
          <i class="ti ti-logout" style="font-size:1rem;"></i>
        </Link>
      </div>
    </div>

  </div>
</nav>

<!-- Header -->
<header class="pc-header">
  <div class="header-wrapper">

    <!-- Izquierda: hamburger + search trigger -->
    <div class="me-auto pc-mob-drp d-flex align-items-center gap-2">
      <!-- Desktop: modo mini -->
      <button type="button" class="btn btn-icon btn-light-secondary d-none d-lg-flex"
        onclick={toggleSidebar} title="Colapsar menú">
        <i class="ti ti-menu-2 f-18"></i>
      </button>
      <!-- Mobile: abrir sidebar -->
      <button type="button" class="btn btn-icon btn-light-secondary d-flex d-lg-none"
        onclick={toggleMobileSidebar} title="Menú">
        <i class="ti ti-menu-2 f-18"></i>
      </button>

      <!-- Botón de búsqueda (abre modal) -->
      <button type="button" class="d-none d-md-flex align-items-center gap-2 btn btn-light border"
        onclick={openSearch}
        style="width:240px; justify-content:flex-start; color:#aaa; font-size:0.875rem; border-radius:8px; padding:6px 12px;">
        <i class="ti ti-search" style="font-size:0.95rem;"></i>
        <span class="flex-grow-1 text-start">Buscar...</span>
        <kbd style="font-size:0.65rem; background:#f3f4f6; border:1px solid #ddd; border-radius:4px; padding:1px 5px; color:#888;">Ctrl K</kbd>
      </button>
    </div>

    <!-- Derecha del header -->
    <div class="ms-auto d-flex align-items-center gap-2">

      <!-- Búsqueda móvil -->
      <button type="button" class="btn btn-icon btn-light-secondary d-flex d-md-none"
        onclick={openSearch} title="Buscar">
        <i class="ti ti-search f-18"></i>
      </button>

      <!-- Lista activa badge -->
      {#if activePriceList}
        <Link href="/price-lists"
          class="badge bg-light-primary text-primary text-decoration-none d-none d-lg-inline-flex align-items-center gap-1 px-3 py-2">
          <i class="ti ti-calendar-check"></i>
          {activePriceList.name}
        </Link>
      {/if}

      <!-- User dropdown (Svelte-managed) -->
      <div class="dropdown">
        <button
          type="button"
          class="btn d-flex align-items-center gap-2 px-2 py-1"
          onclick={toggleDropdown}
          style="background:none; border:none;"
        >
          <div class="avtar avtar-s bg-primary text-white rounded-circle fw-bold" style="font-size:0.85rem;">
            {(auth?.user?.name ?? 'U')[0].toUpperCase()}
          </div>
          <div class="d-none d-md-block text-start">
            <div class="fw-semibold lh-1" style="font-size:0.85rem;">{auth?.user?.name ?? ''}</div>
            <small class="text-muted text-capitalize" style="font-size:0.7rem;">{auth?.user?.roles?.[0] ?? ''}</small>
          </div>
          <i class="ti ti-chevron-down text-muted"
            style="font-size:0.7rem; transition:transform .2s; {dropdownOpen ? 'transform:rotate(180deg)' : ''}"></i>
        </button>

        {#if dropdownOpen}
          <ul class="dropdown-menu dropdown-menu-end shadow border-0 show" style="min-width:220px;">
            <li>
              <div class="px-3 py-2 border-bottom">
                <div class="fw-semibold" style="font-size:0.875rem;">{auth?.user?.name ?? ''}</div>
                <small class="text-muted">{auth?.user?.email ?? ''}</small>
              </div>
            </li>
            <li>
              <Link
                href="/logout"
                method="post"
                as="button"
                class="dropdown-item d-flex align-items-center gap-2 text-danger py-2 mt-1"
              >
                <i class="ti ti-logout"></i> Cerrar sesión
              </Link>
            </li>
          </ul>
        {/if}
      </div>
    </div>
  </div>
</header>

<!-- Main content -->
<div class="pc-container">
  <div class="pc-content">
    {@render children()}
  </div>
</div>

<!-- Footer -->
<footer class="pc-footer">
  <div class="footer-wrapper container-fluid">
    <div class="row">
      <div class="col-sm my-1">
        <p class="m-0 text-muted">SPEC &mdash; Sistema de Gestión de Precios y Servicios</p>
      </div>
      {#if activePriceList}
        <div class="col-auto my-1">
          <span class="badge bg-light-primary text-primary">
            <i class="ti ti-calendar me-1"></i>
            {activePriceList.name}
          </span>
        </div>
      {/if}
    </div>
  </div>
</footer>

<!-- Flash notifications -->
<FlashNotification />
