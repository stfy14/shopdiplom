<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ callbacks: Array })

const tab = ref('new')

const filteredCallbacks = computed(() =>
    props.callbacks.filter(c => c.status === tab.value)
)

const newCount = computed(() => props.callbacks.filter(c => c.status === 'new').length)
const doneCount = computed(() => props.callbacks.filter(c => c.status === 'processed').length)

function formatDate(dt) {
    return new Date(dt).toLocaleDateString('ru-RU', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

function markProcessed(id) {
    router.patch(`/admin/callbacks/${id}/processed`, {}, { preserveScroll: true })
}

function destroy(id) {
    if (confirm('Удалить заявку?')) {
        router.delete(`/admin/callbacks/${id}`, { preserveScroll: true })
    }
}
</script>

<template>
    <div>
        <h1 class="text-2xl font-black text-gray-900 mb-6">Запросы на звонок</h1>

        <!-- Вкладки -->
        <div class="flex gap-1 mb-6 p-1 bg-white border border-gray-100 rounded-xl shadow-sm w-full sm:w-auto sm:inline-flex">
            <button
                @click="tab = 'new'"
                :class="['relative flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-2.5 sm:py-2 rounded-lg text-sm font-bold transition whitespace-nowrap',
                         tab === 'new' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                Новые
                <span v-if="newCount > 0" class="absolute -top-2 -right-2 z-20 bg-red-500 text-white text-[11px] font-bold rounded-full w-5 h-5 flex items-center justify-center shadow-sm border-2 border-white">
                    {{ newCount }}
                </span>
            </button>
            <button
                @click="tab = 'processed'"
                :class="['flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-2.5 sm:py-2 rounded-lg text-sm font-bold transition whitespace-nowrap',
                         tab === 'processed' ? 'bg-emerald-500 text-white' : 'text-gray-500 hover:text-emerald-600 hover:bg-emerald-50']"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Обработанные
                <span v-if="doneCount > 0" class="ml-1 px-2 py-0.5 rounded-lg text-[11px] font-bold" :class="tab === 'processed' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'">
                    {{ doneCount }}
                </span>
            </button>
        </div>

        <!-- Заголовок таблицы -->
        <div class="hidden md:grid grid-cols-12 gap-4 px-5 text-xs font-black text-gray-400 uppercase tracking-wider mb-2">
            <div class="col-span-2">Дата</div>
            <div class="col-span-3">Имя</div>
            <div class="col-span-3">Телефон</div>
            <div class="col-span-2">Комментарий</div>
            <div class="col-span-2 text-right">Действия</div>
        </div>

        <div class="flex flex-col gap-3">
            <div v-if="filteredCallbacks.length === 0" class="text-center py-16 text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
                <div class="text-4xl mb-3">📞</div>
                <div class="font-bold text-gray-500">{{ tab === 'new' ? 'Новых заявок нет' : 'Обработанных заявок нет' }}</div>
            </div>

            <div
                v-for="cb in filteredCallbacks"
                :key="cb.id"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col gap-3 md:grid md:grid-cols-12 md:gap-4 md:items-center md:flex-none transition hover:shadow-md"
            >
                <!-- Дата -->
                <div class="md:col-span-2">
                    <div class="text-xs text-gray-400 font-mono">{{ formatDate(cb.created_at) }}</div>
                </div>

                <!-- Имя -->
                <div class="md:col-span-3 flex items-center justify-between md:block">
                    <div class="font-bold text-gray-900">{{ cb.name }}</div>
                    <!-- Мобильный телефон -->
                    <a :href="`tel:${cb.phone}`" class="md:hidden font-black text-blue-600 text-sm hover:text-blue-800 transition">{{ cb.phone }}</a>
                </div>

                <!-- Телефон (десктоп) -->
                <div class="hidden md:block md:col-span-3">
                    <a :href="`tel:${cb.phone}`" class="font-black text-blue-600 hover:text-blue-800 transition">{{ cb.phone }}</a>
                </div>

                <!-- Комментарий -->
                <div class="md:col-span-2">
                    <div v-if="cb.comment" class="text-sm text-gray-500 font-medium italic line-clamp-2">«{{ cb.comment }}»</div>
                    <div v-else class="text-xs text-gray-300 font-medium">—</div>
                </div>

                <!-- Действия -->
                <div class="md:col-span-2 flex items-center justify-end gap-2">
                    <button
                        v-if="cb.status === 'new'"
                        @click="markProcessed(cb.id)"
                        class="flex items-center gap-1.5 px-3 py-2 bg-green-50 hover:bg-green-100 text-green-700 rounded-xl text-xs font-bold transition shadow-sm border border-green-100"
                        title="Отметить обработанным"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span class="hidden sm:inline">Готово</span>
                    </button>
                    <button
                        @click="destroy(cb.id)"
                        class="w-8 h-8 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-xl flex items-center justify-center transition shadow-sm border border-gray-100"
                        title="Удалить"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>