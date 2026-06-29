<template>
    <WebsiteLayout>
        <div style="text-align: center;">
            <h2 style="color: #8B0000;">Aanbiedingen</h2>
        </div>

        <!-- Geen aanbiedingen -->
        <div v-if="promotions.length === 0" style="text-align: center; padding: 30px;">
            <p style="color: #666;">Momenteel zijn er geen actieve aanbiedingen.</p>
            <p style="color: #999; font-size: 14px;">Kom later terug voor onze wekelijkse acties!</p>
        </div>

        <!-- Aanbiedingen lijst -->
        <div v-for="promo in promotions" :key="promo.id" style="margin-bottom: 25px;">
            <!-- Aanbieding header -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
                <tr style="background-color: #8B0000; color: #FFFF00;">
                    <td style="padding: 8px 10px; font-size: 18px; font-weight: bold;">
                        🏷️ {{ promo.name }}
                    </td>
                    <td style="padding: 8px 10px; text-align: right; font-size: 20px; font-weight: bold;">
                        <span v-if="promo.discount_percent">{{ promo.discount_percent }}% korting</span>
                        <span v-else-if="promo.discount_amount">€{{ fmt(promo.discount_amount) }} korting</span>
                    </td>
                </tr>
            </table>

            <p v-if="promo.description" style="font-style: italic; color: #555; margin: 0 0 8px 0; padding: 0 4px;">
                {{ promo.description }}
            </p>
            <p style="font-size: 12px; color: #888; margin: 0 0 8px 0; padding: 0 4px;">
                Geldig van {{ promo.valid_from }} t/m {{ promo.valid_until }}
            </p>

            <!-- Producten in aanbieding -->
            <table v-if="promo.products?.length" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #ddd;">
                        <th style="padding: 4px 8px; text-align: left; width: 50px;">Nr.</th>
                        <th style="padding: 4px 8px; text-align: left;">Gerecht</th>
                        <th style="padding: 4px 8px; text-align: right;">Normaal</th>
                        <th style="padding: 4px 8px; text-align: right;">Aanbieding</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in promo.products" :key="product.id"
                        style="border-bottom: 1px solid #eee;">
                        <td style="padding: 4px 8px; font-family: monospace; color: #8B0000; font-weight: bold;">{{ product.menu_number }}</td>
                        <td style="padding: 4px 8px;">{{ product.name }}</td>
                        <td style="padding: 4px 8px; text-align: right; text-decoration: line-through; color: #999;">€{{ fmt(product.price) }}</td>
                        <td style="padding: 4px 8px; text-align: right; color: #8B0000; font-weight: bold;">€{{ fmt(product.current_price) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </WebsiteLayout>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue'

const page       = usePage()
const promotions = page.props.promotions ?? []
const fmt        = (v) => {
    const n = parseFloat(v)
    return isNaN(n) ? '0,00' : n.toFixed(2).replace('.', ',')
}
</script>
