<template>
    <WebsiteLayout>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8">
            <h1 class="text-3xl font-bold text-red-800 mb-6">{{ t('order.title') }}</h1>

            <!-- Formulier klantgegevens -->
            <div class="card mb-6">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="name">
                            {{ t('order.name') }} *
                        </label>
                        <input id="name" v-model="form.customer_name" type="text" class="form-input"
                            :placeholder="t('order.name')" required aria-required="true" />
                        <p v-if="errors.customer_name" class="text-red-600 text-xs mt-1">{{ errors.customer_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="phone">
                            {{ t('order.phone') }} *
                        </label>
                        <input id="phone" v-model="form.customer_phone" type="tel" class="form-input"
                            placeholder="06-12345678" required aria-required="true" />
                        <p v-if="errors.customer_phone" class="text-red-600 text-xs mt-1">{{ errors.customer_phone }}</p>
                    </div>
                </div>
            </div>

            <!-- Menu zoeken -->
            <div class="mb-4">
                <input v-model="search" type="search" class="form-input" :placeholder="t('kassa.search')"
                    aria-label="Zoek gerecht" />
            </div>

            <!-- Categoriefilter -->
            <div class="flex flex-wrap gap-2 mb-4">
                <button
                    v-for="cat in allCategories" :key="cat.id"
                    @click="activeCategory = activeCategory === cat.id ? null : cat.id"
                    :class="['badge cursor-pointer transition-colors',
                        activeCategory === cat.id
                            ? 'bg-red-700 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200']"
                >
                    {{ cat.name }}
                </button>
            </div>

            <!-- Gerechten -->
            <div class="space-y-2 mb-6">
                <div v-for="product in filteredProducts" :key="product.id"
                    class="card flex items-center gap-3"
                >
                    <div class="flex-1 min-w-0">
                        <span class="text-xs text-gray-400 font-mono">{{ product.menu_number }}</span>
                        <p class="font-medium text-sm">{{ product.name }}</p>
                        <p class="text-red-700 font-semibold text-sm">€{{ fmt(product.current_price) }}</p>
                    </div>
                    <button @click="addItem(product)" class="btn-primary text-sm py-1">
                        {{ t('order.add_to_cart') }}
                    </button>
                </div>
                <p v-if="filteredProducts.length === 0" class="text-gray-500 text-sm text-center py-4">
                    Geen gerechten gevonden.
                </p>
            </div>

            <!-- Winkelwagen -->
            <div class="card mb-6" aria-live="polite">
                <h2 class="font-bold text-lg mb-3">{{ t('order.cart') }}</h2>
                <p v-if="cart.length === 0" class="text-gray-500 text-sm">{{ t('order.empty_cart') }}</p>
                <ul class="space-y-2">
                    <li v-for="(item, idx) in cart" :key="idx" class="flex items-center justify-between gap-2">
                        <span class="text-sm flex-1">{{ item.quantity }}× {{ item.name }}</span>
                        <span class="text-sm font-medium text-red-700">€{{ fmt(item.quantity * item.price) }}</span>
                        <button @click="removeItem(idx)" class="text-gray-400 hover:text-red-600 text-lg" aria-label="Verwijder">✕</button>
                    </li>
                </ul>
                <div v-if="cart.length" class="border-t mt-3 pt-3 flex justify-between font-bold">
                    <span>{{ t('order.total') }}</span>
                    <span class="text-red-700">€{{ fmt(cartTotal) }}</span>
                </div>
            </div>

            <!-- Bestellen knop -->
            <button
                @click="submitOrder"
                :disabled="cart.length === 0 || submitting"
                class="btn-primary w-full py-3 text-base disabled:opacity-50"
            >
                {{ submitting ? 'Bezig...' : t('order.place_order') }}
            </button>
        </div>
    </WebsiteLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { router, usePage } from '@inertiajs/vue3'
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue'

const { t }       = useI18n()
const page        = usePage()
const search      = ref('')
const activeCategory = ref(null)
const submitting  = ref(false)
const errors      = ref({})
const cart        = ref([])

const form = ref({ customer_name: '', customer_phone: '' })

const allProducts   = computed(() => page.props.products ?? [])
const allCategories = computed(() => page.props.categories ?? [])

const filteredProducts = computed(() => {
    let list = allProducts.value
    if (activeCategory.value) list = list.filter(p => p.category_id === activeCategory.value)
    if (search.value) {
        const q = search.value.toLowerCase()
        list = list.filter(p => p.name.toLowerCase().includes(q) || p.menu_number.includes(q))
    }
    return list
})

const cartTotal = computed(() => cart.value.reduce((s, i) => s + i.quantity * i.price, 0))

function addItem(product) {
    const existing = cart.value.find(i => i.product_id === product.id)
    if (existing) { existing.quantity++ }
    else { cart.value.push({ product_id: product.id, name: product.name, price: product.current_price, quantity: 1 }) }
}

function removeItem(idx) { cart.value.splice(idx, 1) }

function submitOrder() {
    errors.value = {}
    if (!form.value.customer_name) { errors.value.customer_name = 'Naam is verplicht'; return }
    if (!form.value.customer_phone) { errors.value.customer_phone = 'Telefoonnummer is verplicht'; return }

    submitting.value = true
    router.post(route('bestellen.store'), {
        ...form.value,
        items: cart.value.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
    }, { onError: (e) => { errors.value = e; submitting.value = false } })
}

const fmt = (v) => Number(v).toFixed(2).replace('.', ',')
</script>
