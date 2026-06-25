import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createI18n } from 'vue-i18n'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import nl from './lang/nl.json'
import en from './lang/en.json'
import '../css/app.css'

const appName = import.meta.env.VITE_APP_NAME || 'De Gouden Draak'

// UC-13: i18n instelling
const i18n = createI18n({
    legacy: false,
    locale: document.documentElement.lang || 'nl',
    fallbackLocale: 'nl',
    messages: { nl, en },
})

createInertiaApp({
    title: (title) => title ? `${title} – ${appName}` : appName,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .use(ZiggyVue)
            .mount(el)
    },
    progress: { color: '#C0392B' },
})
