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

                    <!-- Поиск (Десктоп) — иконка справа как кнопка -->
                    <form action="/search" method="GET" class="hidden md:flex flex-1 max-w-xl mx-8 lg:mx-12">
                        <div class="relative w-full flex items-center">
                            <input
                                type="search"
                                name="q"
                                placeholder="Поиск по сайту"
                                class="w-full pl-4 pr-12 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold transition shadow-sm placeholder-gray-400"
                            />
                            <button
                                type="submit"
                                class="absolute right-1 top-1/2 -translate-y-1/2 h-[calc(100%-8px)] w-9 rounded-lg bg-gray-100 hover:bg-blue-600 text-gray-500 hover:text-white transition-all duration-200 flex items-center justify-center"
                                title="Найти"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </form>

                    <!-- Правый блок кнопок -->
                    <div class="flex items-center gap-1.5 sm:gap-3 relative" ref="dropdownsWrapper">
                        
                        <!-- Поиск (мобилка) -->
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
                            <Link href="/login" class="hidden sm:flex px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-bold transition">Войти</Link>
                            <Link href="/register" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 shadow-sm transition">Регистрация</Link>
                        </template>

                        <!-- Плавающий поиск для мобильных -->
                        <Transition name="popup-drop">
                            <div v-if="mobileSearchOpen" :class="activeWidget === 'search' ? 'z-50' : 'z-40'" class="fixed sm:hidden left-4 right-4 top-[76px] bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-gray-100 p-2 cursor-default" @click.stop>
                                <form action="/search" method="GET" class="relative w-full flex items-center">
                                    <input type="search" name="q" placeholder="Поиск оборудования..." 
                                        class="w-full pl-4 pr-12 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-[15px] font-bold transition shadow-sm placeholder-gray-400"/>
                                    <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 h-[calc(100%-8px)] w-9 rounded-lg bg-gray-100 hover:bg-blue-600 text-gray-500 hover:text-white transition-all flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </button>
                                </form>
                            </div>
                        </Transition>

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

        <!-- Полноценный подвал -->
        <footer class="bg-gray-900 text-white mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                    
                    <!-- О компании -->
                    <div class="sm:col-span-2 lg:col-span-1">
                        <div class="flex items-center gap-2.5 mb-4">
                            <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center text-lg shadow-sm flex-shrink-0">⚙️</div>
                            <span class="font-black text-xl tracking-tight">ЗПО <span class="text-blue-400">РиТ</span></span>
                        </div>
                        <p class="text-gray-400 text-sm leading-relaxed mb-4">
                            Официальный дилер грузоподъёмного и такелажного оборудования. Работаем с 2012 года. Сертифицированная продукция с документами.
                        </p>
                        <div class="flex items-center gap-2 text-gray-500 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            Пн–Пт: 9:00 – 18:00 (МСК)
                        </div>
                    </div>

                    <!-- Каталог -->
                    <div>
                        <h3 class="font-black text-white text-sm uppercase tracking-wider mb-4">Каталог</h3>
                        <ul class="space-y-2.5">
                            <li><Link href="/catalog/rigging" class="text-gray-400 hover:text-white transition text-sm font-medium flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>Такелажное оборудование</Link></li>
                            <li><Link href="/catalog/lifting-equipment" class="text-gray-400 hover:text-white transition text-sm font-medium flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>Грузоподъёмное</Link></li>
                            <li><Link href="/catalog/hydraulic" class="text-gray-400 hover:text-white transition text-sm font-medium flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>Гидравлика</Link></li>
                            <li><Link href="/catalog/warehouse" class="text-gray-400 hover:text-white transition text-sm font-medium flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>Складское оборудование</Link></li>
                            <li><Link href="/search" class="text-gray-400 hover:text-white transition text-sm font-medium flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>Все товары</Link></li>
                        </ul>
                    </div>

                    <!-- Покупателям -->
                    <div>
                        <h3 class="font-black text-white text-sm uppercase tracking-wider mb-4">Покупателям</h3>
                        <ul class="space-y-2.5">
                            <li><Link href="/cart" class="text-gray-400 hover:text-white transition text-sm font-medium flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>Корзина</Link></li>
                            <li><Link href="/profile" class="text-gray-400 hover:text-white transition text-sm font-medium flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>Мои заказы</Link></li>
                            <li><Link href="/login" class="text-gray-400 hover:text-white transition text-sm font-medium flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>Войти в аккаунт</Link></li>
                            <li><a href="tel:+73431234567" class="text-gray-400 hover:text-white transition text-sm font-medium flex items-center gap-2"><span class="w-1 h-1 rounded-full bg-blue-500 flex-shrink-0"></span>Задать вопрос</a></li>
                        </ul>
                    </div>

                    <!-- Контакты -->
                    <div>
                        <h3 class="font-black text-white text-sm uppercase tracking-wider mb-4">Контакты</h3>
                        <ul class="space-y-3">
                            <li>
                                <a href="tel:+73431234567" class="group flex items-start gap-3">
                                    <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition">
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-white font-bold text-sm group-hover:text-blue-400 transition">+7 (343) 123-45-67</div>
                                        <div class="text-gray-500 text-xs">Основная линия</div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="mailto:info@zpority.ru" class="group flex items-start gap-3">
                                    <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition">
                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-white font-bold text-sm group-hover:text-blue-400 transition">info@zpority.ru</div>
                                        <div class="text-gray-500 text-xs">Общие вопросы</div>
                                    </div>
                                </a>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                </div>
                                <div>
                                    <div class="text-white font-bold text-sm">г. Екатеринбург</div>
                                    <div class="text-gray-500 text-xs">ул. Металлургов, 12, офис 301</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Разделитель -->
                <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-gray-500 text-xs">© 2025 ЗПО РиТ — Грузоподъёмное и такелажное оборудование</p>
                    <p class="text-gray-600 text-xs">ИНН 6671234567 · ОГРН 1126670123456</p>
                </div>
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