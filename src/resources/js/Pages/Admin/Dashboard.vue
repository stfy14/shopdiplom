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
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Обзор системы</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="text-gray-400 text-sm font-bold uppercase mb-2">Новых заказов</div>
                <div class="text-3xl font-black text-blue-600">{{ stats.newOrders }}</div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="text-gray-400 text-sm font-bold uppercase mb-2">Выручка</div>
                <div class="text-3xl font-black text-green-600">{{ formatPrice(stats.totalRevenue) }} ₽</div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="text-gray-400 text-sm font-bold uppercase mb-2">Активных товаров</div>
                <div class="text-3xl font-black text-gray-800">{{ stats.totalProducts }}</div>
                <div v-if="stats.outOfStock > 0" class="text-red-500 text-xs mt-1 font-medium">Нет в наличии: {{ stats.outOfStock }} шт.</div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
                <div class="text-gray-400 text-sm font-bold uppercase mb-2">Пользователей</div>
                <div class="text-3xl font-black text-gray-800">{{ stats.totalUsers }}</div>
            </div>
        </div>

        <h2 class="text-lg font-bold mb-4 text-gray-800">Последние заказы</h2>
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Заказ</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Клиент</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Сумма</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Статус</th>
                        <th class="text-right px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-if="recentOrders.length === 0">
                        <td colspan="5" class="text-center py-8 text-gray-400">Нет заказов</td>
                    </tr>
                    <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800">#{{ order.id }}</div>
                            <div class="text-xs text-gray-400">{{ formatDate(order.created_at) }}</div>
                        </td>
                        <td class="px-6 py-4 font-medium">{{ order.user?.name }}</td>
                        <td class="px-6 py-4 font-bold">{{ formatPrice(order.total_price) }} ₽</td>
                        <td class="px-6 py-4">
                            <span :class="['px-2 py-1 rounded-full text-xs font-bold', statusMap[order.status]?.color]">
                                {{ statusMap[order.status]?.label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <Link :href="`/admin/orders/${order.id}`" class="px-3 py-1.5 border border-blue-200 text-blue-600 rounded-lg text-xs hover:bg-blue-50 transition font-medium">
                                Открыть
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>