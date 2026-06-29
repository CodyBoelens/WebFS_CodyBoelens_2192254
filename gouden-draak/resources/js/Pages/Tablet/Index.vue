<template>
    <div class="min-h-screen bg-red-900 text-white flex flex-col">
        <!-- Header -->
        <header class="bg-red-800 px-6 py-4 flex items-center justify-between shadow-lg">
            <div>
                <h1 class="text-xl font-bold">🐉 De Gouden Draak</h1>
                <p class="text-red-200 text-sm">{{ t('tablet.table') }} {{ table.number }}</p>
            </div>
            <div class="text-right text-sm text-red-200">
                <p v-if="openOrder">Ronde {{ openOrder.round }} / 5</p>
                <p v-if="openOrder">{{ t('tablet.rounds_left', { n: roundsLeft }) }}</p>
            </div>
        </header>

        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">
            <!-- Menu links -->
            <aside class="lg:w-1/3 bg-red-800 border-r border-red-700 flex flex-col">
                <!-- Categorietabs -->
                <div class="flex overflow-x-auto gap-1 p-3 border-b border-red-700">
                    <button
                        v-for="cat in categories" :key="cat.id"
                        @click="activeCategory = cat.id"
                        :class="['flex-shrink-0 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                            activeCategory === cat.id ? 'bg-white text-red-800' : 'bg-red-700 text-white hover:bg-red-600']"
                    >
                        {{ cat.name }}
                    </button>
                </div>

                <!-- Gerechten lijst -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    <button
                        v-for="product in currentProducts" :key="product.id"
                        @click="addToCart(product)"
                        :disabled="!canOrder"
                        class="w-full bg-red-700 hover:bg-red-600 active:bg-red-500 rounded-xl p-3 text-left transition-colors disabled:opacity-50"
                    >
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs text-red-300 font-mono">{{ product.menu_number }}</span>
                                <p class="font-semibold text-sm">{{ product.name }}</p>
                            </div>
                            <span class="text-red-200 font-bold text-sm">€{{ fmt(product.current_price) }}</span>
                        </div>
                    </button>
                </div>
            </aside>

            <!-- Winkelwagen rechts -->
            <main class="flex-1 flex flex-col p-4">
                <h2 class="text-lg font-bold mb-3">{{ t('order.cart') }}</h2>

                <!-- Wachtmelding -->
                <div v-if="!canOrder" class="bg-yellow-500 text-yellow-900 rounded-xl p-3 mb-4 text-sm font-medium">
                    ⏳ {{ t('tablet.wait_message', { minutes: waitMinutes }) }}
                </div>
                <div v-if="openOrder && openOrder.round >= 5" class="bg-orange-500 text-white rounded-xl p-3 mb-4 text-sm font-medium">
                    {{ t('tablet.max_rounds') }}
                </div>

                <!-- Winkelwagen items -->
                <div class="flex-1 overflow-y-auto">
                    <p v-if="cart.length === 0" class="text-red-300 text-center mt-8">{{ t('order.empty_cart') }}</p>
                    <ul class="space-y-2">
                        <li v-for="(item, idx) in cart" :key="idx"
                            class="bg-red-800 rounded-xl p-3 flex items-center gap-3"
                        >
                            <div class="flex-1">
                                <p class="font-medium text-sm">{{ item.name }}</p>
                                <p class="text-red-300 text-xs">€{{ fmt(item.price) }} p.st.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="decrease(idx)" class="w-8 h-8 bg-red-700 rounded-lg text-lg font-bold hover:bg-red-600">−</button>
                                <span class="w-6 text-center font-bold">{{ item.quantity }}</span>
                                <button @click="increase(idx)" class="w-8 h-8 bg-red-700 rounded-lg text-lg font-bold hover:bg-red-600">+</button>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Totaal + knoppen -->
                <div class="border-t border-red-700 pt-4 mt-4">
                    <!-- Winkelwagen totaal -->
                    <div class="flex justify-between font-bold text-lg mb-3">
                        <span>{{ t('order.cart') }}</span>
                        <span>€{{ fmt(cartTotal) }}</span>
                    </div>

                    <!-- Lopende bestelling samenvatting -->
                    <div v-if="openOrder" class="bg-red-700 rounded-xl p-3 mb-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-red-200">Totaal tafel tot nu:</span>
                            <span class="font-bold">€{{ fmt(openOrder.total) }}</span>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-red-200">Ronde:</span>
                            <span class="font-bold">{{ openOrder.round }} / 5</span>
                        </div>
                    </div>

                    <!-- Actieknoppen -->
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <!-- US-5: Hulp vragen -->
                        <button
                            @click="requestHelp"
                            :disabled="helpSent"
                            class="py-4 rounded-xl font-bold text-base bg-yellow-500 hover:bg-yellow-400 text-yellow-900 disabled:opacity-60 transition-colors"
                        >
                            {{ helpSent ? t('tablet.help_sent') : t('tablet.help_button') }}
                        </button>

                        <!-- US-1: Bestellen -->
                        <button
                            @click="placeOrder"
                            :disabled="cart.length === 0 || !canOrder || submitting"
                            class="py-4 rounded-xl font-bold text-base bg-white hover:bg-gray-100 text-red-800 disabled:opacity-50 transition-colors"
                        >
                            {{ submitting ? '...' : t('tablet.order_button') }}
                        </button>
                    </div>

                    <!-- Rekening betalen -->
                    <button
                        v-if="openOrder"
                        @click="betalen"
                        :disabled="betalingSent"
                        class="w-full py-4 rounded-xl font-bold text-base bg-green-500 hover:bg-green-400 text-white disabled:opacity-60 transition-colors"
                    >
                        {{ betalingSent ? '✓ Medewerker komt eraan!' : '💳 Rekening vragen' }}
                    </button>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const { t } = useI18n()
const props = defineProps({
    table:      Object,
    categories: Array,
    openOrder:  Object,
    canOrder:   Boolean,
    roundsLeft: Number,
})

const activeCategory = ref(props.categories[0]?.id ?? null)
const cart           = ref([])
const helpSent       = ref(false)
const submitting     = ref(false)
const betalingSent   = ref(false)

const currentProducts = computed(() =>
    props.categories.find(c => c.id === activeCategory.value)?.active_products ?? []
)

const cartTotal = computed(() => cart.value.reduce((s, i) => s + i.quantity * i.price, 0))

const waitMinutes = computed(() => {
    if (!props.openOrder?.last_order_at) return 0
    const diff = 10 - Math.floor((Date.now() - new Date(props.openOrder.last_order_at)) / 60000)
    return Math.max(0, diff)
})

function addToCart(product) {
    const existing = cart.value.find(i => i.product_id === product.id)
    if (existing) { existing.quantity++ }
    else { cart.value.push({ product_id: product.id, name: product.name, price: product.current_price, quantity: 1 }) }
}
function decrease(idx) { if (cart.value[idx].quantity > 1) cart.value[idx].quantity--; else cart.value.splice(idx, 1) }
function increase(idx) { cart.value[idx].quantity++ }

// US-1: Bestelling plaatsen
async function placeOrder() {
    if (!cart.value.length || !props.canOrder) return
    submitting.value = true
    try {
        await axios.post(`/tablet/${props.table.id}/bestellen`, {
            items: cart.value.map(i => ({ product_id: i.product_id, quantity: i.quantity }))
        })
        cart.value = []
        router.reload({ only: ['openOrder', 'canOrder', 'roundsLeft'] })
    } catch (e) {
        alert(e.response?.data?.message ?? 'Er ging iets mis.')
    } finally {
        submitting.value = false
    }
}

// US-5: Hulp aanvragen
async function requestHelp() {
    try {
        await axios.post(`/tablet/${props.table.id}/hulp`)
        helpSent.value = true
    } catch (e) {
        if (e.response?.status === 409) helpSent.value = true
        else alert('Kon hulpverzoek niet indienen.')
    }
}

// Rekening betalen — stuurt hulpverzoek "Klant wil betalen"
async function betalen() {
    if (!confirm('Wilt u de rekening aanvragen? Een medewerker komt naar uw tafel.')) return
    try {
        await axios.post(`/tablet/${props.table.id}/hulp`, { type: 'betalen' })
        betalingSent.value = true
    } catch (e) {
        // 409 = al een open verzoek, ook dan feedback tonen
        if (e.response?.status === 409) betalingSent.value = true
        else alert('Kon verzoek niet indienen.')
    }
}

const fmt = (v) => {
    const n = parseFloat(v)
    return isNaN(n) ? '0,00' : n.toFixed(2).replace('.', ',')
}
</script>
