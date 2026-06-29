<template>
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Categorieën</h1>
            <Link :href="route('admin.categories.create')" class="btn-primary">+ Categorie toevoegen</Link>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Volgorde</th>
                        <th class="px-4 py-3 text-left">Naam</th>
                        <th class="px-4 py-3 text-center">Actief</th>
                        <th class="px-4 py-3 text-right">Acties</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="cat in categories" :key="cat.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400">{{ cat.sort_order }}</td>
                        <td class="px-4 py-3 font-medium">{{ cat.name }}</td>
                        <td class="px-4 py-3 text-center">
                            <span :class="['badge', cat.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                                {{ cat.active ? '✓' : '✕' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <Link :href="route('admin.categories.edit', cat.id)" class="text-blue-600 hover:underline text-xs font-medium">Bewerken</Link>
                            <Link :href="route('admin.categories.destroy', cat.id)" method="delete" as="button"
                                :onBefore="() => confirm('Categorie verwijderen?')"
                                class="text-red-500 hover:underline text-xs font-medium">Verwijderen</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
defineProps({ categories: Array })
</script>
