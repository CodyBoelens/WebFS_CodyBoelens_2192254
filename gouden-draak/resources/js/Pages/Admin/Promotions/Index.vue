<template>
    <AdminLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">🏷️ Aanbiedingen</h1>
            <Link :href="route('admin.promotions.create')" class="btn-primary">+ Aanbieding toevoegen</Link>
        </div>

        <div v-if="promotions.length === 0" class="card text-center py-12 text-gray-400">
            Nog geen aanbiedingen aangemaakt.
        </div>

        <div class="space-y-4">
            <div v-for="promo in promotions" :key="promo.id" class="card">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="font-bold text-lg">{{ promo.name }}</h2>
                            <span :class="['badge', promo.active && isGeldig(promo)
                                ? 'bg-green-100 text-green-700'
                                : 'bg-gray-100 text-gray-500']">
                                {{ promo.active && isGeldig(promo) ? '✓ Actief' : 'Inactief' }}
                            </span>
                        </div>
                        <p v-if="promo.description" class="text-sm text-gray-500 mb-2">{{ promo.description }}</p>
                        <div class="flex flex-wrap gap-3 text-sm text-gray-600">
                            <span v-if="promo.discount_percent" class="font-semibold text-red-700">
                                {{ promo.discount_percent }}% korting
                            </span>
                            <span v-if="promo.discount_amount" class="font-semibold text-red-700">
                                €{{ fmt(promo.discount_amount) }} korting
                            </span>
                            <span>📅 {{ promo.valid_from }} t/m {{ promo.valid_until }}</span>
                        </div>

                        <!-- Gekoppelde producten -->
                        <div v-if="promo.products?.length" class="mt-3">
                            <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Geldig op:</p>
                            <div class="flex flex-wrap gap-1">
                                <span v-for="p in promo.products" :key="p.id"
                                    class="badge bg-red-50 text-red-700">
                                    {{ p.menu_number }}. {{ p.name }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-xs text-gray-400 mt-2 italic">Geen producten gekoppeld</p>
                    </div>

                    <!-- Acties -->
                    <div class="flex gap-2 flex-shrink-0">
                        <Link :href="route('admin.promotions.edit', promo.id)"
                            class="text-blue-600 hover:underline text-sm font-medium">
                            Bewerken
                        </Link>
                        <Link :href="route('admin.promotions.destroy', promo.id)"
                            method="delete" as="button"
                            :onBefore="() => confirm('Aanbieding verwijderen?')"
                            class="text-red-500 hover:underline text-sm font-medium">
                            Verwijderen
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ promotions: Array })

function isGeldig(promo) {
    const now  = new Date().toISOString().split('T')[0]
    return promo.valid_from <= now && promo.valid_until >= now
}
const fmt = (v) => Number(v ?? 0).toFixed(2).replace('.', ',')
</script>
