<template>
    <WebsiteLayout>
        <div style="text-align: center;">
            <h2 style="color: #8B0000;">Nieuws</h2>
        </div>

        <hr style="border-color: #8B0000; margin: 15px 0;" />

        <div v-if="nieuwsItems.length === 0" style="text-align: center; color: #666; padding: 20px;">
            <p>Momenteel geen nieuwsberichten.</p>
        </div>

        <div v-for="item in nieuwsItems" :key="item.id" style="margin-bottom: 20px;">
            <h3 style="color: #8B0000; margin-bottom: 5px;">{{ item.titel }}</h3>
            <p style="font-size: 12px; color: #999; margin: 0 0 8px 0;">{{ item.datum }}</p>
            <p style="line-height: 1.6;">{{ item.inhoud }}</p>
            <hr style="border-color: #ddd; margin-top: 15px;" />
        </div>
    </WebsiteLayout>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import WebsiteLayout from '@/Layouts/WebsiteLayout.vue'

const page = usePage()

// Nieuws komt vanuit de route als prop, of valt terug op lege array
const nieuwsItems = page.props.nieuws ?? []
</script>
