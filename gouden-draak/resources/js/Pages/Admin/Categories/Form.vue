<template>
    <AdminLayout>
        <div class="max-w-lg">
            <div class="flex items-center gap-3 mb-6">
                <Link :href="route('admin.categories.index')" class="text-gray-500 hover:text-gray-700">← Terug</Link>
                <h1 class="text-2xl font-bold text-gray-900">{{ category ? 'Categorie bewerken' : 'Categorie toevoegen' }}</h1>
            </div>
            <form @submit.prevent="submit" class="card space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Naam *</label>
                    <input v-model="form.name" type="text" class="form-input" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Volgorde</label>
                    <input v-model="form.sort_order" type="number" min="0" class="form-input" />
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="active" v-model="form.active" class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                    <label for="active" class="text-sm font-medium text-gray-700">Actief</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">Opslaan</button>
                    <Link :href="route('admin.categories.index')" class="btn-secondary">Annuleren</Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ category: Object })
const form = ref({
    name:       props.category?.name ?? '',
    sort_order: props.category?.sort_order ?? 0,
    active:     props.category?.active ?? true,
})

function submit() {
    if (props.category) {
        router.put(route('admin.categories.update', props.category.id), form.value)
    } else {
        router.post(route('admin.categories.store'), form.value)
    }
}
</script>
