<template>
    <AdminLayout>
        <div class="max-w-2xl">
            <div class="flex items-center gap-3 mb-6">
                <Link :href="route('admin.menu.index')" class="text-gray-500 hover:text-gray-700">← Terug</Link>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ product ? t('admin.edit_product') : t('admin.add_product') }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="card space-y-5">

                <!-- Categorie -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categorie *</label>
                    <select v-model="form.category_id" class="form-input" required>
                        <option value="">Selecteer categorie...</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <p v-if="errors.category_id" class="text-red-500 text-xs mt-1">{{ errors.category_id }}</p>
                </div>

                <!-- Nummering (alleen bij nieuw gerecht) -->
                <div v-if="!product">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type gerecht *</label>

                    <!-- Keuze: variant of nieuw -->
                    <div class="flex gap-3 mb-3">
                        <button type="button"
                            @click="nummerType = 'variant'"
                            :class="['px-4 py-2 text-sm font-medium rounded-lg border transition-colors',
                                nummerType === 'variant'
                                    ? 'bg-red-700 text-white border-red-700'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50']">
                            Variant van bestaand gerecht
                        </button>
                        <button type="button"
                            @click="nummerType = 'nieuw'"
                            :class="['px-4 py-2 text-sm font-medium rounded-lg border transition-colors',
                                nummerType === 'nieuw'
                                    ? 'bg-red-700 text-white border-red-700'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50']">
                            Volledig nieuw gerecht
                        </button>
                    </div>

                    <!-- Variant: kies basisgerecht -->
                    <div v-if="nummerType === 'variant'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Kies het basisgerecht waarvan dit een variant is *
                        </label>
                        <select v-model="basisNummer" @change="berekenVariantNummer" class="form-input">
                            <option value="">Selecteer basisgerecht...</option>
                            <optgroup v-for="cat in categoriesMetProducten" :key="cat.id" :label="cat.name">
                                <option v-for="p in cat.products" :key="p.id" :value="p.base_number">
                                    {{ p.menu_number }} — {{ p.name }}
                                </option>
                            </optgroup>
                        </select>

                        <!-- Automatisch berekend nummer -->
                        <div v-if="form.menu_number" class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-800 font-medium">
                                ✓ Automatisch menunummer: <strong>{{ form.menu_number }}</strong>
                            </p>
                            <p class="text-xs text-green-600 mt-1">
                                Dit is het volgende beschikbare variantnummer na de bestaande varianten.
                            </p>
                        </div>
                        <p v-if="errors.menu_number" class="text-red-500 text-xs mt-1">{{ errors.menu_number }}</p>
                    </div>

                    <!-- Nieuw: automatisch doorlopend nummer -->
                    <div v-if="nummerType === 'nieuw'">
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-800 font-medium">
                                ✓ Automatisch menunummer: <strong>{{ volgendNieuwNummer }}</strong>
                            </p>
                            <p class="text-xs text-blue-600 mt-1">
                                Dit is het eerstvolgende beschikbare menunummer.
                            </p>
                        </div>
                        <p v-if="errors.menu_number" class="text-red-500 text-xs mt-1">{{ errors.menu_number }}</p>
                    </div>
                </div>

                <!-- Menunummer tonen bij bewerken -->
                <div v-if="product">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Menunummer</label>
                    <input v-model="form.menu_number" type="text" class="form-input bg-gray-50" readonly />
                    <p class="text-xs text-gray-400 mt-1">Menunummer kan niet worden aangepast om nummering consistent te houden.</p>
                </div>

                <!-- Naam -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Naam *</label>
                    <input v-model="form.name" type="text" class="form-input" required />
                    <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
                </div>

                <!-- Beschrijving -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Beschrijving</label>
                    <textarea v-model="form.description" class="form-input" rows="3"></textarea>
                </div>

                <!-- Prijs -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prijs (€) *</label>
                    <input v-model="form.price" type="number" step="0.01" min="0" class="form-input" required />
                    <p v-if="errors.price" class="text-red-500 text-xs mt-1">{{ errors.price }}</p>
                </div>

                <!-- Allergenen -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Allergenen</label>
                    <div class="flex flex-wrap gap-2">
                        <label v-for="allergen in allergenOptions" :key="allergen"
                            class="flex items-center gap-1.5 text-sm cursor-pointer">
                            <input type="checkbox" :value="allergen" v-model="form.allergens"
                                class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                            {{ allergen }}
                        </label>
                    </div>
                </div>

                <!-- Afbeelding -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Afbeelding</label>
                    <input type="file" accept="image/*" @change="handleImage" class="form-input" />
                    <img v-if="imagePreview" :src="imagePreview" class="mt-2 w-32 h-32 object-cover rounded-lg" alt="Preview" />
                </div>

                <!-- Actief -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="active" v-model="form.active"
                        class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                    <label for="active" class="text-sm font-medium text-gray-700">Actief (zichtbaar in menu)</label>
                </div>

                <!-- Knoppen -->
                <div class="flex gap-3 pt-2">
                    <button type="submit" :disabled="submitting || (!product && !kanOpslaan)"
                        class="btn-primary disabled:opacity-50">
                        {{ submitting ? 'Opslaan...' : t('admin.save') }}
                    </button>
                    <Link :href="route('admin.menu.index')" class="btn-secondary">
                        {{ t('admin.cancel') }}
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const { t } = useI18n()
const props = defineProps({
    categories: Array,
    product: Object,
    allProducts: Array,  // alle bestaande menunummers voor berekening
})

const allergenOptions = ['Gluten', 'Noten', 'Schaaldieren', 'Vis', 'Soja', 'Eieren', 'Melk', 'Sesamzaad']
const errors       = ref({})
const submitting   = ref(false)
const imagePreview = ref(props.product?.image ?? null)
const nummerType   = ref('variant')  // 'variant' of 'nieuw'
const basisNummer  = ref('')         // geselecteerd basisnummer voor variant

const form = ref({
    category_id: props.product?.category_id ?? '',
    menu_number: props.product?.menu_number ?? '',
    name:        props.product?.name ?? '',
    description: props.product?.description ?? '',
    price:       props.product?.price ?? '',
    allergens:   props.product?.allergens ?? [],
    active:      props.product?.active ?? true,
    image:       null,
})

// Alle bestaande menunummers (gesorteerd)
const alleNummers = computed(() => (props.allProducts ?? []).map(p => p.menu_number))

// Categorieën met producten voor de dropdown
const categoriesMetProducten = computed(() => {
    return (props.allProducts ?? []).reduce((cats, product) => {
        const cat = (props.categories ?? []).find(c => c.id === product.category_id)
        if (!cat) return cats
        const existing = cats.find(c => c.id === cat.id)
        // Alleen basisnummers tonen (zonder letter, of de eerste van een reeks)
        const baseNum = product.menu_number.replace(/[a-z]+$/i, '')
        const isBase = product.menu_number === baseNum

        if (isBase) {
            if (existing) {
                existing.products.push({ ...product, base_number: baseNum })
            } else {
                cats.push({ id: cat.id, name: cat.name, products: [{ ...product, base_number: baseNum }] })
            }
        }
        return cats
    }, [])
})

// Bereken het volgende variantnummer (1 → 1a → 1b → 1c ...)
function berekenVariantNummer() {
    if (!basisNummer.value) { form.value.menu_number = ''; return }

    const base = basisNummer.value
    // Vind alle nummers die met dit basisnummer beginnen
    const bestaande = alleNummers.value.filter(n => {
        const nBase = n.replace(/[a-z]+$/i, '')
        return nBase === base
    })

    // Bepaal gebruikte letters
    const gebruikteLetters = bestaande
        .map(n => n.replace(base, '').toLowerCase())
        .filter(l => l.length === 1 && l >= 'a' && l <= 'z')

    // Volgende beschikbare letter
    const alfabet = 'abcdefghijklmnopqrstuvwxyz'
    let volgendeLetter = 'a'
    for (const letter of alfabet) {
        if (!gebruikteLetters.includes(letter)) {
            volgendeLetter = letter
            break
        }
    }

    form.value.menu_number = base + volgendeLetter
}

// Bereken het volgende doorlopende nieuwe nummer
const volgendNieuwNummer = computed(() => {
    const nummers = alleNummers.value
        .map(n => parseInt(n.replace(/[a-z]+$/i, '')))
        .filter(n => !isNaN(n))
    if (!nummers.length) return '1'
    return String(Math.max(...nummers) + 1)
})

// Zet automatisch nummer bij type 'nieuw'
watch(nummerType, (type) => {
    if (type === 'nieuw') {
        form.value.menu_number = volgendNieuwNummer.value
    } else {
        form.value.menu_number = ''
        basisNummer.value = ''
    }
})

// Kan opslaan als nummer bepaald is
const kanOpslaan = computed(() => {
    if (nummerType.value === 'variant') return !!form.value.menu_number
    if (nummerType.value === 'nieuw') return !!volgendNieuwNummer.value
    return false
})

function handleImage(e) {
    const file = e.target.files[0]
    if (!file) return
    form.value.image = file
    imagePreview.value = URL.createObjectURL(file)
}

function submit() {
    submitting.value = true
    errors.value = {}

    // Zet het juiste menunummer
    if (!props.product) {
        form.value.menu_number = nummerType.value === 'nieuw'
            ? volgendNieuwNummer.value
            : form.value.menu_number
    }

    const data = new FormData()
    data.append('category_id',  form.value.category_id)
    data.append('menu_number',  form.value.menu_number)
    data.append('name',         form.value.name)
    data.append('description',  form.value.description ?? '')
    data.append('price',        form.value.price)
    data.append('active',       form.value.active ? '1' : '0')

    if (form.value.allergens.length) {
        form.value.allergens.forEach(a => data.append('allergens[]', a))
    }
    if (form.value.image) {
        data.append('image', form.value.image)
    }
    if (props.product) {
        data.append('_method', 'PUT')
    }

    const url = props.product
        ? route('admin.menu.update', props.product.id)
        : route('admin.menu.store')

    router.post(url, data, {
        forceFormData: true,
        onError: (e) => { errors.value = e },
        onFinish: () => { submitting.value = false },
    })
}
</script>
