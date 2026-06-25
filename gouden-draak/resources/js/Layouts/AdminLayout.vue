<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 w-56 bg-red-900 text-white flex flex-col z-10">
            <div class="px-5 py-4 border-b border-red-800">
                <h1 class="font-bold text-lg">🐉 Gouden Draak</h1>
                <p class="text-red-300 text-xs mt-0.5">Admin Panel</p>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                <Link :href="route('admin.dashboard')"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-800 transition-colors">
                    📊 {{ t('admin.dashboard') }}
                </Link>
                <Link :href="route('admin.menu.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-800 transition-colors">
                    🍜 {{ t('admin.menu_management') }}
                </Link>
                <Link :href="route('admin.rapportages.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-800 transition-colors">
                    📈 {{ t('admin.reports') }}
                </Link>
                <Link :href="route('admin.hulpverzoeken')"
                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-800 transition-colors">
                    🔔 {{ t('admin.help_requests') }}
                </Link>
            </nav>
            <div class="px-3 py-4 border-t border-red-800 text-xs">
                <p class="text-red-300">{{ $page.props.auth.user?.name }}</p>
                <Link :href="route('logout')" method="post" as="button" class="text-red-400 hover:text-white">
                    {{ t('auth.logout') }}
                </Link>
            </div>
        </div>

        <!-- Hoofdcontent -->
        <div class="ml-56 flex flex-col min-h-screen">
            <!-- Flash notificaties -->
            <div v-if="$page.props.flash?.success"
                class="bg-green-50 border-b border-green-200 px-6 py-3 text-green-800 text-sm font-medium">
                ✅ {{ $page.props.flash.success }}
            </div>
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>
