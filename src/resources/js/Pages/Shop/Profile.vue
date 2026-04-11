<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link, router } from '@inertiajs/vue3' // ДОБАВИТЬ router
import { onMounted, onUnmounted } from 'vue'   // ДОБАВИТЬ хуки

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

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price)
}

function formatDate(dt) {
    return new Date(dt).toLocaleDateString('ru-RU')
}

onMounted(() => {
    window.Echo.private(`user.${props.user.id}`)
        .listen('.OrderUpdated', () => {
            router.reload({ only:['orders'], preserveScroll: true, preserveState: true })
        })
})

onUnmounted(() => {
    window.Echo.leave(`private-user.${props.user.id}`)
})

</script>

<template>
    <ShopLayout>
        <div class="max-w-3xl mx-auto">
            <!-- Шапка профиля -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6 flex items-center gap-4">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-2xl">
                    👤
                </div>
                <div>
                    <div class="font-bold text-xl">{{ user.name }}</div>
                    <div class="text-gray-400 text-sm">{{ user.email }}</div>
                    <span v-if="user.role === 'admin'" class="text-xs bg-gray-900 text-white px-2 py-0.5 rounded-full mt-1 inline-block">
                        Администратор
                    </span>
                </div>
                <div class="ml-auto flex gap-2">
                    <Link
                        v-if="user.role === 'admin'"
                        href="/admin"
                        class="px-4 py-2 bg-gray-900 text-white rounded-xl text-sm font-medium hover:bg-gray-700 transition"
                    >
                        Панель управления
                    </Link>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="px-4 py-2 border border-gray-200 text-gray-500 rounded-xl text-sm hover:bg-gray-50 transition"
                    >
                        Выйти
                    </Link>
                </div>
            </div>

            <!-- Заказы -->
            <h2 class="font-bold text-lg mb-4">Мои заказы</h2>

            <div v-if="orders.length > 0" class="flex flex-col gap-3">
                <Link
                    v-for="order in orders"
                    :key="order.id"
                    :href="`/orders/${order.uuid}`"
                    class="bg-white rounded-2xl shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition"
                >
                    <div>
                        <div class="font-bold">#{{ order.id }}</div>
                        <div class="text-gray-400 text-xs font-mono">{{ order.uuid.substring(0, 8) }}...</div>
                    </div>

                    <div class="flex-grow">
                        <div class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</div>
                        <div class="text-sm text-gray-500">{{ order.address }}</div>
                    </div>

                    <div class="text-right">
                        <div class="font-bold text-lg">{{ formatPrice(order.total_price) }} ₽</div>
                        <span :class="['text-xs px-2 py-1 rounded-full font-medium', statusMap[order.status]?.color]">
                            {{ statusMap[order.status]?.label }}
                        </span>
                    </div>
                </Link>
            </div>

            <div v-else class="text-center py-16 text-gray-400">
                <div class="text-5xl mb-3">📦</div>
                <div>У вас пока нет заказов</div>
                <Link href="/" class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700 transition">
                    Перейти в каталог
                </Link>
            </div>
        </div>
    </ShopLayout>
</template>