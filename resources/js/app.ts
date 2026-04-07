import '../css/app.css';
import axios from 'axios';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Axios config for Sanctum
axios.defaults.withCredentials = true; // send cookies automatically

// Fetch CSRF cookie before mounting app
axios.get('/sanctum/csrf-cookie').then(() => {

    createInertiaApp({
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => resolvePageComponent(
            `./pages/${name}.vue`, 
            import.meta.glob<DefineComponent>('./pages/**/*.vue')
        ),
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(Toast)
                .use(ZiggyVue)
                .mount(el);
        },
        progress: {
            color: '#4B5563',
        },
    });

    initializeTheme();

}).catch((err) => {
    console.error('Failed to fetch CSRF cookie', err);
});