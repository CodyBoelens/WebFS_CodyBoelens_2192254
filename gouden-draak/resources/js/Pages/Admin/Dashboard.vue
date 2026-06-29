<template>
    <AdminLayout>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ t('admin.dashboard') }}</h1>

        <!-- Statistieken -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <div class="card text-center py-6">
                <p class="text-3xl font-bold text-red-700">{{ stats.total_orders }}</p>
                <p class="text-sm text-gray-500 mt-1">Bestellingen vandaag</p>
            </div>
            <div class="card text-center py-6">
                <p class="text-3xl font-bold text-red-700">€{{ fmt(stats.revenue_today) }}</p>
                <p class="text-sm text-gray-500 mt-1">Omzet vandaag</p>
            </div>
            <div class="card text-center py-6">
                <p class="text-3xl font-bold text-yellow-600">{{ stats.open_help_requests }}</p>
                <p class="text-sm text-gray-500 mt-1">Hulpverzoeken open</p>
            </div>
        </div>

        <!-- Open bestellingen per bron -->
        <h2 class="text-lg font-semibold text-gray-700 mb-3">Open bestellingen</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="card text-center py-4 border-l-4 border-red-700">
                <p class="text-2xl font-bold text-red-700">{{ stats.open_orders_kassa }}</p>
                <p class="text-sm text-gray-500 mt-1">🖥️ Kassa</p>
            </div>
            <div class="card text-center py-4 border-l-4 border-blue-500">
                <p class="text-2xl font-bold text-blue-600">{{ stats.open_orders_tablet }}</p>
                <p class="text-sm text-gray-500 mt-1">📱 Tablet</p>
            </div>
            <div class="card text-center py-4 border-l-4 border-green-500">
                <p class="text-2xl font-bold text-green-600">{{ stats.open_orders_website }}</p>
                <p class="text-sm text-gray-500 mt-1">🌐 Website (afhaal)</p>
            </div>
        </div>

        <!-- Snelkoppelingen -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <Link :href="route('admin.menu.index')" class="card hover:border-red-300 hover:shadow-md transition-all flex items-center gap-4">
                <div class="text-3xl">🍜</div>
                <div>
                    <p class="font-semibold">{{ t('admin.menu_management') }}</p>
                    <p class="text-xs text-gray-400">Gerechten beheren</p>
                </div>
            </Link>
            <Link :href="route('admin.rapportages.index')" class="card hover:border-red-300 hover:shadow-md transition-all flex items-center gap-4">
                <div class="text-3xl">📈</div>
                <div>
                    <p class="font-semibold">{{ t('admin.reports') }}</p>
                    <p class="text-xs text-gray-400">Dagelijkse rapportages</p>
                </div>
            </Link>
            <Link :href="route('admin.hulpverzoeken')" class="card hover:border-red-300 hover:shadow-md transition-all flex items-center gap-4">
                <div class="text-3xl">🔔</div>
                <div>
                    <p class="font-semibold">{{ t('admin.help_requests') }}</p>
                    <p class="text-xs text-gray-400 flex items-center gap-1">
                        <span v-if="stats.open_help_requests > 0" class="w-2 h-2 rounded-full bg-yellow-500 inline-block"></span>
                        {{ stats.open_help_requests }} open
                    </p>
                </div>
            </Link>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const { t } = useI18n()
defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_orders: 0,
            revenue_today: 0,
            open_orders_kassa: 0,
            open_orders_tablet: 0,
            open_orders_website: 0,
            open_help_requests: 0,
        })
    }
})
const fmt = (v) => Number(v ?? 0).toFixed(2).replace('.', ',')
</script>
