<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    orders: Array,
    user: Object,
})

const statusMap = {
    new:               { label: 'Новый',        color: 'bg-blue-100 text-blue-700' },
    processing:        { label: 'В обработке',   color: 'bg-yellow-100 text-yellow-700' },
    shipped:           { label: 'Отправлен',     color: 'bg-green-100 text-green-700' },
    cancelled:         { label: 'Отменён',       color: 'bg-red-100 text-red-700' },
    cancelled_by_user: { label: 'Отменён вами',  color: 'bg-gray-100 text-gray-700' },
}

function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }
function formatDate(dt) { return new Date(dt).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }
</script>

<template>
    <ShopLayout>
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6 flex items-center gap-4">
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
                <div class="grid grid-cols-12 gap-4 px-5 text-xs font-bold text-gray-400 uppercase mb-2">
                    <div class="col-span-3">Заказ</div>
                    <div class="col-span-4">Контакты</div>
                    <div class="col-span-2">Сумма</div>
                    <div class="col-span-3">Статус</div>
                </div>

                <div class="flex flex-col gap-3">
                    <Link v-for="order in orders" :key="order.id" :href="`/orders/${order.uuid}`" class="relative grid grid-cols-12 gap-4 items-center bg-white rounded-2xl shadow-sm p-5 transition hover:shadow-md hover:bg-gray-50">
                        <div class="col-span-3">
                            <div class="font-extrabold text-gray-800">#{{ order.id }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ formatDate(order.created_at) }}</div>
                        </div>
                        <div class="col-span-4 pr-4">
                            <div class="text-sm font-medium">{{ order.phone }}</div>
                            <div class="text-xs text-gray-500 truncate" :title="`г. ${order.city}, ул. ${order.street}, ${order.house}`">г. {{ order.city }}, ул. {{ order.street }}, {{ order.house }}</div>
                        </div>
                        <div class="col-span-2 font-bold text-gray-800">{{ formatPrice(order.total_price) }} ₽</div>
                        <div class="col-span-3">
                            <span :class="['px-2 py-1 rounded-full text-xs font-bold whitespace-nowrap', statusMap[order.status]?.color]">
                                {{ statusMap[order.status]?.label }}
                            </span>
                        </div>

                        <!-- Иконки-индикаторы для пользователя (справа сверху карточки) -->
                        <div class="absolute -top-2 -right-2 flex items-center gap-1">
                            <div v-if="order.status === 'new'" class="w-7 h-7 rounded-full bg-blue-500 text-white flex items-center justify-center shadow-sm text-[12px]" title="Новый заказ">📦</div>
                            <div v-if="order.unread_messages_count > 0" class="w-7 h-7 rounded-full bg-yellow-400 text-yellow-900 flex items-center justify-center shadow-sm text-[12px]" title="Есть непрочитанные сообщения от менеджера">💬</div>
                        </div>

                        <!-- Кнопка "Открыть" -->
                        <div @click.stop class="absolute top-1/2 -translate-y-1/2 right-5">
                            <Link :href="`/orders/${order.uuid}`" class="px-4 py-2 border rounded-xl text-xs font-medium hover:shadow-sm transition bg-white">
                                Открыть
                            </Link>
                        </div>
                    </Link>
                </div>
            </div>

            <div v-else class="text-center py-16 text-gray-400">
                <div class="text-5xl mb-3">📦</div>
                <div>У вас пока нет заказов</div>
                <Link href="/" class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700 transition">Перейти в каталог</Link>
            </div>
        </div>
    </ShopLayout>
</template>