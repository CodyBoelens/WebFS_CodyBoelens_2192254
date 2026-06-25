<template>
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ t('admin.menu_management') }}</h1>
            <Link :href="route('admin.menu.create')" class="btn-primary">
                + {{ t('admin.add_product') }}
            </Link>
        </div>

        <!-- Per categorie -->
        <div v-for="category in categories" :key="category.id" class="mb-8">
            <h2 class="text-lg font-semibold text-red-700 mb-3 border-b border-gray-200 pb-2">
                {{ category.name }}
                <span class="text-gray-400 font-normal text-sm ml-2">({{ category.products.length }})</span>
            </h2>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Nr.</th>
                            <th class="px-4 py-3 text-left">Naam</th>
                            <th class="px-4 py-3 text-right">Prijs</th>
                            <th class="px-4 py-3 text-center">Actief</th>
                            <th class="px-4 py-3 text-right">Acties</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="product in category.products" :key="product.id"
                            :class="['hover:bg-gray-50', !product.active ? 'opacity-50' : '']"
                        >
                            <td class="px-4 py-3 font-mono text-gray-500">{{ product.menu_number }}</td>
                            <td class="px-4 py-3 font-medium">
                                {{ product.name }}
                                <span v-if="!product.active" class="ml-2 badge bg-gray-100 text-gray-500">verwijderd</span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">€{{ fmt(product.price) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['badge', product.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                                    {{ product.active ? '✓' : '✕' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <Link :href="route('admin.menu.edit', product.id)"
                                    class="text-blue-600 hover:underline text-xs font-medium">
                                    Bewerken
                                </Link>
                                <Link :href="route('admin.menu.destroy', product.id)" method="delete" as="button"
                                    :onBefore="() => confirm(t('admin.delete_confirm'))"
                                    class="text-red-500 hover:underline text-xs font-medium">
                                    Verwijderen
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const { t } = useI18n()
defineProps({ categories: Array })
const fmt = (v) => Number(v).toFixed(2).replace('.', ',')
</script>
