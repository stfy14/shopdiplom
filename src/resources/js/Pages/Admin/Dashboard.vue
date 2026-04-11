<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });
defineProps({ stats: Object, recentOrders: Array });

function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price); }
function formatDate(dt) { return new Date(dt).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }); }

const statusMap = {
    new: { label: 'Новый', color: 'bg-blue-100 text-blue-700' },
    processing: { label: 'В обработке', color: 'bg-yellow-100 text-yellow-700' },
    shipped: { label: 'Отправлен', color: 'bg-green-100 text-green-700' },
    cancelled: { label: 'Отмена (Маг.)', color: 'bg-red-100 text-red-700' },
    cancelled_by_user: { label: 'Отмена (Клиент)', color: 'bg-gray-100 text-gray-700' },
};
</script>

<template>
    <div>
        <h1 class="text-2xl font-black mb-6 text-gray-900">Обзор системы</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="text-gray-400 text-sm font-bold uppercase mb-2">Новых заказов</div>
                <div class="text-4xl font-black text-blue-600">{{ stats.newOrders }}</div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="text-gray-400 text-sm font-bold uppercase mb-2">Выручка</div>
                <div class="text-4xl font-black text-green-600">{{ formatPrice(stats.totalRevenue) }} ₽</div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="text-gray-400 text-sm font-bold uppercase mb-2">Активных товаров</div>
                <div class="text-4xl font-black text-gray-800">{{ stats.totalProducts }}</div>
                <div v-if="stats.outOfStock > 0" class="text-red-500 text-xs mt-1 font-bold">Нет в наличии: {{ stats.outOfStock }} шт.</div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="text-gray-400 text-sm font-bold uppercase mb-2">Пользователей</div>
                <div class="text-4xl font-black text-gray-800">{{ stats.totalUsers }}</div>
            </div>
        </div>

        <h2 class="text-xl font-black mb-4 text-gray-900">Последние заказы</h2>

        <!-- Заголовок — только md+ -->
        <div class="hidden md:grid grid-cols-12 gap-4 px-5 text-xs font-black text-gray-400 uppercase tracking-wider mb-2">
            <div class="col-span-2">Заказ</div>
            <div class="col-span-5">Клиент</div>
            <div class="col-span-3 text-center">Статус</div>
            <div class="col-span-2">Сумма</div>
        </div>

        <div class="flex flex-col gap-3">
            <div v-if="recentOrders.length === 0" class="text-center py-16 text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
                Пока нет ни одного заказа
            </div>
            
            <Link
                v-for="order in recentOrders"
                :key="order.id"
                :href="`/admin/orders/${order.id}`"
                class="relative bg-white rounded-2xl shadow-sm border border-gray-100 group
                       transition duration-300 ease-out hover:shadow-md hover:-translate-y-1 will-change-transform antialiased
                       p-4 flex flex-col gap-2
                       md:grid md:grid-cols-12 md:gap-4 md:items-center md:flex-none"
            >
                <!-- Заказ + сумма в одну строку на мобильном -->
                <div class="md:col-span-2 flex items-start justify-between md:block">
                    <div>
                        <div class="font-black text-gray-900">#{{ order.id }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ formatDate(order.created_at) }}</div>
                    </div>
                    <div class="md:hidden font-black text-gray-900 text-sm">{{ formatPrice(order.total_price) }} ₽</div>
                </div>

                <!-- Клиент -->
                <div class="md:col-span-5 font-bold text-gray-900 truncate">{{ order.user?.name }}</div>

                <!-- Статус -->
                <div class="md:col-span-3 md:flex md:justify-center">
                    <span :class="['inline-flex px-3 py-1 rounded-lg text-xs font-bold whitespace-nowrap', statusMap[order.status]?.color]">
                        {{ statusMap[order.status]?.label }}
                    </span>
                </div>

                <!-- Сумма — только md+ -->
                <div class="hidden md:block md:col-span-2 font-black text-gray-900 whitespace-nowrap">
                    {{ formatPrice(order.total_price) }} ₽
                </div>
            </Link>
        </div>
    </div>
</template>