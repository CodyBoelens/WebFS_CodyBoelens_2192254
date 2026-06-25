<template>
    <AdminLayout>
        <div class="max-w-2xl">
            <div class="flex items-center gap-3 mb-6">
                <Link :href="route('admin.menu.index')" class="text-gray-500 hover:text-gray-700">← Terug</Link>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ product ? t('admin.edit_product') : t('admin.add_product') }}
                </h1>
            </div>

            <form @submit.prevent="submit" class="card space-y-5" enctype="multipart/form-data">
                <!-- Categorie -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categorie *</label>
                    <select v-model="form.category_id" class="form-input" required>
                        <option value="">Selecteer categorie...</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <p v-if="errors.category_id" class="text-red-500 text-xs mt-1">{{ errors.category_id }}</p>
                </div>

                <!-- Menunummer (UC-20: nummering blijft gelijk) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Menunummer *</label>
                    <input v-model="form.menu_number" type="text" class="form-input" placeholder="bv. 12, 12a, 12b" required />
                    <p class="text-xs text-gray-400 mt-1">Gebruik letter-toevoeging (a, b, c) voor varianten van bestaande nummers.</p>
                    <p v-if="errors.menu_number" class="text-red-500 text-xs mt-1">{{ errors.menu_number }}</p>
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
                            class="flex items-center gap-1.5 text-sm cursor-pointer"
                        >
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
                    <button type="submit" :disabled="submitting" class="btn-primary disabled:opacity-50">
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
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const { t } = useI18n()
const props = defineProps({ categories: Array, product: Object })

const allergenOptions = ['Gluten', 'Noten', 'Schaaldieren', 'Vis', 'Soja', 'Eieren', 'Melk', 'Sesamzaad']
const errors      = ref({})
const submitting  = ref(false)
const imagePreview = ref(props.product?.image ?? null)

const form = ref({
    category_id:  props.product?.category_id ?? '',
    menu_number:  props.product?.menu_number ?? '',
    name:         props.product?.name ?? '',
    description:  props.product?.description ?? '',
    price:        props.product?.price ?? '',
    allergens:    props.product?.allergens ?? [],
    active:       props.product?.active ?? true,
    image:        null,
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

    const data = new FormData()
    Object.entries(form.value).forEach(([k, v]) => {
        if (k === 'allergens') v.forEach(a => data.append('allergens[]', a))
        else if (v !== null) data.append(k, v)
    })

    const url  = props.product ? route('admin.menu.update', props.product.id) : route('admin.menu.store')
    const method = props.product ? 'post' : 'post'

    if (props.product) data.append('_method', 'PUT')

    router.post(url, data, {
        onError: (e) => { errors.value = e },
        onFinish: () => { submitting.value = false },
    })
}
</script>
