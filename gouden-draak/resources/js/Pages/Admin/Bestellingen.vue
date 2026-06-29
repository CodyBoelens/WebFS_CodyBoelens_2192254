<template>
    <AdminLayout>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Open bestellingen</h1>

        <div v-if="orders.length === 0" class="card text-center py-12 text-gray-400">
            Geen open bestellingen.
        </div>

        <div class="space-y-4">
            <div v-for="order in orders" :key="order.id" class="card">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-lg">#{{ order.id }}</span>
                            <span :class="['badge', sourceBadge(order.source)]">
                                {{ sourceLabel(order.source) }}
                            </span>
                            <span :class="['badge', statusBadge(order.status)]">
                                {{ order.status }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ order.table_number ? `Tafel ${order.table_number}` : order.customer_name }}
                            — {{ order.created_at }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-red-700 text-lg">€{{ fmt(order.total) }}</p>
                    </div>
                </div>

                <!-- Gerechten -->
                <table class="w-full text-sm mb-3">
                    <tr v-for="item in order.items" :key="item.nummer" class="border-b border-gray-50">
                        <td class="py-1 font-mono text-gray-400 w-10">{{ item.nummer }}</td>
                        <td class="py-1">{{ item.quantity }}× {{ item.naam }}</td>
                        <td class="py-1 text-gray-400 italic text-xs">{{ item.note }}</td>
                    </tr>
                </table>

                <!-- Status knoppen -->
                <div class="flex flex-wrap gap-2">
                    <button @click="setStatus(order.id, 'in_behandeling')"
                        :disabled="order.status === 'in_behandeling'"
                        class="text-xs px-3 py-1.5 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 disabled:opacity-40">
                        🍳 In behandeling
                    </button>
                    <button @click="setStatus(order.id, 'gereed')"
                        :disabled="order.status === 'gereed'"
                        class="text-xs px-3 py-1.5 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 disabled:opacity-40">
                        ✓ Gereed
                    </button>
                    <button @click="setStatus(order.id, 'betaald')"
                        class="text-xs px-3 py-1.5 rounded-lg bg-gray-800 text-white hover:bg-gray-700">
                        💳 Betaald
                    </button>
                    <button @click="setStatus(order.id, 'geannuleerd')"
                        class="text-xs px-3 py-1.5 rounded-lg bg-red-100 text-red-700 hover:bg-red-200">
                        ✕ Annuleren
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ orders: Array })

function setStatus(id, status) {
    router.put(route('admin.bestellingen.status', id), { status }, { preserveScroll: true })
}

function sourceLabel(source) {
    return { tablet: '📱 Tablet', kassa: '🖥️ Kassa', website: '🌐 Website' }[source] ?? source
}
function sourceBadge(source) {
    return { tablet: 'bg-blue-100 text-blue-700', kassa: 'bg-purple-100 text-purple-700', website: 'bg-green-100 text-green-700' }[source] ?? ''
}
function statusBadge(status) {
    return { open: 'bg-yellow-100 text-yellow-700', in_behandeling: 'bg-blue-100 text-blue-700', gereed: 'bg-green-100 text-green-700' }[status] ?? 'bg-gray-100 text-gray-500'
}
const fmt = (v) => Number(v ?? 0).toFixed(2).replace('.', ',')
</script>
