<template>
    <WebsiteLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Acties -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <h1 class="text-3xl font-bold text-red-800">{{ t('menu.title') }}</h1>
                <div class="flex flex-wrap gap-2">
                    <a :href="route('menu.pdf')" class="btn-primary text-sm" target="_blank">
                        📄 {{ t('menu.download_pdf') }}
                    </a>
                    <button @click="sortMode = 'favourites'" class="btn-secondary text-sm">
                        ⭐ {{ t('menu.sort_favourites_top') }}
                    </button>
                    <button @click="sortMode = 'alpha'" class="btn-secondary text-sm">
                        🔤 {{ t('menu.sort_alpha') }}
                    </button>
                    <button @click="sortMode = 'default'" class="btn-secondary text-sm">
                        🔢 Standaard
                    </button>
                </div>
            </div>

            <!-- Per categorie -->
            <div v-for="category in sortedCategories" :key="category.id" class="mb-10">
                <h2 class="text-xl font-semibold text-red-700 border-b border-red-200 pb-2 mb-4">
                    {{ category.name }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="product in sortedProducts(category.active_products)"
                        :key="product.id"
                        class="card flex gap-3 relative"
                    >
                        <!-- Afbeelding -->
                        <img
                            v-if="product.image"
                            :src="product.image"
                            :alt="product.name"
                            class="w-16 h-16 object-cover rounded-lg flex-shrink-0"
                        />
                        <div v-else class="w-16 h-16 bg-red-50 rounded-lg flex-shrink-0 flex items-center justify-center text-2xl">
                            🍜
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-1">
                                <div>
                                    <span class="text-xs text-gray-400 font-mono">{{ product.menu_number }}</span>
                                    <h3 class="font-semibold text-gray-900 text-sm leading-tight">{{ product.name }}</h3>
                                </div>
                                <!-- Favorieten hart (UC-15) -->
                                <button
                                    @click="toggleFavourite(product.id)"
                                    :aria-label="isFavourite(product.id) ? t('menu.remove_favourite') : t('menu.add_favourite')"
                                    class="flex-shrink-0 text-lg hover:scale-110 transition-transform"
                                >
                                    {{ isFavourite(product.id) ? '❤️' : '🤍' }}
                                </button>
                            </div>
                            <p v-if="product.description" class="text-xs text-gray-500 mt-1 line-clamp-2">
                                {{ product.description }}
                            </p>
                            <div class="mt-2 flex items-center justify-between">
                                <div>
                                    <span v-if="product.current_price < product.price" class="line-through text-gray-400 text-xs mr-1">
                                        €{{ fmt(product.price) }}
                                    </span>
                                    <span :class="product.current_price < product.price ? 'text-red-600 font-bold' : 'text-gray-800'" class="text-sm">
                                        €{{ fmt(product.current_price) }}
                                    </span>
                                </div>
                                <span v-if="product.allergens?.length" class="text-xs text-gray-400">
                                    ⚠️ {{ product.allergens.join(', ') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </WebsiteLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue'

const { t }      = useI18n()
const page       = usePage()
const sortMode   = ref('default')   // 'default' | 'favourites' | 'alpha'
const favourites = ref([])           // UC-15: opgeslagen als cookie

// UC-15: lees favorieten uit cookie bij laden
onMounted(() => {
    const cookie = document.cookie.split('; ').find(r => r.startsWith('favourites='))
    if (cookie) {
        try { favourites.value = JSON.parse(decodeURIComponent(cookie.split('=')[1])) }
        catch { favourites.value = [] }
    }
})

// UC-15: schrijf favorieten naar cookie
function saveFavourites() {
    const expires = new Date()
    expires.setFullYear(expires.getFullYear() + 1)
    document.cookie = `favourites=${encodeURIComponent(JSON.stringify(favourites.value))};expires=${expires.toUTCString()};path=/;SameSite=Lax`
}

function isFavourite(id) { return favourites.value.includes(id) }

function toggleFavourite(id) {
    if (isFavourite(id)) {
        favourites.value = favourites.value.filter(f => f !== id)
    } else {
        favourites.value = [...favourites.value, id]
    }
    saveFavourites()
}

// Sortering per categorie
function sortedProducts(products) {
    if (!products) return []
    if (sortMode.value === 'favourites') {
        return [...products].sort((a, b) => {
            const af = isFavourite(a.id), bf = isFavourite(b.id)
            if (af && !bf) return -1
            if (!af && bf) return 1
            return parseInt(a.menu_number) - parseInt(b.menu_number)
        })
    }
    if (sortMode.value === 'alpha') {
        const favs = products.filter(p => isFavourite(p.id)).sort((a, b) => a.name.localeCompare(b.name))
        const rest = products.filter(p => !isFavourite(p.id)).sort((a, b) =>
            parseInt(a.menu_number) - parseInt(b.menu_number))
        return [...favs, ...rest]
    }
    return [...products].sort((a, b) => a.sort_order - b.sort_order)
}

const sortedCategories = computed(() => page.props.categories ?? [])
const fmt = (v) => Number(v).toFixed(2).replace('.', ',')
</script>
