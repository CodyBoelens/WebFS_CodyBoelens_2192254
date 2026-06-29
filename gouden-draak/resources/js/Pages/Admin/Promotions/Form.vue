<template>
    <AdminLayout>
        <div class="max-w-2xl">
            <div class="flex items-center gap-3 mb-6">
                <Link :href="route('admin.promotions.index')" class="text-gray-500 hover:text-gray-700">← Terug</Link>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ promotion ? 'Aanbieding bewerken' : 'Aanbieding toevoegen' }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="card space-y-5">
                <!-- Naam -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Naam *</label>
                    <input v-model="form.name" type="text" class="form-input" required
                        placeholder="bv. Weekaanbieding, Studentenkorting..." />
                    <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
                </div>

                <!-- Beschrijving -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Beschrijving</label>
                    <textarea v-model="form.description" class="form-input" rows="2"
                        placeholder="Optionele toelichting..."></textarea>
                </div>

                <!-- Kortingstype -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kortingstype *</label>
                    <div class="flex gap-3 mb-3">
                        <button type="button"
                            @click="kortingsType = 'percent'"
                            :class="['px-4 py-2 text-sm font-medium rounded-lg border transition-colors',
                                kortingsType === 'percent'
                                    ? 'bg-red-700 text-white border-red-700'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50']">
                            Percentage (%)
                        </button>
                        <button type="button"
                            @click="kortingsType = 'amount'"
                            :class="['px-4 py-2 text-sm font-medium rounded-lg border transition-colors',
                                kortingsType === 'amount'
                                    ? 'bg-red-700 text-white border-red-700'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50']">
                            Vast bedrag (€)
                        </button>
                    </div>

                    <div v-if="kortingsType === 'percent'">
                        <label class="block text-sm text-gray-600 mb-1">Korting percentage *</label>
                        <div class="flex items-center gap-2">
                            <input v-model="form.discount_percent" type="number" step="0.01" min="0" max="100"
                                class="form-input w-32" placeholder="10" required />
                            <span class="text-gray-500">%</span>
                        </div>
                    </div>
                    <div v-if="kortingsType === 'amount'">
                        <label class="block text-sm text-gray-600 mb-1">Kortingsbedrag *</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">€</span>
                            <input v-model="form.discount_amount" type="number" step="0.01" min="0"
                                class="form-input w-32" placeholder="2.50" required />
                        </div>
                    </div>
                </div>

                <!-- Geldigheid -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Geldig vanaf *</label>
                        <input v-model="form.valid_from" type="date" class="form-input" required />
                        <p v-if="errors.valid_from" class="text-red-500 text-xs mt-1">{{ errors.valid_from }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Geldig t/m *</label>
                        <input v-model="form.valid_until" type="date" class="form-input" required />
                        <p v-if="errors.valid_until" class="text-red-500 text-xs mt-1">{{ errors.valid_until }}</p>
                    </div>
                </div>

                <!-- Actief -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="active" v-model="form.active"
                        class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                    <label for="active" class="text-sm font-medium text-gray-700">Actief</label>
                </div>

                <!-- Producten koppelen -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Geldig op gerechten
                        <span class="text-gray-400 font-normal">(selecteer één of meerdere)</span>
                    </label>

                    <!-- Zoekbalk -->
                    <input v-model="productSearch" type="search" class="form-input mb-2"
                        placeholder="Zoek gerecht..." />

                    <div class="border border-gray-200 rounded-lg max-h-60 overflow-y-auto">
                        <label v-for="product in filteredProducts" :key="product.id"
                            class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-0">
                            <input type="checkbox" :value="product.id" v-model="form.product_ids"
                                class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                            <span class="font-mono text-xs text-gray-400 w-8">{{ product.menu_number }}</span>
                            <span class="text-sm flex-1">{{ product.name }}</span>
                            <span class="text-sm text-gray-500">€{{ fmt(product.price) }}</span>
                        </label>
                        <p v-if="filteredProducts.length === 0" class="text-center text-gray-400 py-4 text-sm">
                            Geen gerechten gevonden.
                        </p>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ form.product_ids.length }} gerecht(en) geselecteerd</p>
                </div>

                <!-- Knoppen -->
                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="submitting" class="btn-primary disabled:opacity-50">
                        {{ submitting ? 'Opslaan...' : 'Opslaan' }}
                    </button>
                    <Link :href="route('admin.promotions.index')" class="btn-secondary">Annuleren</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ promotion: Object, products: Array })

const errors       = ref({})
const submitting   = ref(false)
const productSearch = ref('')
const kortingsType = ref(
    props.promotion?.discount_percent ? 'percent' : 'amount'
)

const form = ref({
    name:             props.promotion?.name ?? '',
    description:      props.promotion?.description ?? '',
    discount_percent: props.promotion?.discount_percent ?? '',
    discount_amount:  props.promotion?.discount_amount ?? '',
    valid_from:       props.promotion?.valid_from?.split('T')[0] ?? new Date().toISOString().split('T')[0],
    valid_until:      props.promotion?.valid_until?.split('T')[0] ?? '',
    active:           props.promotion?.active ?? true,
    product_ids:      props.promotion?.products?.map(p => p.id) ?? [],
})

const filteredProducts = computed(() => {
    if (!productSearch.value) return props.products ?? []
    const q = productSearch.value.toLowerCase()
    return (props.products ?? []).filter(p =>
        p.name.toLowerCase().includes(q) || p.menu_number.includes(q)
    )
})

function submit() {
    submitting.value = true
    errors.value = {}

    // Reset niet-gebruikte kortingstype
    if (kortingsType.value === 'percent') form.value.discount_amount = null
    if (kortingsType.value === 'amount')  form.value.discount_percent = null

    const url = props.promotion
        ? route('admin.promotions.update', props.promotion.id)
        : route('admin.promotions.store')

    const method = props.promotion ? 'put' : 'post'

    router[method](url, form.value, {
        onError: (e) => { errors.value = e },
        onFinish: () => { submitting.value = false },
    })
}

const fmt = (v) => Number(v ?? 0).toFixed(2).replace('.', ',')
</script>
