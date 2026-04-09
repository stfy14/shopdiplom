<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    orders: Array,
})

let pollInterval = null

onMounted(() => {
    pollInterval = setInterval(() => {
        router.reload({ preserveScroll: true })
    }, 5000)
})

onUnmounted(() => {
    clearInterval(pollInterval)
})

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
    return new Date(dt).toLocaleDateString('ru-RU')
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow-sm mb-6">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <span class="font-bold text-xl">📦 Все заказы</span>
                <Link href="/admin" class="px-4 py-2 border border-gray-300 rounded-xl text-sm hover:bg-gray-50">
                    ← Назад к товарам
                </Link>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-6">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Заказ</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Клиент</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Контакты</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Сумма</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Дата</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Статус</th>
                            <th class="text-right px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="order in orders"
                            :key="order.id"
                            class="hover:bg-gray-50 transition cursor-pointer"
                            @click="$inertia.visit(`/admin/orders/${order.id}`)"
                        >
                            <td class="px-6 py-4">
                                <div class="font-bold">#{{ order.id }}</div>
                                <div class="text-xs text-gray-400 font-mono">{{ order.uuid.substring(0, 8) }}...</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-sm">
                                        👤
                                    </div>
                                    {{ order.user?.name }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">{{ order.phone }}</div>
                                <div class="text-xs text-gray-400 truncate max-w-48">{{ order.address }}</div>
                            </td>
                            <td class="px-6 py-4 font-bold">{{ formatPrice(order.total_price) }} ₽</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(order.created_at) }}</td>
                            <td class="px-6 py-4">
                                <span :class="['px-2 py-1 rounded-full text-xs font-bold', statusMap[order.status]?.color]">
                                    {{ statusMap[order.status]?.label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link
                                    :href="`/admin/orders/${order.id}`"
                                    class="px-3 py-1.5 border rounded-lg text-xs hover:bg-gray-50 transition"
                                    @click.stop
                                >
                                    Открыть →
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>