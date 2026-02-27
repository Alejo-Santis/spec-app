import { createInertiaApp } from '@inertiajs/svelte';
import { mount } from 'svelte';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

createInertiaApp({
    title: (title) => `${title} - SPEC`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.svelte', { eager: true });
        return pages[`./Pages/${name}.svelte`];
    },
    setup({ el, App, props }) {
        mount(App, { target: el, props });
    },
});
