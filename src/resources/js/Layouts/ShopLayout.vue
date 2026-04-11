<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, provide } from 'vue'
import CartPopup from '@/Components/CartPopup.vue'
import Toast from '@/Components/Toast.vue'

const page = usePage()
const toast = ref(null)

provide('toast', {
    success: (msg) => toast.value?.add(msg, 'success'),
    error:   (msg) => toast.value?.add(msg, 'error'),
})

watch(() => page.props.flash?.cart_added, (val) => {
    if (val) toast.value?.add(`${val} добавлен в корзину`, 'success')
})

const user        = computed(() => page.props.auth.user)
const cartCount   = computed(() => page.props.cartCount ?? 0)
const cartItems   = computed(() => page.props.cartItems ?? [])
const cartOpen    = ref(false)
// Текущий компонент — для умной перезагрузки нужных пропсов
const currentPage = computed(() => page.component)

function fmt(p) {
    return new Intl.NumberFormat('ru-RU').format(p)
}

const statusLabels = {
    new:               'Новый',
    processing:        'В обработке',
    shipped:           'Отправлен',
    cancelled:         'Отменён',
    cancelled_by_user: 'Отменён клиентом',
}

// ---------- ПОДПИСКИ ----------
watch(() => user.value, (newUser, oldUser) => {
    if (oldUser) {
        window.Echo.leave(`user.${oldUser.id}`)
        if (oldUser.role === 'admin') window.Echo.leave('admin-notifications')
    }

    if (!newUser) return

    // ===== АДМИНИСТРАТОР =====
    if (newUser.role === 'admin') {
        window.Echo.private('admin-notifications')

            .listen('.NewOrderPlaced', (event) => {
                if (event.order) {
                    toast.value?.add(`📦 Новый заказ #${event.order.id} на ${fmt(event.order.total_price)} ₽`, 'success')
                }
                const extra = currentPage.value === 'Admin/Orders' ? ['orders'] : []
                router.reload({ only: ['adminNotifications', ...extra], preserveScroll: true, preserveState: true })
            })

            .listen('.OrderUpdated', (event) => {
                const id = event.order?.id
                const pg = currentPage.value
                const onThisOrder = pg === 'Admin/OrderView' &&
                    window.location.pathname.includes(`/admin/orders/${id}`)

                switch (event.type) {
                    case 'new_message':
                        if (!onThisOrder)
                            toast.value?.add(`💬 Новое сообщение в заказе #${id}`, 'success')
                        break
                    case 'contacts_updated':
                        if (!onThisOrder)
                            toast.value?.add(`✏️ Клиент обновил контакты заказа #${id}`, 'success')
                        break
                    case 'cancelled':
                        toast.value?.add(`❌ Заказ #${id} отменён клиентом`, 'error')
                        break
                    // status_change, contacts_updated_by_admin, read — без тоста для админа
                }

                const extra = []
                if (pg === 'Admin/Orders')    extra.push('orders')
                if (pg === 'Admin/OrderView') extra.push('order')
                router.reload({ only: ['adminNotifications', ...extra], preserveScroll: true, preserveState: true })
            })

            .listen('.ProductUpdated', () => {
                if (currentPage.value === 'Admin/Products') {
                    router.reload({ only: ['products'], preserveScroll: true, preserveState: true })
                }
            })
    }

    // ===== ПОЛЬЗОВАТЕЛЬ =====
    if (newUser.role === 'user') {
        window.Echo.private(`user.${newUser.id}`)

            .listen('.OrderUpdated', (event) => {
                const order = event.order
                if (!order) return

                const pg          = currentPage.value
                const onThisOrder = pg === 'Shop/Order' &&
                    window.location.pathname.includes(`/orders/${order.uuid}`)

                switch (event.type) {
                    case 'status_change': {
                        const label = statusLabels[order.status] ?? order.status
                        if (!onThisOrder)
                            toast.value?.add(`📦 Заказ #${order.id}: статус → «${label}»`, 'success')
                        break
                    }
                    case 'contacts_updated_by_admin':
                        if (!onThisOrder)
                            toast.value?.add(`✏️ Менеджер обновил контакты заказа #${order.id}`, 'success')
                        break
                    // new_message, contacts_updated, cancelled — пользователь сам инициировал
                }

                // Обновляем список заказов на странице профиля
                if (pg === 'Shop/Profile') {
                    router.reload({ only: ['orders'], preserveScroll: true, preserveState: true })
                }
                // Shop/Order.vue сам обновляет через order.{id} канал
            })

            // Сообщение от администратора — уведомляем если не на странице этого заказа
            .listen('.NewOrderMessage', (event) => {
                if (event.sender_role !== 'admin') return
                const onThisOrder = currentPage.value === 'Shop/Order' &&
                    window.location.pathname.includes(`/orders/${event.order_uuid}`)
                if (!onThisOrder) {
                    toast.value?.add(`💬 Новый ответ по заказу #${event.order_number}`, 'success')
                }
            })

            // Изменение цены товара в корзине
            .listen('.CartPriceChanged', (event) => {
                const was  = fmt(event.old_price)
                const now  = fmt(event.new_price)
                const icon = event.new_price < event.old_price ? '📉' : '📈'
                const type = event.new_price < event.old_price ? 'success' : 'error'
                toast.value?.add(`${icon} «${event.product_title}»: ${was} → ${now} ₽`, type)
                router.reload({ only: ['cartItems', 'cartCount'], preserveScroll: true, preserveState: true })
            })
    }

}, { immediate: true })
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

        <footer class="bg-white border-t mt-16">
            <div class="max-w-7xl mx-auto px-4 py-8 text-center text-gray-400 text-sm">
                © 2025 ЗПО РиТ — Грузоподъёмное оборудование
            </div>
        </footer>

        <CartPopup :show="cartOpen" :items="cartItems" @close="cartOpen = false" />
        <Toast ref="toast" />
    </div>
</template>