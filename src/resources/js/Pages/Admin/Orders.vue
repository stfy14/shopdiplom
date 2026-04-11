<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3'
import { onMounted, onUnmounted } from 'vue'

// 1. Используем AdminLayout
defineOptions({ layout: AdminLayout });

const props = defineProps({
    orders: Array,
    tab: String,
})

// 2. Упрощенный слушатель: только перезагружает данные страницы.
// Тосты и счетчики обновляются в Layout.
onMounted(() => {
    window.Echo.private('admin-notifications')
        .listen('.NewOrderPlaced', () => {
            router.reload({ preserveScroll: true, preserveState: true });
        })
        .listen('.OrderUpdated', () => {
            router.reload({ preserveScroll: true, preserveState: true });
        });
});

onUnmounted(() => {
    window.Echo.leave('admin-notifications');
});

const statusMap = {
    new:               { label: 'Новый',          color: 'bg-blue-100 text-blue-700' },
    processing:        { label: 'В обработке',     color: 'bg-yellow-100 text-yellow-700' },
    shipped:           { label: 'Отправлен',       color: 'bg-green-100 text-green-700' },
    cancelled:         { label: 'Отмена (Маг.)',   color: 'bg-red-100 text-red-700' },
    cancelled_by_user: { label: 'Отмена (Клиент)', color: 'bg-gray-100 text-gray-700' },
}

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price)
}

function formatDate(dt) {
    return new Date(dt).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
    <!-- 3. Весь <nav> был удален отсюда. -->
    <div>
        <div class="flex gap-2 mb-4">
            <Link
                href="/admin/orders"
                :class="[
                    'px-4 py-2 rounded-xl text-sm font-medium transition',
                    tab === 'active' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border hover:bg-gray-50'
                ]"
            >
                Активные
            </Link>
            <Link
                href="/admin/orders?tab=archive"
                :class="[
                    'px-4 py-2 rounded-xl text-sm font-medium transition',
                    tab === 'archive' ? 'bg-red-500 text-white shadow-sm' : 'bg-white border border-red-200 text-red-500 hover:bg-red-50'
                ]"
            >
                <span class="text-sm">🗑️</span> Отмененные
            </Link>
        </div>

        <!-- Заголовки таблицы -->
        <div class="grid grid-cols-12 gap-4 px-5 text-xs font-bold text-gray-400 uppercase mb-2">
            <div class="col-span-2">Заказ</div>
            <div class="col-span-2">Клиент</div>
            <div class="col-span-3">Контакты</div>
            <div class="col-span-2">Сумма</div>
            <div class="col-span-3">Статус</div>
        </div>

        <!-- Список заказов -->
        <div class="flex flex-col gap-3">
            <div v-if="orders.length === 0" class="text-center py-16 text-gray-400">
                В этой категории нет заказов
            </div>

            <Link
                v-for="order in orders"
                :key="order.id"
                :href="`/admin/orders/${order.id}`"
                class="relative grid grid-cols-12 gap-4 items-center bg-white rounded-2xl shadow-sm p-5 transition hover:shadow-md hover:bg-gray-50"
            >
                <!-- Содержимое плашки заказа остается без изменений -->
                <div class="col-span-2">
                    <div class="font-extrabold text-gray-800">#{{ order.id }}</div>
                    <div class="text-xs text-gray-400 font-mono">{{ order.uuid.substring(0, 8) }}...</div>
                    <div class="text-xs text-gray-400 mt-1">{{ formatDate(order.created_at) }}</div>
                </div>
                <div class="col-span-2 flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-sm flex-shrink-0">👤</div>
                    <div class="font-medium truncate">{{ order.user?.name }}</div>
                </div>
                <div class="col-span-3">
                    <div class="text-sm font-medium">{{ order.phone }}</div>
                    <div class="text-xs text-gray-500 truncate">г. {{ order.city }}, ул. {{ order.street }}, {{ order.house }}</div>
                </div>
                <div class="col-span-2 font-bold text-gray-800">{{ formatPrice(order.total_price) }} ₽</div>
                <div class="col-span-3">
                     <span :class="['px-2 py-1 rounded-full text-xs font-bold', statusMap[order.status]?.color]">
                        {{ statusMap[order.status]?.label }}
                    </span>
                </div>
                <div class="absolute -top-2 -right-2 flex items-center gap-1">
                    <div v-if="order.status === 'new'" class="w-7 h-7 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-sm text-[12px]" title="Новый заказ">
                        📦
                    </div>
                    <div v-if="order.unread_messages_count > 0" class="w-7 h-7 rounded-full bg-yellow-400 text-yellow-900 flex items-center justify-center shadow-sm text-[12px]" title="Новое сообщение">
                        💬
                    </div>
                    <div v-if="order.has_unseen_activity && order.status !== 'new'" class="w-7 h-7 rounded-full bg-red-500 text-white flex items-center justify-center shadow-sm text-[12px]" title="Клиент обновил данные">
                        ✏️
                    </div>
                </div>
                <div @click.stop class="absolute top-1/2 -translate-y-1/2 right-5">
                    <Link
                        :href="`/admin/orders/${order.id}`"
                        class="px-4 py-2 border rounded-xl text-xs font-medium hover:shadow-sm transition bg-white"
                    >
                        Открыть
                    </Link>
                </div>
            </Link>
        </div>
    </div>
</template>