<template>
    <WebsiteLayout>
        <div style="text-align: center;">
            <h2 style="color: #8B0000;">Onze Menukaart</h2>

            <!-- Sorteer en PDF knoppen -->
            <div style="margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;">
                <a :href="route('menu.pdf')" target="_blank"
                    style="background-color: #8B0000; color: #FFFF00; padding: 6px 16px; font-weight: bold; text-decoration: none; border: 1px solid #8B0000; font-size: 14px;">
                    📄 Download Menu PDF
                </a>
                <button @click="sortMode = 'favourites'"
                    style="background-color: #8B0000; color: white; padding: 6px 16px; border: 1px solid #8B0000; cursor: pointer; font-size: 14px;">
                    ⭐ {{ t('menu.sort_favourites_top') }}
                </button>
                <button @click="sortMode = 'alpha'"
                    style="background-color: #8B0000; color: white; padding: 6px 16px; border: 1px solid #8B0000; cursor: pointer; font-size: 14px;">
                    🔤 {{ t('menu.sort_alpha') }}
                </button>
                <button @click="sortMode = 'default'"
                    style="background-color: #8B0000; color: white; padding: 6px 16px; border: 1px solid #8B0000; cursor: pointer; font-size: 14px;">
                    🔢 Standaard
                </button>
            </div>
        </div>

        <!-- Per categorie -->
        <div v-for="category in sortedCategories" :key="category.id" style="margin-bottom: 20px;">
            <h3 style="background-color: #8B0000; color: #FFFF00; padding: 5px 10px; margin: 0 0 8px 0;">
                {{ category.name }}
            </h3>

            <!-- Desktop tabel -->
            <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 300px;">
                <tr v-for="product in sortedProducts(category.products)" :key="product.id"
                    style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 4px 8px; width: 45px; font-weight: bold; color: #8B0000; font-family: monospace; white-space: nowrap;">
                        {{ product.menu_number }}
                    </td>
                    <td style="padding: 4px 8px;">
                        <span style="font-weight: bold;">{{ product.name }}</span>
                        <br v-if="product.description" />
                        <small v-if="product.description" style="color: #666;">{{ product.description }}</small>
                        <small v-if="product.allergens?.length" style="color: #999; display: block;">
                            ⚠️ {{ product.allergens.join(', ') }}
                        </small>
                    </td>
                    <td style="padding: 4px 8px; text-align: right; white-space: nowrap; width: 90px;">
                        <span v-if="parseFloat(product.current_price) < parseFloat(product.price)"
                            style="text-decoration: line-through; color: #999; font-size: 12px; margin-right: 4px;">
                            €{{ fmt(product.price) }}
                        </span>
                        <span :style="parseFloat(product.current_price) < parseFloat(product.price) ? 'color:#8B0000;font-weight:bold;' : ''">
                            €{{ fmt(product.current_price) }}
                        </span>
                    </td>
                    <td style="padding: 4px 8px; width: 36px; text-align: center;">
                        <button @click="toggleFavourite(product.id)"
                            :aria-label="isFavourite(product.id) ? t('menu.remove_favourite') : t('menu.add_favourite')"
                            style="background: none; border: none; cursor: pointer; font-size: 16px; padding: 0;">
                            {{ isFavourite(product.id) ? '❤️' : '🤍' }}
                        </button>
                    </td>
                </tr>
            </table>
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
const sortMode   = ref('default')
const favourites = ref([])

onMounted(() => {
    const cookie = document.cookie.split('; ').find(r => r.startsWith('favourites='))
    if (cookie) {
        try { favourites.value = JSON.parse(decodeURIComponent(cookie.split('=')[1])) }
        catch { favourites.value = [] }
    }
})

function saveFavourites() {
    const expires = new Date()
    expires.setFullYear(expires.getFullYear() + 1)
    document.cookie = `favourites=${encodeURIComponent(JSON.stringify(favourites.value))};expires=${expires.toUTCString()};path=/;SameSite=Lax`
}

function isFavourite(id) { return favourites.value.includes(Number(id)) }

function toggleFavourite(id) {
    const numId = Number(id)
    if (isFavourite(numId)) {
        favourites.value = favourites.value.filter(f => f !== numId)
    } else {
        favourites.value = [...favourites.value, numId]
    }
    saveFavourites()
}

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
        const rest = products.filter(p => !isFavourite(p.id)).sort((a, b) => parseInt(a.menu_number) - parseInt(b.menu_number))
        return [...favs, ...rest]
    }
    return [...products].sort((a, b) => a.sort_order - b.sort_order)
}

const sortedCategories = computed(() => page.props.categories ?? [])
const fmt = (v) => {
    const n = parseFloat(v)
    return isNaN(n) ? '0,00' : n.toFixed(2).replace('.', ',')
}
</script>
