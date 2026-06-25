<template>
    <WebsiteLayout>
        <div class="max-w-xl mx-auto px-4 py-12 text-center">
            <div class="text-6xl mb-4">✅</div>
            <h1 class="text-3xl font-bold text-red-800 mb-2">{{ t('order.thank_you') }}</h1>
            <p class="text-gray-600 mb-6">
                {{ t('order.order_number') }}: <strong class="text-gray-900">#{{ order.id }}</strong>
            </p>

            <!-- Bestellingsoverzicht -->
            <div class="card text-left mb-6">
                <h2 class="font-semibold mb-3 text-gray-700">Uw bestelling:</h2>
                <ul class="space-y-1 text-sm">
                    <li v-for="item in order.items" :key="item.id" class="flex justify-between">
                        <span>{{ item.quantity }}× {{ item.product.menu_number }}. {{ item.product.name }}</span>
                        <span class="font-medium">€{{ fmt(item.quantity * item.unit_price) }}</span>
                    </li>
                </ul>
                <div class="border-t mt-3 pt-3 flex justify-between font-bold text-red-700">
                    <span>Totaal</span>
                    <span>€{{ fmt(order.total) }}</span>
                </div>
            </div>

            <!-- QR Code (UC-16) -->
            <div class="card mb-6">
                <p class="text-sm text-gray-600 mb-3">
                    Laat deze QR code zien bij het ophalen van uw bestelling.
                </p>
                <div class="flex justify-center">
                    <img :src="`data:image/png;base64,${qrCode}`" alt="QR Code voor uw bestelling"
                        class="w-48 h-48 border rounded-lg" />
                </div>
            </div>

            <!-- Print knop -->
            <button @click="window.print()" class="btn-primary">
                🖨️ {{ t('order.print_qr') }}
            </button>
        </div>
    </WebsiteLayout>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue'

const { t }  = useI18n()
const props  = defineProps({ order: Object, qrCode: String })
const fmt    = (v) => Number(v).toFixed(2).replace('.', ',')
</script>

<style>
@media print {
    header, footer, button { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
}
</style>
