/**
 * Definiciones de tours por página.
 * Las claves son los path prefix de la URL (Inertia).
 */
export const STORAGE_PREFIX = 'spec_tour_v1_';

export const tours = {
  '/': {
    key: 'dashboard',
    label: 'Tour del Dashboard',
    steps: [
      {
        popover: {
          title: '👋 Bienvenido a SPEC',
          description: 'Este tour te muestra los elementos clave del dashboard. Puedes cerrarlo en cualquier momento y volver a verlo con el botón <strong>?</strong> del header.',
        },
      },
      {
        element: '#tour-active-list',
        popover: {
          title: '📅 Lista de precios activa',
          description: 'Muestra la lista de precios vigente (ej: "Lista 2026"). Todos los precios y bolsas se calculan sobre esta lista. Haz clic para ir a la administración de listas.',
          side: 'bottom',
          align: 'end',
        },
      },
      {
        element: '#tour-nav',
        popover: {
          title: '🗂️ Navegación principal',
          description: 'Accede a todos los módulos: <strong>Clientes</strong>, <strong>Precios</strong>, <strong>Listas de precios</strong>, <strong>Bolsas</strong> y configuración del sistema. El sidebar se puede colapsar con el botón de hamburguesa.',
          side: 'right',
        },
      },
      {
        element: '#tour-search',
        popover: {
          title: '🔍 Búsqueda global',
          description: 'Pulsa <kbd>Ctrl+K</kbd> (Mac: <kbd>Cmd+K</kbd>) desde cualquier página para buscar clientes, listas de precios y precios de forma instantánea.',
          side: 'bottom',
        },
      },
      {
        element: '#tour-kpis',
        popover: {
          title: '📊 Indicadores clave',
          description: 'Clientes activos, cobertura de precios asignados, clientes sin precio (alerta) y bolsas activas. Un número en naranja o rojo requiere atención.',
          side: 'bottom',
        },
      },
      {
        element: '#tour-active-bundles',
        popover: {
          title: '📦 Bolsas activas',
          description: 'Estado de todas las bolsas prepagadas. La barra de progreso indica el consumo: <span style="color:#198754">verde</span> = normal, <span style="color:#ffc107">amarillo</span> = &gt;70%, <span style="color:#dc3545">rojo</span> = &gt;90% (saldo crítico).',
          side: 'top',
        },
      },
      {
        element: '#tour-quick-actions',
        popover: {
          title: '⚡ Acciones rápidas',
          description: 'Los atajos más frecuentes: crear cliente, asignar precio, crear bolsa o iniciar una nueva lista de precios anual.',
          side: 'left',
        },
      },
      {
        element: '#tour-recent-activity',
        popover: {
          title: '🕐 Actividad reciente',
          description: 'Historial de las últimas acciones en el sistema. Haz clic en "Ver todo" para acceder al log completo de auditoría.',
          side: 'left',
        },
      },
      {
        element: '#tour-help',
        popover: {
          title: '❓ Volver a ver este tour',
          description: '¡Haz clic aquí en cualquier momento para relanzar el tour de la página actual! Cada módulo tiene su propio tutorial.',
          side: 'bottom',
        },
      },
    ],
  },

  '/clients': {
    key: 'clients',
    label: 'Tour de Clientes',
    steps: [
      {
        popover: {
          title: '👥 Módulo de Clientes',
          description: 'Gestión completa de clientes: personas <strong>jurídicas</strong> (empresas con NIT) y personas <strong>naturales</strong> (cédula). Incluye campos fiscales DIAN.',
        },
      },
      {
        element: '#tour-client-actions',
        popover: {
          title: '🔧 Importar · Exportar · Crear',
          description: '<strong>Importar:</strong> carga masiva desde CSV con template descargable.<br><strong>Exportar:</strong> descarga el listado completo a Excel.<br><strong>Nuevo cliente:</strong> formulario individual.',
          side: 'bottom',
          align: 'end',
        },
      },
      {
        element: '#tour-client-filters',
        popover: {
          title: '🔎 Filtros en tiempo real',
          description: 'Busca por nombre, NIT o nombre comercial. Filtra por tipo (jurídica/natural) o estado (activo/inactivo). Los resultados se actualizan automáticamente mientras escribes.',
          side: 'bottom',
        },
      },
      {
        element: '#tour-client-table',
        popover: {
          title: '📋 Tabla de clientes',
          description: 'Haz clic en <i class="ti ti-eye"></i> para ver el detalle: precios asignados y bolsas del cliente. <i class="ti ti-pencil"></i> edita sus datos. <i class="ti ti-trash"></i> realiza baja lógica (el cliente se puede recuperar).',
          side: 'top',
        },
      },
    ],
  },

  '/client-prices': {
    key: 'client-prices',
    label: 'Tour de Precios por Cliente',
    steps: [
      {
        popover: {
          title: '💲 Precios por Cliente',
          description: 'Aquí se asigna el precio de cada servicio a cada cliente para una lista de precios específica. La fórmula es: <code>precio_base × (1 + ajuste%) → negociado → descuento</code>.',
        },
      },
      {
        element: '#tour-cp-actions',
        popover: {
          title: '📥 Importar · Exportar · Asignar',
          description: 'Importa precios masivamente con CSV. Exporta el listado (respeta el filtro de lista activa). "Asignar precio" crea un precio para un cliente puntual.',
          side: 'bottom',
          align: 'end',
        },
      },
      {
        element: '#tour-cp-filters',
        popover: {
          title: '🗂️ Filtros',
          description: 'Filtra por <strong>lista de precios</strong> (ej: Lista 2026), por nombre de cliente o por tipo de servicio. Combínalos para comparar precios entre listas.',
          side: 'bottom',
        },
      },
      {
        element: '#tour-cp-table',
        popover: {
          title: '💰 Precios y negociaciones',
          description: 'Cada fila muestra el precio base, el precio final calculado, si aplica IVA y el descuento. El ícono <i class="ti ti-pencil" style="color:#ffc107"></i> indica que el precio fue negociado manualmente (override del cálculo automático).',
          side: 'top',
        },
      },
    ],
  },

  '/price-lists': {
    key: 'price-lists',
    label: 'Tour de Listas de Precios',
    steps: [
      {
        popover: {
          title: '📅 Listas de Precios',
          description: 'Cada año comercial tiene su propia lista de precios con un porcentaje de ajuste global. Solo puede haber <strong>una lista activa</strong> a la vez.',
        },
      },
      {
        element: '#tour-pl-header',
        popover: {
          title: '➕ Crear nueva lista',
          description: 'Al crear una lista puedes elegir <strong>"Generar desde lista anterior"</strong> para copiar todos los precios de los clientes y aplicar el ajuste automáticamente, ahorrando tiempo en el proceso anual.',
          side: 'bottom',
          align: 'end',
        },
      },
      {
        element: '#tour-pl-table',
        popover: {
          title: '✅ Activar y gestionar',
          description: '<i class="ti ti-eye"></i> muestra el detalle completo (bundle tiers incluidos).<br><i class="ti ti-check"></i> activa la lista (desactiva la anterior automáticamente).<br>La lista activa aparece con badge <span style="background:#198754;color:#fff;padding:1px 6px;border-radius:4px;font-size:0.75rem">ACTIVA</span>.',
          side: 'top',
        },
      },
    ],
  },

  '/client-bundles': {
    key: 'client-bundles',
    label: 'Tour de Bolsas',
    steps: [
      {
        popover: {
          title: '📦 Bolsas / Paquetes Prepagados',
          description: 'Las bolsas son paquetes de servicios que el cliente compra por adelantado (ej: "Bolsa 500 certificados"). El sistema lleva el control del saldo disponible.',
        },
      },
      {
        element: '#tour-bundles-filters',
        popover: {
          title: '🔎 Filtros',
          description: 'Busca bolsas por cliente, filtra por lista de precios o muestra solo activas/inactivas. Útil para auditar el estado de consumo de todos los clientes.',
          side: 'bottom',
        },
      },
      {
        element: '#tour-bundles-table',
        popover: {
          title: '📊 Control de saldo y consumo',
          description: 'La barra de progreso indica el porcentaje consumido. Las filas en <span style="color:#ffc107">amarillo</span> superan el 70%; en <span style="color:#dc3545">rojo</span>, el 90% (saldo crítico — el cliente necesita renovar).<br><br>Haz clic en <i class="ti ti-eye"></i> para ver el historial de consumos y registrar nuevos.',
          side: 'top',
        },
      },
    ],
  },
};

/**
 * Devuelve el tour correspondiente a la URL actual de Inertia.
 */
export function getTourForUrl(url) {
  // Exact match first (for '/')
  if (tours[url]) return { path: url, ...tours[url] };
  // Prefix match for sub-paths
  for (const [path, tour] of Object.entries(tours)) {
    if (path !== '/' && (url.startsWith(path + '/') || url.startsWith(path + '?'))) {
      return { path, ...tour };
    }
  }
  return null;
}
