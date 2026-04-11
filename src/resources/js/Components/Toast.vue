<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const toasts = ref([])
const timers = {}
// Сократили время до 5 секунд
const DURATION = 5000

const iconPaths = {
    order:      'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
    message:    'M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z',
    edit:       'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10',
    cancel:     'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    status:     'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    price_up:   'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941',
    price_down: 'M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181',
    cart:       'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.343 1.087-.835l1.823-6.44a1.125 1.125 0 00-.44-1.229l-5.432-4.075a1.125 1.125 0 00-1.518.056L5.64 5.39a1.125 1.125 0 00-.056 1.518l3.65 4.563M7.5 14.25L5.106 5.106M17.25 20.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM7.5 20.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014 0z',
    success:    'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    error:      'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
}

function getIconPath(icon, type) {
    return iconPaths[icon] ?? (type === 'error' ? iconPaths.error : iconPaths.success)
}

function add(message, type = 'success', { href = null, icon = null } = {}) {
    const id = Date.now()
    toasts.value.push({ id, message, type, href, icon })
    timers[id] = setTimeout(() => remove(id), DURATION)
}

function remove(id) {
    clearTimeout(timers[id])
    delete timers[id]
    toasts.value = toasts.value.filter(t => t.id !== id)
}

function navigate(toast) {
    if (!toast.href) return
    remove(toast.id)
    router.visit(toast.href)
}

defineExpose({ add })
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 items-end pointer-events-none">
        <TransitionGroup name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'relative w-80 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden pointer-events-auto',
                    toast.href ? 'cursor-pointer hover:bg-gray-50 transition-colors' : ''
                ]"
                @click="navigate(toast)"
            >
                <div class="flex items-start gap-3 px-5 py-4">
                    
                    <!-- Иконка как в панели уведомлений -->
                    <div :class="[
                        'flex-shrink-0 w-10 h-10 rounded-2xl flex items-center justify-center shadow-sm border mt-0.5',
                        toast.type === 'error' ? 'bg-red-50 text-red-500 border-red-100' : 'bg-white text-blue-600 border-gray-100'
                    ]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="getIconPath(toast.icon, toast.type)" />
                        </svg>
                    </div>

                    <!-- Текст как в панели уведомлений -->
                    <div class="flex-grow min-w-0 pt-0.5 pr-4">
                        <p class="text-sm font-bold text-gray-900 leading-snug">{{ toast.message }}</p>
                        <p v-if="toast.href" class="text-[11px] text-blue-500 font-bold mt-1.5 flex items-center gap-1 hover:text-blue-700 transition-colors uppercase tracking-wide">
                            Перейти
                            <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </p>
                    </div>
                </div>

                <!-- Кнопка закрытия -->
                <button
                    @click.stop="remove(toast.id)"
                    class="absolute top-3 right-3 w-7 h-7 rounded-xl flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                <!-- Прогресс-бар удален -->
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active { transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { transition: all 0.25s ease-in; }
.toast-enter-from   { opacity: 0; transform: translateX(110%) scale(0.9); }
.toast-leave-to     { opacity: 0; transform: translateX(110%); }
.toast-move         { transition: transform 0.3s ease; }
</style>