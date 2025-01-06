// Import necessary CSS and JavaScript files
import '../css/app.css'; // Tailwind or custom CSS
import './bootstrap'; // Laravel's bootstrap.js file for initializing dependencies
// Import Syncfusion
import '@syncfusion/ej2-base/styles/material.css';
import '@syncfusion/ej2-buttons/styles/material.css';
import '@syncfusion/ej2-calendars/styles/material.css';
import '@syncfusion/ej2-dropdowns/styles/material.css';
import '@syncfusion/ej2-inputs/styles/material.css';
import '@syncfusion/ej2-navigations/styles/material.css';
import '@syncfusion/ej2-popups/styles/material.css';
import '@syncfusion/ej2-vue-schedule/styles/material.css';
import './syncfusion-license';
// Import Inertia.js, Vue, and other libraries
import {createInertiaApp} from '@inertiajs/vue3'; // Inertia.js for Vue 3
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers'; // Inertia page resolution
import {createApp, h} from 'vue'; // Vue.js core functions
import {ZiggyVue} from '../../vendor/tightenco/ziggy'; // ZiggyVue for route handling
// Import Toast for alerts
import Toast from "vue-toastification"; // Toast notifications
import "vue-toastification/dist/index.css"; // Toast styles

// Import PrimeVue and its resources
import PrimeVue from 'primevue/config';
import Lara from '@primevue/themes/lara';
import 'primeicons/primeicons.css'; // PrimeIcons

// Optional: Import commonly used PrimeVue components globally
import Calendar from 'primevue/calendar';

// Set the app name, defaulting to 'GRH Softtodo' if not set in environment
const appName = import.meta.env.VITE_APP_NAME || 'GRH Softtodo';

// Create the Inertia app
createInertiaApp({
    // Set the page title dynamically
    title: (title) => `${title} - ${appName}`,

    // Resolve the pages dynamically using Laravel Vite Plugin
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),

    // Set up the Vue app instance with PrimeVue and other plugins
    setup({el, App, props, plugin}) {
        const app = createApp({render: () => h(App, props)})
            .use(plugin) // Inertia plugin
            .use(ZiggyVue) // Ziggy plugin for route handling
            .use(Toast, { // Toast notifications plugin
                position: "top-right",
                timeout: 3000,
                closeOnClick: true,
            })
            .use(PrimeVue, {
                theme: {
                    preset: Lara,
                    options: {
                        prefix: 'p',
                        darkModeSelector: '',
                        cssLayer: false
                    }
                }
            });

        // Register PrimeVue components globally (optional)
        app.component('Calendar', Calendar);

        // Mount the app to the element
        app.mount(el);
    },

    // Progress bar settings for Inertia
    progress: {
        color: '#4B5563',
    },
});
