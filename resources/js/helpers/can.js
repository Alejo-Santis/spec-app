import { page } from '@inertiajs/svelte';
import { get } from 'svelte/store';

/**
 * Verifica si el usuario autenticado tiene un permiso dado.
 * Uso en Svelte 5:
 *   import { can } from '@/helpers/can.js';
 *   {#if can('clients.create')} ... {/if}
 */
export function can(permission) {
    const permissions = get(page)?.props?.auth?.user?.permissions ?? [];
    return permissions.includes(permission);
}

/**
 * Verifica si el usuario tiene alguno de los roles dados.
 * Uso: {#if hasRole('admin')} ... {/if}
 */
export function hasRole(...roles) {
    const userRoles = get(page)?.props?.auth?.user?.roles ?? [];
    return roles.some(r => userRoles.includes(r));
}
