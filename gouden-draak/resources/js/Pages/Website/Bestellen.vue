<template>
    <WebsiteLayout>
        <div style="text-align: center;">
            <h2 style="color: #8B0000;">{{ t('order.title') }}</h2>
        </div>

        <!-- Klantgegevens -->
        <table style="width: 100%; margin-bottom: 15px; border-collapse: collapse;">
            <tr>
                <td style="padding: 5px; width: 50%;">
                    <label style="display: block; font-weight: bold; margin-bottom: 3px; color: #8B0000;">
                        {{ t('order.name') }} *
                    </label>
                    <input v-model="form.customer_name" type="text"
                        style="width: 100%; padding: 5px; border: 1px solid #8B0000; box-sizing: border-box;"
                        :placeholder="t('order.name')" required />
                    <p v-if="errors.customer_name" style="color: red; font-size: 12px; margin: 2px 0 0;">{{ errors.customer_name }}</p>
                </td>
                <td style="padding: 5px; width: 50%;">
                    <label style="display: block; font-weight: bold; margin-bottom: 3px; color: #8B0000;">
                        {{ t('order.phone') }} *
                    </label>
                    <input v-model="form.customer_phone" type="tel"
                        style="width: 100%; padding: 5px; border: 1px solid #8B0000; box-sizing: border-box;"
                        placeholder="06-12345678" required />
                    <p v-if="errors.customer_phone" style="color: red; font-size: 12px; margin: 2px 0 0;">{{ errors.customer_phone }}</p>
                </td>
            </tr>
        </table>

        <hr style="border-color: #8B0000; margin: 10px 0;" />

        <!-- Zoekbalk -->
        <table style="width: 100%; margin-bottom: 10px;">
            <tr>
                <td style="padding: 0 5px 0 0;">
                    <input v-model="search" type="search"
                        style="width: 100%; padding: 5px; border: 1px solid #8B0000; box-sizing: border-box;"
                        :placeholder="t('kassa.search')" />
                </td>
            </tr>
        </table>

        <!-- Categoriefilter -->
        <div style="margin-bottom: 10px; display: flex; flex-wrap: wrap; gap: 5px;">
            <button
                v-for="cat in allCategories" :key="cat.id"
                @click="activeCategory = activeCategory === cat.id ? null : cat.id"
                :style="{
                    padding: '3px 10px',
                    backgroundColor: activeCategory === cat.id ? '#8B0000' : '#ddd',
                    color: activeCategory === cat.id ? '#FFFF00' : '#333',
                    border: '1px solid #8B0000',
                    cursor: 'pointer',
                    fontSize: '13px'
                }"
            >
                {{ cat.name }}
            </button>
        </div>

        <!-- Gerechten tabel -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
            <thead>
                <tr style="background-color: #8B0000; color: #FFFF00;">
                    <th style="padding: 5px 8px; text-align: left; width: 50px;">Nr.</th>
                    <th style="padding: 5px 8px; text-align: left;">Gerecht</th>
                    <th style="padding: 5px 8px; text-align: right;">Prijs</th>
                    <th style="padding: 5px 8px; text-align: center; width: 80px;">Toevoegen</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="product in filteredProducts" :key="product.id"
                    style="border-bottom: 1px solid #eee;">
                    <td style="padding: 4px 8px; font-family: monospace; color: #8B0000; font-weight: bold;">{{ product.menu_number }}</td>
                    <td style="padding: 4px 8px;">{{ product.name }}</td>
                    <td style="padding: 4px 8px; text-align: right;">€{{ fmt(product.current_price) }}</td>
                    <td style="padding: 4px 8px; text-align: center;">
                        <button @click="addItem(product)"
                            style="background-color: #8B0000; color: white; border: none; padding: 3px 10px; cursor: pointer; font-size: 13px;">
                            + Toevoegen
                        </button>
                    </td>
                </tr>
                <tr v-if="filteredProducts.length === 0">
                    <td colspan="4" style="padding: 15px; text-align: center; color: #666;">Geen gerechten gevonden.</td>
                </tr>
            </tbody>
        </table>

        <hr style="border-color: #8B0000; margin: 10px 0;" />

        <!-- Winkelwagen -->
        <h3 style="color: #8B0000;">{{ t('order.cart') }}</h3>
        <p v-if="cart.length === 0" style="color: #666; font-style: italic;">{{ t('order.empty_cart') }}</p>
        <table v-else style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr v-for="(item, idx) in cart" :key="idx" style="border-bottom: 1px solid #eee;">
                <td style="padding: 4px 8px;">{{ item.quantity }}× {{ item.name }}</td>
                <td style="padding: 4px 8px; text-align: right;">€{{ fmt(item.quantity * item.price) }}</td>
                <td style="padding: 4px 8px; text-align: center; width: 40px;">
                    <button @click="removeItem(idx)"
                        style="background: none; border: none; color: #8B0000; cursor: pointer; font-weight: bold;">✕</button>
                </td>
            </tr>
            <tr style="background-color: #f5f5f5; font-weight: bold;">
                <td style="padding: 6px 8px;">{{ t('order.total') }}</td>
                <td style="padding: 6px 8px; text-align: right; color: #8B0000;">€{{ fmt(cartTotal) }}</td>
                <td></td>
            </tr>
        </table>

        <!-- Bestellen knop -->
        <div style="text-align: center; margin-top: 10px;">
            <button @click="submitOrder" :disabled="cart.length === 0 || submitting"
                style="background-color: #8B0000; color: #FFFF00; border: 2px solid #FFFF00; padding: 10px 40px; font-size: 16px; font-weight: bold; cursor: pointer;"
                :style="{ opacity: cart.length === 0 ? 0.5 : 1 }"
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
const form        = ref({ customer_name: '', customer_phone: '' })

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

const fmt = (v) => {
    const n = parseFloat(v)
    return isNaN(n) ? '0,00' : n.toFixed(2).replace('.', ',')
}
</script>
