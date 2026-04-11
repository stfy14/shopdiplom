<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3'
import { onMounted, onUnmounted } from 'vue'

defineOptions({ layout: AdminLayout });

const props = defineProps({
    orders: Array,
    tab: String,
})

const handleReload = () => {
    router.reload({ preserveScroll: true, preserveState: true })
}

onMounted(() => {
    window.Echo.private('admin-notifications')
        .listen('.NewOrderPlaced', handleReload)
        .listen('.OrderUpdated', handleReload)
})

onUnmounted(() => {
    window.Echo.private('admin-notifications')
        .stopListening('.NewOrderPlaced', handleReload)
        .stopListening('.OrderUpdated', handleReload)
})

const statusMap = {
    new:               { label: 'Новый',          color: 'bg-blue-100 text-blue-700' },
    processing:        { label: 'В обработке',     color: 'bg-yellow-100 text-yellow-700' },
    shipped:           { label: 'Отправлен',       color: 'bg-green-100 text-green-700' },
    cancelled:         { label: 'Отмена (Маг.)',   color: 'bg-red-100 text-red-700' },
    cancelled_by_user: { label: 'Отмена (Клиент)', color: 'bg-gray-100 text-gray-700' },
}

function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }
function formatDate(dt) { return new Date(dt).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }
</script>

<template>
    <div>
        <div class="flex gap-2 mb-6 p-1 bg-white border border-gray-100 rounded-xl shadow-sm inline-flex">
            <Link href="/admin/orders" :class="['flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition', tab === 'active' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg>
                Активные
            </Link>
            <Link href="/admin/orders?tab=archive" :class="['flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition', tab === 'archive' ? 'bg-red-500 text-white' : 'text-gray-500 hover:text-red-500 hover:bg-red-50']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Отмененные
            </Link>
        </div>

        <div class="grid grid-cols-12 gap-4 px-5 text-xs font-black text-gray-400 uppercase tracking-wider mb-2">
            <div class="col-span-2">Заказ</div>
            <div class="col-span-2">Клиент</div>
            <div class="col-span-3">Контакты</div>
            <div class="col-span-2">Сумма</div>
            <div class="col-span-3">Статус</div>
        </div>

        <div class="flex flex-col gap-3">
            <div v-if="orders.length === 0" class="text-center py-16 text-gray-400 bg-white rounded-3xl border border-gray-100">
                В этой категории нет заказов
            </div>

            <Link
                v-for="order in orders"
                :key="order.id"
                :href="`/admin/orders/${order.id}`"
                class="relative grid grid-cols-12 gap-4 items-center bg-white rounded-2xl shadow-sm p-4 transition-all hover:shadow-md hover:-translate-y-px border border-gray-100 group"
            >
                <div class="col-span-2">
                    <div class="font-black text-gray-900">#{{ order.id }}</div>
                    <div class="text-xs text-gray-400 font-mono group-hover:text-gray-500 transition">{{ order.uuid.substring(0, 8) }}...</div>
                    <div class="text-xs text-gray-400 mt-1">{{ formatDate(order.created_at) }}</div>
                </div>
                <div class="col-span-2 flex items-center gap-3">
                    <div class="w-9 h-9 bg-gray-100 rounded-full flex items-center justify-center text-blue-600 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </div>
                    <div class="font-bold text-gray-900 truncate">{{ order.user?.name }}</div>
                </div>
                <div class="col-span-3">
                    <div class="text-sm font-bold text-gray-800">{{ order.phone }}</div>
                    <div class="text-xs text-gray-500 truncate">г. {{ order.city }}, ул. {{ order.street }}, {{ order.house }}</div>
                </div>
                <div class="col-span-2 font-black text-gray-900">{{ formatPrice(order.total_price) }} ₽</div>
                <div class="col-span-3">
                    <span :class="['px-3 py-1 rounded-lg text-xs font-bold whitespace-nowrap', statusMap[order.status]?.color]">
                        {{ statusMap[order.status]?.label }}
                    </span>
                </div>
                <div class="absolute -top-1.5 -right-1.5 flex items-center gap-1.5">
                    <div v-if="order.status === 'new'" class="w-7 h-7 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-sm text-sm border-2 border-white" title="Новый заказ">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div v-if="order.unread_messages_count > 0" class="w-7 h-7 rounded-full bg-yellow-400 text-yellow-900 flex items-center justify-center shadow-sm text-sm border-2 border-white" title="Новое сообщение">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div v-if="order.has_unseen_activity && order.status !== 'new'" class="w-7 h-7 rounded-full bg-red-500 text-white flex items-center justify-center shadow-sm text-sm border-2 border-white" title="Клиент обновил данные">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </div>
                </div>
                <div @click.stop class="absolute top-1/2 -translate-y-1/2 right-5 opacity-0 group-hover:opacity-100 transition-opacity">
                    <Link :href="`/admin/orders/${order.id}`" class="px-4 py-2 border rounded-xl text-xs font-bold text-gray-600 hover:text-gray-900 transition bg-white/50 backdrop-blur-sm shadow-sm hover:shadow-md">
                        Открыть
                    </Link>
                </div>
            </Link>
        </div>
    </div>
</template>