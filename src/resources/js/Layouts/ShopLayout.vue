<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, provide, onMounted, onUnmounted } from 'vue'
import CartPopup from '@/Components/CartPopup.vue'
import Toast from '@/Components/Toast.vue'
import NotificationCenter from '@/Components/NotificationCenter.vue'
import axios from 'axios'

const page = usePage()
const toast = ref(null)

const localNotifications = ref([])
const bellOpen = ref(false)
const cartOpen = ref(false)
const mobileSearchOpen = ref(false)
const dropdownsWrapper = ref(null) 

// Отслеживаем, какой виджет активен, чтобы давать ему наивысший z-index
const activeWidget = ref(null)

const unreadCount = computed(() => localNotifications.value.filter(n => !n.read).length)

watch(() => page.props.notifications, (newVal) => {
    localNotifications.value = newVal ? [...newVal] :[]
}, { immediate: true, deep: true })

onMounted(() => document.addEventListener('click', handleGlobalClick))
onUnmounted(() => document.removeEventListener('click', handleGlobalClick))

function handleGlobalClick(e) {
    if (dropdownsWrapper.value && !dropdownsWrapper.value.contains(e.target)) {
        bellOpen.value = false
        cartOpen.value = false
        mobileSearchOpen.value = false
    }
}

// Взаимоисключающее открытие окон с передачей высшего z-index
function toggleCart() {
    if (cartOpen.value) {
        cartOpen.value = false
    } else {
        activeWidget.value = 'cart'
        cartOpen.value = true
        bellOpen.value = false
        mobileSearchOpen.value = false
    }
}

function toggleSearch() {
    if (mobileSearchOpen.value) {
        mobileSearchOpen.value = false
    } else {
        activeWidget.value = 'search'
        mobileSearchOpen.value = true
        cartOpen.value = false
        bellOpen.value = false
    }
}

function openBell() {
    if (bellOpen.value) {
        bellOpen.value = false
        return
    }
    activeWidget.value = 'bell'
    bellOpen.value = true
    cartOpen.value = false
    mobileSearchOpen.value = false

    if (unreadCount.value > 0) {
        localNotifications.value.forEach(n => n.read = true)
        axios.post('/notifications/mark-read').then(() => {
            router.reload({ only: ['notifications', 'adminNotifications'] })
        })
    }
}

function removeNotification(id) {
    localNotifications.value = localNotifications.value.filter(n => n.id !== id)
    axios.delete(`/notifications/${id}`).then(() => {
        router.reload({ only:['notifications', 'adminNotifications'] })
    })
}

function clearAllNotifications() {
    const items = [...localNotifications.value]
    items.reverse().forEach((notif, index) => {
        setTimeout(() => {
            localNotifications.value = localNotifications.value.filter(n => n.id !== notif.id)
        }, index * 200)
    })
    
    setTimeout(() => {
        axios.delete('/notifications/all').then(() => {
            router.reload({ only: ['notifications', 'adminNotifications'] })
        })
    }, (items.length * 200) + 600)
}

provide('toast', {
    success: (msg, opts = {}) => toast.value?.add(msg, 'success', opts),
    error:   (msg, opts = {}) => toast.value?.add(msg, 'error', opts),
})

watch(() => page.props.flash?.cart_added, (val) => {
    if (val) toast.value?.add(`${val} добавлен в корзину`, 'success')
})

const user        = computed(() => page.props.auth.user)
const cartCount   = computed(() => page.props.cartCount ?? 0)
const currentPage = computed(() => page.component)

function fmt(p) { return new Intl.NumberFormat('ru-RU').format(p) }

const statusLabels = {
    new: 'Новый', processing: 'В обработке', shipped: 'Отправлен', 
    cancelled: 'Отменён', cancelled_by_user: 'Отменён клиентом',
}

watch(() => user.value, (newUser, oldUser) => {
    if (oldUser) {
        window.Echo.leave(`user.${oldUser.id}`)
        if (oldUser.role === 'admin') window.Echo.leave('admin-notifications')
    }

    if (!newUser) return

    if (newUser.role === 'admin') {
        window.Echo.private('admin-notifications')
            .listen('.NewOrderPlaced', (event) => {
                if (event.order) toast.value?.add(`Новый заказ #${event.order.id} на ${fmt(event.order.total_price)} ₽`, 'success', { icon: 'order', href: `/admin/orders/${event.order.id}` })
                router.reload({ only:['notifications', 'adminNotifications', 'orders'], preserveScroll: true, preserveState: true })
            })
            .listen('.OrderUpdated', (event) => {
                const id = event.order?.id
                const pg = currentPage.value
                const onThisOrder = pg === 'Admin/OrderView' && window.location.pathname.includes(`/admin/orders/${id}`)

                if (event.type === 'new_message' && !onThisOrder) toast.value?.add(`Новое сообщение в заказе #${id}`, 'success', { icon: 'message', href: `/admin/orders/${id}` })
                if (event.type === 'contacts_updated' && !onThisOrder) toast.value?.add(`Клиент обновил контакты заказа #${id}`, 'success', { icon: 'edit', href: `/admin/orders/${id}` })
                if (event.type === 'cancelled') toast.value?.add(`Заказ #${id} отменён клиентом`, 'error', { icon: 'cancel', href: `/admin/orders/${id}` })

                router.reload({ only: ['notifications', 'adminNotifications', 'orders', 'order'], preserveScroll: true, preserveState: true })
            })
            .listen('.ProductUpdated', () => {
                if (currentPage.value === 'Admin/Products') router.reload({ only: ['products'], preserveScroll: true, preserveState: true })
            })
    }

    if (newUser.role === 'user') {
        window.Echo.private(`user.${newUser.id}`)
            .listen('.OrderUpdated', (event) => {
                const order = event.order
                if (!order) return
                const pg = currentPage.value
                const onThisOrder = pg === 'Shop/Order' && window.location.pathname.includes(`/orders/${order.uuid}`)

                if (event.type === 'status_change' && !onThisOrder) toast.value?.add(`Заказ #${order.id}: статус изменён на «${statusLabels[order.status] ?? order.status}»`, 'success', { icon: 'status', href: `/orders/${order.uuid}` })
                if (event.type === 'contacts_updated_by_admin' && !onThisOrder) toast.value?.add(`Менеджер обновил контакты заказа #${order.id}`, 'success', { icon: 'edit', href: `/orders/${order.uuid}` })

                router.reload({ only: ['notifications', 'orders'], preserveScroll: true, preserveState: true })
            })
            .listen('.NewOrderMessage', (event) => {
                if (event.sender_role !== 'admin') return
                const onThisOrder = currentPage.value === 'Shop/Order' && window.location.pathname.includes(`/orders/${event.order_uuid}`)
                if (!onThisOrder) toast.value?.add(`Новый ответ по заказу #${event.order_number}`, 'success', { icon: 'message', href: `/orders/${event.order_uuid}` })
                
                router.reload({ only: ['notifications'], preserveScroll: true, preserveState: true })
            })
            .listen('.CartPriceChanged', (event) => {
                const priceDown = event.new_price < event.old_price
                toast.value?.add(`«${event.product_title}»: ${fmt(event.old_price)} → ${fmt(event.new_price)} ₽`, priceDown ? 'success' : 'error', { icon: priceDown ? 'price_down' : 'price_up', href: '/cart' })
                
                router.reload({ only:['notifications', 'cartItems', 'cartCount'], preserveScroll: true, preserveState: true })
            })
    }
}, { immediate: true })
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Навбар -->
        <nav class="bg-white shadow-sm sticky top-0 z-40 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16 sm:h-[72px]">
                    
                    <Link href="/" class="flex items-center gap-2.5 font-black text-lg sm:text-xl text-gray-900 tracking-tight group flex-shrink-0">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center text-lg shadow-sm group-hover:scale-105 transition-transform">⚙️</div>
                        <span class="hidden sm:inline">ЗПО <span class="text-blue-600">РиТ</span></span>
                    </Link>

                    <!-- Поиск (Десктоп) -->
                    <form action="/" method="GET" class="hidden md:flex flex-1 max-w-xl mx-8 lg:mx-12">
                        <div class="relative w-full flex items-center">
                            <svg class="absolute left-4 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="search" name="q" placeholder="Поиск оборудования..." 
                                class="w-full pl-11 pr-4 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold transition shadow-sm placeholder-gray-400"/>
                        </div>
                    </form>

                    <!-- Правый блок кнопок и виджетов -->
                    <div class="flex items-center gap-1.5 sm:gap-3 relative" ref="dropdownsWrapper">
                        
                        <!-- Поиск (Только мобилка) -->
                        <button @click="toggleSearch" class="md:hidden relative flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>

                        <button @click.stop="toggleCart" class="relative flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] sm:text-[11px] font-bold rounded-full w-4 h-4 sm:w-5 sm:h-5 flex items-center justify-center shadow-sm border-2 border-white">{{ cartCount }}</span>
                        </button>

                        <div v-if="user" class="relative">
                            <button @click.stop="openBell" class="relative flex items-center justify-center w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                                </svg>
                                <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 bg-blue-500 text-white text-[10px] sm:text-[11px] font-bold rounded-full w-4 h-4 sm:w-5 sm:h-5 flex items-center justify-center shadow-sm border-2 border-white">
                                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                                </span>
                            </button>
                        </div>

                        <template v-if="user">
                            <Link v-if="user.role === 'admin'" href="/admin" class="hidden lg:flex px-4 py-2.5 rounded-xl bg-gray-900 text-white text-sm font-bold hover:bg-gray-800 transition shadow-sm">Админка</Link>
                            <Link href="/profile" class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition text-base sm:text-lg" title="Профиль">
                                👤
                            </Link>
                        </template>
                        <template v-else>
                            <Link href="/login" class="hidden sm:flex px-4 py-2.5 rounded-xl text-gray-600 text-sm font-bold hover:bg-gray-100 transition">Войти</Link>
                            <Link href="/register" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 shadow-sm transition">Вход</Link>
                        </template>

                        <!-- Плавающий поиск для мобильных -->
                        <Transition name="popup-drop">
                            <div v-if="mobileSearchOpen" :class="activeWidget === 'search' ? 'z-50' : 'z-40'" class="fixed sm:hidden left-4 right-4 top-[76px] bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-gray-100 p-2 cursor-default" @click.stop>
                                <form action="/" method="GET" class="relative w-full flex items-center">
                                    <svg class="absolute left-4 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <input type="search" name="q" placeholder="Поиск оборудования..." 
                                        class="w-full pl-11 pr-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-[15px] font-bold transition shadow-sm placeholder-gray-400"/>
                                </form>
                            </div>
                        </Transition>

                        <!-- Корзина и Уведомления с динамическим z-index -->
                        <Transition name="popup-drop">
                            <CartPopup v-if="cartOpen" @close="cartOpen = false" :class="activeWidget === 'cart' ? 'z-50' : 'z-40'" />
                        </Transition>
                        
                        <Transition name="popup-drop">
                            <NotificationCenter v-if="bellOpen" :notifications="localNotifications" @remove="removeNotification" @remove-all="clearAllNotifications" @close="bellOpen = false" :class="activeWidget === 'bell' ? 'z-50' : 'z-40'" />
                        </Transition>

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

        <Toast ref="toast" />
    </div>
</template>

<style>
.popup-drop-enter-active {
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.popup-drop-leave-active {
    transition: all 0.2s ease-in-out;
}
.popup-drop-enter-from,
.popup-drop-leave-to {
    opacity: 0;
    transform: translateY(-12px) scale(0.96);
}
@media (min-width: 640px) {
    .popup-drop-enter-active, .popup-drop-leave-active {
        transform-origin: top right;
    }
}
</style>