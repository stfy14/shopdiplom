<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    orders: Array,
    user: Object,
})

// WebSocket is handled by ShopLayout which already reloads only['orders']
// when on Shop/Profile for OrderUpdated and NewOrderMessage events.
// No duplicate listeners needed here.

const statusMap = {
    new:               { label: 'Ждём подтверждения', color: 'bg-blue-100 text-blue-700' },
    processing:        { label: 'В обработке',        color: 'bg-yellow-100 text-yellow-700' },
    shipped:           { label: 'Отправлен',          color: 'bg-green-100 text-green-700' },
    cancelled:         { label: 'Отменён',            color: 'bg-red-100 text-red-700' },
    cancelled_by_user: { label: 'Отменён вами',       color: 'bg-gray-100 text-gray-700' },
}

function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }
function formatDate(dt) {
    return new Date(dt).toLocaleDateString('ru-RU', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-5xl mx-auto">
            <!-- User card -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6 flex items-center gap-4 border border-gray-100">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-2xl">👤</div>
                <div>
                    <div class="font-bold text-xl">{{ user.name }}</div>
                    <div class="text-gray-400 text-sm">{{ user.email }}</div>
                    <span v-if="user.role === 'admin'" class="text-xs bg-gray-900 text-white px-2 py-0.5 rounded-full mt-1 inline-block">Администратор</span>
                </div>
                <div class="ml-auto flex gap-2">
                    <Link v-if="user.role === 'admin'" href="/admin" class="px-4 py-2 bg-gray-900 text-white rounded-xl text-sm font-medium hover:bg-gray-700 transition">Панель управления</Link>
                    <Link href="/logout" method="post" as="button" class="px-4 py-2 border border-gray-200 text-gray-500 rounded-xl text-sm hover:bg-gray-50 transition">Выйти</Link>
                </div>
            </div>

            <h2 class="font-bold text-xl mb-4 text-gray-800">Мои заказы</h2>

            <div v-if="orders.length > 0">
                <div class="grid grid-cols-12 gap-4 px-5 text-xs font-black text-gray-400 uppercase tracking-wider mb-2">
                    <div class="col-span-2">Заказ</div>
                    <div class="col-span-4">Контакты</div>
                    <div class="col-span-3">Сумма</div>
                    <div class="col-span-3">Статус</div>
                </div>

                <div class="flex flex-col gap-3">
                    <!-- Применили все фиксы анимации и убрали кнопку -->
                    <Link
                        v-for="order in orders"
                        :key="order.id"
                        :href="`/orders/${order.uuid}`"
                        class="relative grid grid-cols-12 gap-4 items-center bg-white rounded-2xl p-4 border border-gray-100 group shadow-sm transition duration-300 ease-out hover:shadow-md hover:-translate-y-1 will-change-transform antialiased"
                    >
                        <!-- Order # + date -->
                        <div class="col-span-2">
                            <div class="font-black text-gray-900">#{{ order.id }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ formatDate(order.created_at) }}</div>
                        </div>

                        <!-- Contacts -->
                        <div class="col-span-4">
                            <div class="text-sm font-bold text-gray-800">{{ order.phone }}</div>
                            <div class="text-xs text-gray-500 truncate">г. {{ order.city }}, ул. {{ order.street }}, {{ order.house }}</div>
                        </div>

                        <!-- Total -->
                        <div class="col-span-3 font-black text-gray-900 whitespace-nowrap">{{ formatPrice(order.total_price) }} ₽</div>

                        <!-- Status badge -->
                        <div class="col-span-3">
                            <span :class="['px-3 py-1 rounded-lg text-xs font-bold whitespace-nowrap', statusMap[order.status]?.color]">
                                {{ statusMap[order.status]?.label }}
                            </span>
                        </div>

                        <!-- Иконки уведомлений остаются на месте -->
                        <div class="absolute -top-1.5 -right-1.5 flex items-center gap-1.5">
                            <div v-if="order.status === 'new'" class="w-7 h-7 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-md border-2 border-white" title="Ждём подтверждения заказа">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div v-if="order.unread_messages_count > 0" class="w-7 h-7 rounded-full bg-yellow-400 text-yellow-900 flex items-center justify-center shadow-md border-2 border-white" title="Новое сообщение от менеджера">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </div>
                            <div v-if="order.user_has_unseen_status_change" class="w-7 h-7 rounded-full bg-green-500 text-white flex items-center justify-center shadow-md border-2 border-white" title="Статус заказа изменён">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div v-if="order.user_has_unseen_contact_update" class="w-7 h-7 rounded-full bg-purple-500 text-white flex items-center justify-center shadow-md border-2 border-white" title="Менеджер обновил контактные данные">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </div>
                        </div>

                        <!-- Блок с кнопкой "Открыть" полностью удалён -->
                    </Link>
                </div>
            </div>

            <div v-else class="text-center py-16 text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
                <div class="text-5xl mb-3">📦</div>
                <div>У вас пока нет заказов</div>
                <Link href="/" class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700 transition">
                    Перейти в каталог
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>