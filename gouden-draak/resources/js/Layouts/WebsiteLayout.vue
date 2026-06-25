<template>
    <div class="min-h-screen flex flex-col">
        <!-- Navigatie -->
        <header class="bg-red-800 text-white shadow-lg">
            <nav class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <Link :href="route('home')" class="flex items-center gap-2 font-bold text-xl tracking-wide">
                        🐉 De Gouden Draak
                    </Link>

                    <!-- Desktop nav -->
                    <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                        <Link :href="route('home')" class="hover:text-red-200 transition-colors">{{ t('nav.home') }}</Link>
                        <Link :href="route('menu')" class="hover:text-red-200 transition-colors">{{ t('nav.menu') }}</Link>
                        <Link :href="route('aanbiedingen')" class="hover:text-red-200 transition-colors">{{ t('nav.offers') }}</Link>
                        <Link :href="route('bestellen')" class="hover:text-red-200 transition-colors">{{ t('nav.order') }}</Link>
                    </div>

                    <!-- Taal switch (UC-13) -->
                    <div class="flex items-center gap-2">
                        <button
                            v-for="lang in ['nl', 'en']"
                            :key="lang"
                            @click="switchLocale(lang)"
                            :class="['text-xs font-semibold px-2 py-1 rounded transition-colors',
                                locale === lang ? 'bg-white text-red-800' : 'text-white hover:bg-red-700']"
                            :aria-label="`${t('nav.language')}: ${lang.toUpperCase()}`"
                        >
                            {{ lang.toUpperCase() }}
                        </button>

                        <!-- Hamburger (mobiel) -->
                        <button
                            class="md:hidden ml-2 p-1 rounded hover:bg-red-700"
                            @click="mobileOpen = !mobileOpen"
                            aria-label="Menu openen"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobiel menu -->
                <div v-show="mobileOpen" class="md:hidden pb-3 space-y-1 text-sm font-medium">
                    <Link :href="route('home')" class="block py-2 hover:text-red-200">{{ t('nav.home') }}</Link>
                    <Link :href="route('menu')" class="block py-2 hover:text-red-200">{{ t('nav.menu') }}</Link>
                    <Link :href="route('aanbiedingen')" class="block py-2 hover:text-red-200">{{ t('nav.offers') }}</Link>
                    <Link :href="route('bestellen')" class="block py-2 hover:text-red-200">{{ t('nav.order') }}</Link>
                </div>
            </nav>
        </header>

        <!-- Pagina-inhoud -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-gray-400 text-center text-xs py-4 mt-8">
            &copy; {{ new Date().getFullYear() }} De Gouden Draak
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()
const page        = usePage()
const mobileOpen  = ref(false)

function switchLocale(lang) {
    locale.value = lang
    router.post(route('locale.switch', lang), {}, { preserveState: true, preserveScroll: true })
}
</script>
