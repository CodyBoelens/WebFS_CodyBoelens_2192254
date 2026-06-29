<template>
    <WebsiteLayout>
        <div style="text-align: center;">
            <h2 style="color: #8B0000;">Contact</h2>
        </div>

        <hr style="border-color: #8B0000; margin: 15px 0;" />

        <table style="width: 100%; border-collapse: collapse;">
            <tr class="contact-row">
                <!-- Adresgegevens -->
                <td class="contact-col" style="vertical-align: top; padding: 0 15px 15px 0;">
                    <h3 style="color: #8B0000;">Adres</h3>
                    <p>
                        <strong>De Gouden Draak</strong><br />
                        Onderwijsboulevard 215<br />
                        5223 DE 's-Hertogenbosch<br /><br />
                        <strong>Telefoon:</strong> 073 - 123 45 67<br />
                        <strong>E-mail:</strong> info@goudendraak.nl
                    </p>

                    <h3 style="color: #8B0000; margin-top: 20px;">Openingstijden</h3>
                    <table style="border-collapse: collapse;">
                        <tr v-for="dag in openingstijden" :key="dag.dag">
                            <td style="padding: 2px 15px 2px 0; font-weight: bold;">{{ dag.dag }}</td>
                            <td style="padding: 2px 0;">{{ dag.tijden }}</td>
                        </tr>
                    </table>
                </td>

                <!-- Kaart afbeelding -->
                <td class="contact-col" style="vertical-align: top; padding-bottom: 15px;">
                    <h3 style="color: #8B0000;">Routekaart</h3>
                    <img src="/images/2020-03-24_1406.png"
                        alt="Routekaart De Gouden Draak"
                        style="width: 100%; border: 1px solid #8B0000;"
                        onerror="this.style.display='none'" />
                    <p style="font-size: 13px; color: #666; margin-top: 5px;">
                        Gelegen in het centrum van 's-Hertogenbosch, vlakbij het station.
                    </p>
                </td>
            </tr>
        </table>
    </WebsiteLayout>
</template>

<script setup>
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue'

const openingstijden = [
    { dag: 'Maandag',   tijden: 'Gesloten' },
    { dag: 'Dinsdag',   tijden: '17:00 – 21:30' },
    { dag: 'Woensdag',  tijden: '17:00 – 21:30' },
    { dag: 'Donderdag', tijden: '17:00 – 21:30' },
    { dag: 'Vrijdag',   tijden: '17:00 – 22:00' },
    { dag: 'Zaterdag',  tijden: '16:00 – 22:00' },
    { dag: 'Zondag',    tijden: '16:00 – 21:30' },
]
</script>

<style scoped>
.contact-col { width: 50%; }

@media (max-width: 600px) {
    .contact-row { display: block; }
    .contact-col { display: block; width: 100%; padding: 0 0 15px 0 !important; }
}
</style>
