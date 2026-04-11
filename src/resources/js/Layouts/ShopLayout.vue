<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, provide } from 'vue'
import CartPopup from '@/Components/CartPopup.vue'
import Toast from '@/Components/Toast.vue'

const page = usePage()
const toast = ref(null)

provide('toast', {
    success: (msg) => toast.value?.add(msg, 'success'),
    error: (msg) => toast.value?.add(msg, 'error'),
})

watch(() => page.props.flash?.cart_added, (val) => {
    if (val) toast.value?.add(`${val} добавлен в корзину`, 'success')
})

const user = computed(() => page.props.auth.user)
const cartCount = computed(() => page.props.cartCount ?? 0)
const cartItems = computed(() => page.props.cartItems ??[])
const cartOpen = ref(false)

// НЕУБИВАЕМАЯ ПОДПИСКА НА СОКЕТЫ
watch(() => user.value, (newUser, oldUser) => {
    // Отписываемся от старых каналов, если сменился юзер (или вышел)
    if (oldUser) {
        window.Echo.leave(`user.${oldUser.id}`);
        if (oldUser.role === 'admin') window.Echo.leave('admin-notifications');
    }

    if (newUser) {
        // Канал Админа
        if (newUser.role === 'admin') {
            window.Echo.private('admin-notifications')
                .listen('.NewOrderPlaced', (event) => {
                    if (event.order) toast.value?.add(`Поступил новый заказ #${event.order.id}!`, 'success');
                    router.reload({ only:['adminNotifications'], preserveScroll: true, preserveState: true });
                })
                .listen('.OrderUpdated', (event) => {
                    if (event.order?.has_unseen_activity) {
                        toast.value?.add(`Новая активность в заказе #${event.order.id}`, 'success');
                    }
                    router.reload({ only:['adminNotifications'], preserveScroll: true, preserveState: true });
                });
        }

        // Канал Пользователя
        if (newUser.role === 'user') {
            window.Echo.private(`user.${newUser.id}`)
                .listen('.OrderUpdated', (event) => {
                    if (!event.order) return;
                    const statusMap = {
                        new: 'Новый', processing: 'В обработке',
                        shipped: 'Отправлен', cancelled: 'Отменён', cancelled_by_user: 'Отменён вами',
                    };
                    const statusName = statusMap[event.order.status] || event.order.status;
                    
                    // Уведомляем, только если юзер не сидит прямо сейчас внутри этого заказа
                    if (!window.location.pathname.includes(`/orders/${event.order.uuid}`)) {
                        toast.value?.add(`Заказ #${event.order.id}: статус изменен на «${statusName}»`, 'success');
                    }
                });
        }
    }
}, { immediate: true });
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <nav class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <Link href="/" class="flex items-center gap-2 font-bold text-xl text-gray-900">
                        <span class="text-blue-600">⚙️</span> ЗПО РиТ
                    </Link>

                    <form action="/" method="GET" class="hidden md:flex flex-1 max-w-md mx-8">
                        <div class="relative w-full">
                            <input type="search" name="q" placeholder="Поиск товаров..." class="w-full pl-4 pr-10 py-2 rounded-full border border-gray-200 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</button>
                        </div>
                    </form>

                    <div class="flex items-center gap-3">
                        <button @click="cartOpen = true" class="relative flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 hover:bg-gray-200 transition text-sm font-medium">
                            🛒 Корзина
                            <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ cartCount }}</span>
                        </button>
                        <template v-if="user">
                            <Link v-if="user.role === 'admin'" href="/admin" class="px-4 py-2 rounded-full bg-gray-900 text-white text-sm font-medium hover:bg-gray-700 transition">Админка</Link>
                            <Link href="/profile" class="px-4 py-2 rounded-full bg-gray-900 text-white text-sm font-medium hover:bg-gray-700 transition">👤 Профиль</Link>
                        </template>
                        <template v-else>
                            <Link href="/login" class="px-4 py-2 rounded-full border border-blue-600 text-blue-600 text-sm font-medium hover:bg-blue-50 transition">Войти</Link>
                            <Link href="/register" class="px-4 py-2 rounded-full bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">Регистрация</Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full">
            <slot />
        </main>
        <footer class="bg-white border-t mt-16"><div class="max-w-7xl mx-auto px-4 py-8 text-center text-gray-400 text-sm">© 2025 ЗПО РиТ — Грузоподъёмное оборудование</div></footer>
        <CartPopup :show="cartOpen" :items="cartItems" @close="cartOpen = false" />
        <Toast ref="toast" />
    </div>
</template>