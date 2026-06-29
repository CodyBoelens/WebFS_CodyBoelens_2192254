<template>
    <WebsiteLayout>
        <div style="text-align: center;">
            <h2 style="color: #8B0000;">{{ t('order.thank_you') }}</h2>
            <p>{{ t('order.order_number') }}: <strong style="color: #8B0000;">#{{ order.id }}</strong></p>
        </div>

        <hr style="border-color: #8B0000; margin: 15px 0;" />

        <!-- Bestellingsoverzicht -->
        <h3 style="color: #8B0000;">Uw bestelling:</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
            <tr v-for="item in order.items" :key="item.id" style="border-bottom: 1px solid #eee;">
                <td style="padding: 4px 8px; font-family: monospace; color: #8B0000; width: 50px;">{{ item.product.menu_number }}</td>
                <td style="padding: 4px 8px;">{{ item.quantity }}× {{ item.product.name }}</td>
                <td style="padding: 4px 8px; text-align: right;">€{{ fmt(item.quantity * item.unit_price) }}</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f5f5f5;">
                <td colspan="2" style="padding: 6px 8px;">Totaal</td>
                <td style="padding: 6px 8px; text-align: right; color: #8B0000;">€{{ fmt(order.total) }}</td>
            </tr>
        </table>

        <hr style="border-color: #8B0000; margin: 15px 0;" />

        <!-- QR Code (UC-16) -->
        <div style="text-align: center;">
            <h3 style="color: #8B0000;">Uw ophaal-QR code</h3>
            <p style="font-size: 13px; color: #555; margin-bottom: 10px;">
                Laat deze QR code zien bij het ophalen van uw bestelling.
            </p>
            <div v-html="decodedQr" class="qr-wrapper"></div>

            <br /><br />
            <button @click="printPage"
                style="background-color: #8B0000; color: #FFFF00; border: 2px solid #FFFF00; padding: 8px 30px; font-size: 15px; font-weight: bold; cursor: pointer;">
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

// Decodeer base64 SVG naar HTML string
const decodedQr = props.qrCode ? atob(props.qrCode) : ''

function printPage() { window.print() }
const fmt = (v) => {
    const n = parseFloat(v)
    return isNaN(n) ? '0,00' : n.toFixed(2).replace('.', ',')
}
</script>

<style>
.qr-wrapper {
    display: inline-block;
    border: 1px solid #8B0000;
    padding: 5px;
    background: white;
}
.qr-wrapper svg {
    display: block;
    width: 200px !important;
    height: 200px !important;
}
@media print {
    header, footer, button { display: none !important; }
}
</style>
