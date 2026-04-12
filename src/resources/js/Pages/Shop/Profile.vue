<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    orders: Array,
    user: Object,
})

const activeTab = ref('active')

const statusMap = {
    new:               { label: 'Ждём подтверждения', color: 'bg-blue-100 text-blue-700' },
    processing:        { label: 'В обработке',        color: 'bg-yellow-100 text-yellow-700' },
    shipped:           { label: 'Отправлен',          color: 'bg-green-100 text-green-700' },
    completed:         { label: 'Завершён',           color: 'bg-emerald-100 text-emerald-700' },
    cancelled:         { label: 'Отменён',            color: 'bg-red-100 text-red-700' },
    cancelled_by_user: { label: 'Отменён вами',       color: 'bg-gray-100 text-gray-700' },
}

const filteredOrders = computed(() => {
    if (activeTab.value === 'active') {
        return props.orders.filter(o => ['new', 'processing', 'shipped'].includes(o.status))
    }
    if (activeTab.value === 'completed') {
        return props.orders.filter(o => o.status === 'completed')
    }
    if (activeTab.value === 'cancelled') {
        return props.orders.filter(o => ['cancelled', 'cancelled_by_user'].includes(o.status))
    }
    return props.orders
})

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
            <div class="bg-white rounded-2xl shadow-sm p-5 sm:p-6 mb-6 flex flex-col sm:flex-row sm:items-center gap-5 border border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-2xl flex-shrink-0">👤</div>
                    <div class="min-w-0">
                        <div class="font-bold text-xl truncate">{{ user.name }}</div>
                        <div class="text-gray-400 text-sm truncate">{{ user.email }}</div>
                        <span v-if="user.role === 'admin'" class="text-xs bg-gray-900 text-white px-2.5 py-1 rounded-lg mt-1.5 inline-block font-bold">Администратор</span>
                    </div>
                </div>
                <div class="sm:ml-auto flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <Link v-if="user.role === 'admin'" href="/admin" class="w-full sm:w-auto text-center px-5 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-bold hover:bg-gray-800 transition">Панель управления</Link>
                    <Link href="/logout" method="post" as="button" class="w-full sm:w-auto text-center px-5 py-2.5 border border-gray-200 text-gray-600 font-bold rounded-xl text-sm hover:bg-gray-50 transition">Выйти</Link>
                </div>
            </div>

            <h2 class="font-black text-xl mb-4 text-gray-900">Мои заказы</h2>

            <!-- Вкладки (дизайн 1-в-1 как в Admin/Orders) -->
            <div class="flex sm:inline-flex w-full sm:w-auto mb-6 p-1 bg-white border border-gray-100 rounded-xl shadow-sm">
                <button
                    @click="activeTab = 'active'"
                    :class="['flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-2.5 sm:py-2 rounded-lg text-sm font-bold transition',
                             activeTab === 'active' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg>
                    Активные
                </button>
                <button
                    @click="activeTab = 'completed'"
                    :class="['flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-2.5 sm:py-2 rounded-lg text-sm font-bold transition',
                             activeTab === 'completed' ? 'bg-emerald-500 text-white' : 'text-gray-500 hover:text-emerald-600 hover:bg-emerald-50']"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Завершённые
                </button>
                <button
                    @click="activeTab = 'cancelled'"
                    :class="['flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-2.5 sm:py-2 rounded-lg text-sm font-bold transition',
                             activeTab === 'cancelled' ? 'bg-red-500 text-white' : 'text-gray-500 hover:text-red-500 hover:bg-red-50']"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Отменённые
                </button>
            </div>

            <div v-if="filteredOrders.length > 0">
                <div class="hidden md:grid grid-cols-12 gap-4 px-5 text-xs font-black text-gray-400 uppercase tracking-wider mb-2">
                    <div class="col-span-2">Заказ</div>
                    <div class="col-span-5">Контакты</div>
                    <div class="col-span-3 text-center">Статус</div>
                    <div class="col-span-2">Сумма</div>
                </div>

                <div class="flex flex-col gap-3">
                    <Link
                        v-for="order in filteredOrders"
                        :key="order.id"
                        :href="`/orders/${order.uuid}`"
                        class="relative bg-white rounded-2xl border border-gray-100 group shadow-sm
                               transition duration-300 ease-out hover:shadow-md hover:-translate-y-1 will-change-transform antialiased
                               p-4 flex flex-col gap-2
                               md:grid md:grid-cols-12 md:gap-4 md:items-center md:flex-none"
                    >
                        <div class="md:col-span-2 flex items-start justify-between md:block">
                            <div>
                                <div class="font-black text-gray-900">#{{ order.id }}</div>
                                <div class="text-xs text-gray-400 mt-1">{{ formatDate(order.created_at) }}</div>
                            </div>
                            <div class="md:hidden font-black text-gray-900 text-sm">{{ formatPrice(order.total_price) }} ₽</div>
                        </div>

                        <div class="md:col-span-5">
                            <div class="text-sm font-bold text-gray-800">{{ order.phone }}</div>
                            <div class="text-xs text-gray-500 truncate">г. {{ order.city }}, ул. {{ order.street }}, {{ order.house }}</div>
                        </div>

                        <div class="md:col-span-3 md:flex md:justify-center">
                            <span :class="['inline-flex px-3 py-1 rounded-lg text-xs font-bold whitespace-nowrap', statusMap[order.status]?.color]">
                                {{ statusMap[order.status]?.label }}
                            </span>
                        </div>

                        <div class="hidden md:block md:col-span-2 font-black text-gray-900 whitespace-nowrap">
                            {{ formatPrice(order.total_price) }} ₽
                        </div>

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
                    </Link>
                </div>
            </div>

            <div v-else class="text-center py-16 text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
                <div class="text-5xl mb-3">📦</div>
                <div class="font-bold text-gray-500">
                    <template v-if="activeTab === 'active'">Активных заказов нет</template>
                    <template v-else-if="activeTab === 'completed'">Завершённых заказов нет</template>
                    <template v-else>Отменённых заказов нет</template>
                </div>
                <Link v-if="activeTab === 'active'" href="/" class="mt-4 inline-block px-6 py-2.5 font-bold bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700 transition">
                    Перейти в каталог
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>