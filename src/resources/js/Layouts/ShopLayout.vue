<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, provide } from 'vue'
import CartPopup from '@/Components/CartPopup.vue'
import Toast from '@/Components/Toast.vue'
import NotificationCenter from '@/Components/NotificationCenter.vue'

const page = usePage()
const toast = ref(null)
const notifications = ref([])
const bellOpen = ref(false)
const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

let _notifId = 0
function notify(message, type = 'success', opts = {}) {
    notifications.value.unshift({
        id: ++_notifId,
        message,
        type,
        href: opts.href ?? null,
        icon: opts.icon ?? null,
        read: false,
        createdAt: new Date(),
    })
    if (notifications.value.length > 50) notifications.value.length = 50
    toast.value?.add(message, type, opts)
}

function openBell() {
    bellOpen.value = true
    notifications.value.forEach(n => { n.read = true })
}

function removeNotification(id) {
    notifications.value = notifications.value.filter(n => n.id !== id)
}

function clearAllNotifications() {
    notifications.value = []
}

provide('toast', {
    success: (msg, opts = {}) => notify(msg, 'success', opts),
    error:   (msg, opts = {}) => notify(msg, 'error', opts),
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
                    notify(`Новый заказ #${event.order.id} на ${fmt(event.order.total_price)} ₽`, 'success', { icon: 'order', href: `/admin/orders/${event.order.id}` })
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
                            notify(`Новое сообщение в заказе #${id}`, 'success', { icon: 'message', href: `/admin/orders/${id}` })
                        break
                    case 'contacts_updated':
                        if (!onThisOrder)
                            notify(`Клиент обновил контакты заказа #${id}`, 'success', { icon: 'edit', href: `/admin/orders/${id}` })
                        break
                    case 'cancelled':
                        notify(`Заказ #${id} отменён клиентом`, 'error', { icon: 'cancel', href: `/admin/orders/${id}` })
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
                            notify(`Заказ #${order.id}: статус изменён на «${label}»`, 'success', { icon: 'status', href: `/orders/${order.uuid}` })
                        break
                    }
                    case 'contacts_updated_by_admin':
                        if (!onThisOrder)
                            notify(`Менеджер обновил контакты заказа #${order.id}`, 'success', { icon: 'edit', href: `/orders/${order.uuid}` })
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
                    notify(`Новый ответ по заказу #${event.order_number}`, 'success', { icon: 'message', href: `/orders/${event.order_uuid}` })
                }
            })

            // Изменение цены товара в корзине
            .listen('.CartPriceChanged', (event) => {
                const was  = fmt(event.old_price)
                const now  = fmt(event.new_price)
                const priceDown = event.new_price < event.old_price
                const type = priceDown ? 'success' : 'error'
                notify(`«${event.product_title}»: ${was} → ${now} ₽`, type, { icon: priceDown ? 'price_down' : 'price_up', href: '/cart' })
                router.reload({ only: ['cartItems', 'cartCount'], preserveScroll: true, preserveState: true })
            })
    }

}, { immediate: true })
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-40 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-[72px]">
                    <!-- Логотип -->
                    <Link href="/" class="flex items-center gap-2.5 font-black text-xl text-gray-900 tracking-tight group">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center text-lg shadow-sm group-hover:scale-105 transition-transform">⚙️</div>
                        <span>ЗПО <span class="text-blue-600">РиТ</span></span>
                    </Link>

                    <!-- Поиск -->
                    <form action="/" method="GET" class="hidden md:flex flex-1 max-w-xl mx-12">
                        <div class="relative w-full flex items-center">
                            <svg class="absolute left-4 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="search" name="q" placeholder="Поиск оборудования..." 
                                class="w-full pl-11 pr-4 py-2.5 rounded-2xl bg-gray-100/80 border-transparent focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-sm font-medium placeholder-gray-400"/>
                        </div>
                    </form>

                    <!-- Кнопки справа -->
                    <div class="flex items-center gap-3">
                        <button @click="cartOpen = true" class="relative flex items-center justify-center w-11 h-11 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[11px] font-bold rounded-full w-5 h-5 flex items-center justify-center shadow-sm border-2 border-white">{{ cartCount }}</span>
                        </button>
                        <!-- Колокольчик -->
                        <div v-if="user" class="relative">
                            <button
                                @click="bellOpen ? bellOpen = false : openBell()"
                                class="relative flex items-center justify-center w-11 h-11 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                                </svg>
                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -top-1 -right-1 bg-blue-500 text-white text-[11px] font-bold rounded-full w-5 h-5 flex items-center justify-center shadow-sm border-2 border-white"
                                >
                                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                                </span>
                            </button>
                    
                            <!-- Оверлей для закрытия по клику вне -->
                            <div v-if="bellOpen" class="fixed inset-0 z-40" @click="bellOpen = false" />
                        
                            <!-- Дропдаун -->
                            <Transition name="bell-drop">
                                <NotificationCenter
                                    v-if="bellOpen"
                                    :notifications="notifications"
                                    @remove="removeNotification"
                                    @remove-all="clearAllNotifications"
                                />
                            </Transition>
                        </div>
                        <template v-if="user">
                            <Link v-if="user.role === 'admin'" href="/admin" class="hidden sm:flex px-4 py-2.5 rounded-xl bg-gray-900 text-white text-sm font-bold hover:bg-gray-800 transition shadow-sm">Админка</Link>
                            <Link href="/profile" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-50 text-blue-600 text-sm font-bold hover:bg-blue-100 transition">
                                <div class="w-6 h-6 bg-blue-200/50 rounded-full flex items-center justify-center text-xs">👤</div>
                                <span class="hidden sm:inline">Профиль</span>
                            </Link>
                        </template>
                        <template v-else>
                            <Link href="/login" class="px-4 py-2.5 rounded-xl text-gray-600 text-sm font-bold hover:bg-gray-100 transition">Войти</Link>
                            <Link href="/register" class="px-4 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 shadow-sm transition">Регистрация</Link>
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

<style scoped>
.bell-drop-enter-active { transition: all 0.22s cubic-bezier(0.34, 1.4, 0.64, 1); }
.bell-drop-leave-active { transition: all 0.15s ease-in; }
.bell-drop-enter-from   { opacity: 0; transform: translateY(-10px) scale(0.96); }
.bell-drop-leave-to     { opacity: 0; transform: translateY(-6px) scale(0.97); }
</style>