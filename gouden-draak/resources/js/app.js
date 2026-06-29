import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createI18n } from 'vue-i18n'
import { ZiggyVue } from 'ziggy-js'
import nl from './lang/nl.json'
import en from './lang/en.json'
import '../css/app.css'

const appName = import.meta.env.VITE_APP_NAME || 'De Gouden Draak'

// UC-13: bepaal initiële locale — sessie > browser-taal > standaard NL
const serverLocale = document.documentElement.lang
const browserLocale = navigator.language?.startsWith('nl') ? 'nl' : 'en'
const initialLocale = (serverLocale && ['nl','en'].includes(serverLocale))
    ? serverLocale
    : browserLocale

const i18n = createI18n({
    legacy: false,
    locale: initialLocale,
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
