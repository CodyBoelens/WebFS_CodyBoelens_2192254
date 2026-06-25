<template>
    <div class="min-h-screen bg-red-900 flex items-center justify-center px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-8">
            <div class="text-center mb-8">
                <div class="text-5xl mb-3">🐉</div>
                <h1 class="text-2xl font-bold text-gray-900">De Gouden Draak</h1>
                <p class="text-gray-500 text-sm mt-1">{{ t('auth.login') }}</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="email">
                        {{ t('auth.email') }}
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="form-input"
                        autocomplete="email"
                        required
                        aria-required="true"
                    />
                    <p v-if="errors.email" class="text-red-600 text-xs mt-1" role="alert">{{ errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="password">
                        {{ t('auth.password') }}
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="form-input"
                        autocomplete="current-password"
                        required
                        aria-required="true"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember" v-model="form.remember"
                        class="rounded border-gray-300 text-red-700 focus:ring-red-500" />
                    <label for="remember" class="text-sm text-gray-600">Onthoud mij</label>
                </div>

                <button type="submit" :disabled="submitting"
                    class="btn-primary w-full py-3 disabled:opacity-60"
                >
                    {{ submitting ? '...' : t('auth.login') }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t }    = useI18n()
const page     = usePage()
const errors   = ref(page.props.errors ?? {})
const submitting = ref(false)

const form = ref({ email: '', password: '', remember: false })

function submit() {
    submitting.value = true
    router.post(route('login.store'), form.value, {
        onError: (e) => { errors.value = e },
        onFinish: () => { submitting.value = false },
    })
}
</script>
