<template>
    <AdminLayout>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ t('admin.reports') }}</h1>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Datum</th>
                        <th class="px-4 py-3 text-right">Bestellingen</th>
                        <th class="px-4 py-3 text-right">Omzet</th>
                        <th class="px-4 py-3 text-right">Acties</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="report in reports.data" :key="report.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ report.report_date }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ report.total_orders }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-red-700">€{{ fmt(report.total_revenue) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a v-if="report.file_path"
                                :href="route('admin.rapportages.download', report.id)"
                                class="text-blue-600 hover:underline text-xs font-medium"
                            >
                                ⬇ {{ t('admin.download') }}
                            </a>
                            <span v-else class="text-gray-400 text-xs">Niet beschikbaar</span>
                        </td>
                    </tr>
                    <tr v-if="reports.data.length === 0">
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Nog geen rapportages gegenereerd.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginering -->
        <div v-if="reports.last_page > 1" class="flex justify-center gap-2 mt-4">
            <Link v-for="page in reports.last_page" :key="page"
                :href="`?page=${page}`"
                :class="['px-3 py-1 rounded text-sm',
                    page === reports.current_page ? 'bg-red-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border']"
            >
                {{ page }}
            </Link>
        </div>

        <p class="text-xs text-gray-400 mt-4">
            Rapportages worden elke dag om 01:00 automatisch gegenereerd via de Laravel Scheduler.
        </p>
    </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const { t } = useI18n()
defineProps({ reports: Object })
const fmt = (v) => Number(v).toFixed(2).replace('.', ',')
</script>
