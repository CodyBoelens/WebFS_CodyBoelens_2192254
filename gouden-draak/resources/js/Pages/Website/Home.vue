<template>
    <WebsiteLayout>
        <!-- Welkomsttekst -->
        <div style="text-align: center;">
            <h3 style="color: #8B0000;">Welkom bij De Gouden Draak</h3>
            <p>
                De Gouden Draak is een gezellig Chinees-Indisch restaurant in het hart van 's-Hertogenbosch.
                Wij serveren authentieke gerechten bereid met verse ingrediënten.
            </p>
        </div>

        <hr style="border-color: #8B0000; margin: 15px 0;" />

        <!-- Actieve aanbiedingen uit database -->
        <div v-if="promotions.length > 0">
            <div style="text-align: center;">
                <h2 style="text-decoration: underline; color: #8B0000;">Actuele Aanbiedingen</h2>
            </div>

            <div v-for="promo in promotions" :key="promo.id" style="margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
                    <tr style="background-color: #8B0000; color: #FFFF00;">
                        <td style="padding: 8px 10px; font-size: 18px; font-weight: bold;">
                            🏷️ {{ promo.name }}
                        </td>
                        <td style="padding: 8px 10px; text-align: right; font-size: 14px; font-weight: bold;">
                            <span v-if="promo.discount_percent">{{ promo.discount_percent }}% korting</span>
                            <span v-else-if="promo.discount_amount">€{{ fmt(promo.discount_amount) }} korting</span>
                            <br />
                            <span style="font-size: 12px; font-weight: normal;">
                                ⏳ Nog {{ dagenOver(promo.valid_until) }} dag(en) geldig
                            </span>
                        </td>
                    </tr>
                </table>

                <p v-if="promo.description" style="font-style: italic; color: #555; margin: 0 0 6px 4px;">
                    {{ promo.description }}
                </p>

                <table v-if="promo.products?.length" style="width: 100%; border-collapse: collapse;">
                    <tr v-for="product in promo.products" :key="product.id"
                        style="border-bottom: 1px solid #eee;">
                        <td style="padding: 3px 8px; font-family: monospace; color: #8B0000; width: 50px;">{{ product.menu_number }}</td>
                        <td style="padding: 3px 8px;">{{ product.name }}</td>
                        <td style="padding: 3px 8px; text-align: right; white-space: nowrap;">
                            <span style="text-decoration: line-through; color: #999; font-size: 12px; margin-right: 4px;">
                                €{{ fmt(product.price) }}
                            </span>
                            <span style="color: #8B0000; font-weight: bold;">
                                €{{ fmt(product.current_price) }}
                            </span>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 12px; color: #888; margin: 4px 0 0 4px;">
                    Geldig t/m {{ promo.valid_until }}
                </p>
            </div>

            <hr style="border-color: #8B0000; margin: 15px 0;" />
        </div>

        <!-- Openingstijden -->
        <div style="text-align: center;">
            <h3 style="color: #8B0000;">Openingstijden</h3>
            <table style="margin: 0 auto; border-collapse: collapse;">
                <tr v-for="dag in openingstijden" :key="dag.dag">
                    <td style="padding: 3px 15px; text-align: right; font-weight: bold;">{{ dag.dag }}</td>
                    <td style="padding: 3px 15px; text-align: left;">{{ dag.tijden }}</td>
                </tr>
            </table>
        </div>

        <hr style="border-color: #8B0000; margin: 15px 0;" />

        <!-- Afhaal bestellen knop -->
        <div style="text-align: center; margin-top: 10px;">
            <Link :href="route('bestellen')"
                style="background-color: #8B0000; color: #FFFF00; padding: 10px 30px; font-size: 18px; font-weight: bold; text-decoration: none; border: 2px solid #FFFF00; display: inline-block;">
                🥡 Afhaal bestellen
            </Link>
        </div>
    </WebsiteLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue'

const page       = usePage()
const promotions = page.props.promotions ?? []

const openingstijden = [
    { dag: 'Maandag',   tijden: 'Gesloten' },
    { dag: 'Dinsdag',   tijden: '17:00 – 21:30' },
    { dag: 'Woensdag',  tijden: '17:00 – 21:30' },
    { dag: 'Donderdag', tijden: '17:00 – 21:30' },
    { dag: 'Vrijdag',   tijden: '17:00 – 22:00' },
    { dag: 'Zaterdag',  tijden: '16:00 – 22:00' },
    { dag: 'Zondag',    tijden: '16:00 – 21:30' },
]

const fmt = (v) => {
    const n = parseFloat(v)
    return isNaN(n) ? '0,00' : n.toFixed(2).replace('.', ',')
}

function dagenOver(validUntil) {
    const vandaag  = new Date()
    vandaag.setHours(0, 0, 0, 0)
    const eind = new Date(validUntil)
    eind.setHours(0, 0, 0, 0)
    const diff = Math.round((eind - vandaag) / (1000 * 60 * 60 * 24))
    return Math.max(0, diff)
}
</script>
