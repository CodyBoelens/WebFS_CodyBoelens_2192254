<template>
    <AdminLayout>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ t('admin.help_requests') }}</h1>

        <!-- Live indicator -->
        <div class="flex items-center gap-2 mb-4 text-sm text-gray-500">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            Live – ververst automatisch elke 15 seconden
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tafel</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Tijdstip</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Actie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="req in openRequests" :key="req.id" class="hover:bg-yellow-50">
                        <td class="px-4 py-3 font-bold text-lg">Tafel {{ req.table?.number }}</td>
                        <td class="px-4 py-3">
                            <span :class="req.type === 'betalen'
                                ? 'badge bg-green-100 text-green-800'
                                : 'badge bg-yellow-100 text-yellow-800'">
                                {{ req.type === 'betalen' ? '💳 Wil betalen' : '🙋 Hulp nodig' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ formatTime(req.created_at) }}</td>
                        <td class="px-4 py-3">
                            <span class="badge bg-yellow-100 text-yellow-800">Open</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="dismiss(req.id)" class="btn-primary text-xs py-1">
                                {{ t('admin.dismiss') }}
                            </button>
                        </td>
                    </tr>
                    <tr v-if="openRequests.length === 0">
                        <td colspan="4" class="px-4 py-8 text-center text-green-600 font-medium">
                            ✅ Geen openstaande hulpverzoeken
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const { t }        = useI18n()
const openRequests = ref([])
let interval       = null

async function fetchRequests() {
    try {
        const res = await axios.get('/api/v1/help-requests')
        openRequests.value = res.data
    } catch {}
}

async function dismiss(id) {
    await axios.put(`/api/v1/help-requests/${id}/dismiss`)
    await fetchRequests()
}

function formatTime(dt) {
    return new Date(dt).toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
    fetchRequests()
    interval = setInterval(fetchRequests, 15000)  // Elke 15 sec verversen (US-5)
})
onUnmounted(() => clearInterval(interval))
</script>
