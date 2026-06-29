<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Topbar -->
        <header class="bg-red-800 text-white px-6 py-3 flex items-center justify-between shadow">
            <h1 class="text-xl font-bold">🐉 {{ t('kassa.title') }}</h1>
            <div class="flex items-center gap-4 text-sm">
                <span>{{ $page.props.auth.user?.name }}</span>
                <Link :href="route('logout')" method="post" as="button" class="hover:underline">{{ t('auth.logout') }}</Link>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <!-- Linker kolom: zoeken + menu -->
            <aside class="w-full md:w-2/3 flex flex-col border-r border-gray-200 bg-white">
                <!-- Zoekbalk (US-9) -->
                <div class="p-4 border-b flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <input
                            v-model="search"
                            type="search"
                            class="form-input pl-9"
                            :placeholder="t('kassa.search')"
                            aria-label="Zoek gerecht"
                        />
                        <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                    </div>
                    <select v-model="activeCategory" class="form-input sm:w-48">
                        <option value="">{{ t('kassa.filter_category') }}</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>

                <!-- Gerechten grid -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 xl:grid-cols-3 gap-3">
                        <button
                            v-for="product in filteredProducts" :key="product.id"
                            @click="addToOrder(product)"
                            class="bg-white border border-gray-200 hover:border-red-400 hover:bg-red-50 rounded-xl p-3 text-left transition-colors"
                        >
                            <span class="text-xs text-gray-400 font-mono block">{{ product.menu_number }}</span>
                            <p class="font-semibold text-sm leading-tight">{{ product.name }}</p>
                            <p class="text-red-700 font-bold text-sm mt-1">€{{ fmt(product.current_price) }}</p>
                        </button>
                    </div>
                    <p v-if="filteredProducts.length === 0" class="text-center text-gray-400 py-8">Geen gerechten gevonden.</p>
                </div>
            </aside>

            <!-- Rechter kolom: huidige bestelling -->
            <main class="hidden md:flex md:w-1/3 flex-col bg-white p-4">
                <h2 class="font-bold text-lg mb-3 text-gray-800">{{ t('order.cart') }}</h2>

                <div class="flex-1 overflow-y-auto space-y-2">
                    <p v-if="currentOrder.length === 0" class="text-gray-400 text-sm text-center mt-6">
                        {{ t('order.empty_cart') }}
                    </p>

                    <!-- Orderregel -->
                    <div v-for="(item, idx) in currentOrder" :key="idx"
                        class="border border-gray-100 rounded-xl p-3 space-y-2"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm truncate">{{ item.name }}</p>
                                <p class="text-xs text-gray-500">€{{ fmt(item.price) }}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="decrease(idx)" class="w-7 h-7 bg-gray-100 hover:bg-gray-200 rounded font-bold text-sm">−</button>
                                <span class="w-6 text-center text-sm font-bold">{{ item.quantity }}</span>
                                <button @click="increase(idx)" class="w-7 h-7 bg-gray-100 hover:bg-gray-200 rounded font-bold text-sm">+</button>
                                <button @click="removeItem(idx)" class="w-7 h-7 text-red-400 hover:text-red-600 rounded font-bold text-lg leading-none">✕</button>
                            </div>
                        </div>

                        <!-- US-11: Rijstkeuze -->
                        <select v-model="item.rice_choice" class="form-input text-xs">
                            <option value="">{{ t('kassa.rice_choice') }}...</option>
                            <option v-for="(label, key) in riceChoices" :key="key" :value="key">{{ label }}</option>
                        </select>

                        <!-- US-10: Opmerking met autocomplete -->
                        <div class="relative">
                            <input
                                v-model="item.note"
                                type="text"
                                class="form-input text-xs"
                                :placeholder="t('kassa.note_placeholder')"
                                :aria-label="t('kassa.note')"
                                @focus="activeSuggestIdx = idx"
                                @blur="closeSuggest"
                                autocomplete="off"
                            />
                            <!-- Meestgebruikte opmerkingen dropdown (US-10) -->
                            <div v-if="activeSuggestIdx === idx"
                                class="absolute z-10 left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-40 overflow-y-auto"
                            >
                                <p class="text-xs text-gray-400 px-3 pt-2 pb-1 font-semibold">Meest gebruikt:</p>
                                <button
                                    v-for="preset in filteredPresets(item.note)" :key="preset"
                                    @mousedown.prevent="selectPreset(idx, preset)"
                                    class="w-full text-left text-xs px-3 py-2 hover:bg-red-50 hover:text-red-700 transition-colors border-b border-gray-50 last:border-0"
                                >
                                    {{ preset }}
                                </button>
                                <p v-if="filteredPresets(item.note).length === 0" class="text-xs text-gray-400 px-3 py-2 italic">
                                    Typ een opmerking...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Totaal + bestellen -->
                <div class="border-t pt-3 mt-3">
                    <div class="flex justify-between font-bold text-base mb-3">
                        <span>{{ t('order.total') }}</span>
                        <span class="text-red-700">€{{ fmt(orderTotal) }}</span>
                    </div>
                    <button
                        @click="submitOrder"
                        :disabled="currentOrder.length === 0 || submitting"
                        class="btn-primary w-full py-3 mb-2 disabled:opacity-50"
                    >
                        {{ submitting ? 'Bezig...' : t('kassa.new_order') }}
                    </button>
                </div>

                <!-- Open bestellingen -->
                <div class="border-t pt-3 mt-2">
                    <h3 class="font-semibold text-sm text-gray-600 mb-2">{{ t('kassa.open_orders') }}</h3>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        <div v-for="order in openOrders" :key="order.id"
                            class="bg-gray-50 rounded-lg p-2 text-xs flex justify-between items-center"
                        >
                            <span>#{{ order.id }} – €{{ fmt(order.total) }}</span>
                            <Link
                                :href="route('kassa.order.paid', order.id)"
                                method="put"
                                as="button"
                                class="text-green-600 hover:underline font-medium"
                            >
                                {{ t('kassa.mark_paid') }}
                            </Link>
                        </div>
                        <p v-if="openOrders.length === 0" class="text-gray-400">Geen open bestellingen.</p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'

const { t }    = useI18n()
const page     = usePage()
const search   = ref('')
const activeCategory  = ref('')
const currentOrder    = ref([])
const submitting      = ref(false)
const notePresets     = ref([])       // US-10: meestgebruikte opmerkingen
const activeSuggestIdx = ref(null)

const categories  = computed(() => page.props.categories ?? [])
const openOrders  = computed(() => page.props.openOrders ?? [])
const riceChoices = computed(() => page.props.riceChoices ?? {})

// US-10: haal meestgebruikte opmerkingen op bij laden
onMounted(async () => {
    try {
        const res = await axios.get('/api/v1/note-presets')
        notePresets.value = res.data
    } catch {}
})

const allProducts = computed(() =>
    categories.value.flatMap(c => c.active_products ?? [])
)

const filteredProducts = computed(() => {
    let list = allProducts.value
    if (activeCategory.value) list = list.filter(p => p.category_id === Number(activeCategory.value))
    if (search.value) {
        const q = search.value.toLowerCase()
        list = list.filter(p => p.name.toLowerCase().includes(q) || p.menu_number.includes(q))
    }
    return list
})

const orderTotal = computed(() => currentOrder.value.reduce((s, i) => s + i.quantity * i.price, 0))

function addToOrder(product) {
    const existing = currentOrder.value.find(i => i.product_id === product.id)
    if (existing) { existing.quantity++ }
    else {
        currentOrder.value.push({
            product_id: product.id,
            name: product.name,
            price: product.current_price,
            quantity: 1,
            rice_choice: '',
            note: '',
        })
    }
}

function decrease(idx) { if (currentOrder.value[idx].quantity > 1) currentOrder.value[idx].quantity--; else removeItem(idx) }
function increase(idx) { currentOrder.value[idx].quantity++ }
function removeItem(idx) { currentOrder.value.splice(idx, 1) }

// US-10: kies een preset opmerking
function filteredPresets(currentNote) {
    if (!currentNote) return notePresets.value
    return notePresets.value.filter(p =>
        p.toLowerCase().includes(currentNote.toLowerCase())
    )
}

function selectPreset(idx, preset) {
    currentOrder.value[idx].note = preset
    activeSuggestIdx.value = null
}
function closeSuggest() {
    setTimeout(() => { activeSuggestIdx.value = null }, 150)
}

function submitOrder() {
    submitting.value = true
    router.post(route('kassa.order.store'), {
        items: currentOrder.value.map(i => ({
            product_id: i.product_id,
            quantity: i.quantity,
            note: i.note || null,
            rice_choice: i.rice_choice || null,
        })),
    }, {
        onSuccess: () => { currentOrder.value = [] },
        onFinish: () => { submitting.value = false },
    })
}

const fmt = (v) => {
    const n = parseFloat(v)
    return isNaN(n) ? '0,00' : n.toFixed(2).replace('.', ',')
}
</script>
