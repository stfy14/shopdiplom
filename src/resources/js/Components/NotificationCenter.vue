<script setup>
import { router } from '@inertiajs/vue3'

defineProps({ notifications: { type: Array, required: true } })
const emit = defineEmits(['remove', 'remove-all'])

function navigate(n) {
    if (!n.href) return
    emit('remove', n.id)
    router.visit(n.href)
}

function timeAgo(date) {
    const s = Math.floor((Date.now() - new Date(date)) / 1000)
    if (s < 60)  return 'только что'
    const m = Math.floor(s / 60)
    if (m < 60)  return `${m} мин. назад`
    const h = Math.floor(m / 60)
    if (h < 24)  return `${h} ч. назад`
    return new Date(date).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit' })
}

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

function iconPath(n) {
    return iconPaths[n.icon] ?? (n.type === 'error' ? iconPaths.error : iconPaths.success)
}
</script>

<template>
    <!-- Изменили позиционирование: mt-5 для спуска ниже границы нава. right-[-8px] sm:right-0 чтобы не вылезало за границы, ширина подстраивается под экран -->
    <div class="absolute top-full right-[-8px] sm:right-0 mt-5 w-[calc(100vw-32px)] sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50 flex flex-col transform origin-top-right">
        <!-- Шапка -->
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 flex-shrink-0">
            <span class="font-black text-gray-900 text-sm tracking-tight">Уведомления</span>
            <button
                v-if="notifications.length > 0"
                @click.stop="emit('remove-all')"
                class="text-xs font-bold text-gray-400 hover:text-red-500 transition px-2 py-1 rounded-lg hover:bg-red-50"
            >
                Очистить всё
            </button>
        </div>

        <div class="overflow-y-auto" style="max-height: 440px;">
            <div v-if="notifications.length === 0" class="flex flex-col items-center justify-center py-16 text-gray-400">
                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-500">Всё чисто</p>
                <p class="text-xs text-gray-400 mt-1">Новые уведомления появятся здесь</p>
            </div>

            <!-- Список со скроллингом -->
            <TransitionGroup name="notif" tag="div" class="relative overflow-hidden flex flex-col">
                <div
                    v-for="n in notifications"
                    :key="n.id"
                    :class="[
                        'relative flex items-start gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0 group transition-colors bg-white',
                        n.href ? 'cursor-pointer hover:bg-gray-50/80' : '',
                        !n.read ? 'bg-blue-50/30' : ''
                    ]"
                    @click="navigate(n)"
                >
                    <div class="flex-shrink-0 pt-1.5 w-2">
                        <div v-if="!n.read" class="w-2 h-2 rounded-full bg-blue-500 ring-2 ring-blue-100" />
                    </div>

                    <div :class="[
                        'flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center',
                        n.type === 'error' ? 'bg-red-50 text-red-500' : 'bg-green-50 text-green-600'
                    ]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="iconPath(n)" />
                        </svg>
                    </div>

                    <div class="flex-grow min-w-0">
                        <p :class="['text-sm leading-snug', !n.read ? 'font-bold text-gray-900' : 'font-semibold text-gray-700']">
                            {{ n.message }}
                        </p>
                        <p class="text-[11px] text-gray-400 font-medium mt-0.5">{{ timeAgo(n.createdAt) }}</p>
                        <p v-if="n.href" class="text-[11px] text-blue-500 font-bold mt-1 flex items-center gap-1 group-hover:text-blue-600 transition-colors">
                            Перейти
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </p>
                    </div>

                    <button
                        @click.stop="emit('remove', n.id)"
                        class="flex-shrink-0 w-6 h-6 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition opacity-0 group-hover:opacity-100 mt-0.5"
                        title="Удалить"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </div>
</template>

<style scoped>
.notif-enter-active { transition: all 0.25s cubic-bezier(0.34, 1.2, 0.64, 1); }
/* Анимация смахивания ВЛЕВО: меняем translateX */
.notif-leave-active { transition: all 0.25s ease-in; position: absolute; width: 100%; z-index: 0; }
.notif-enter-from   { opacity: 0; transform: translateY(-10px); }
.notif-leave-to     { opacity: 0; transform: translateX(-100%); }
.notif-move         { transition: transform 0.25s ease; z-index: 1; }
</style>