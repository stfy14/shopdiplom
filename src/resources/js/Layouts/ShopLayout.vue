<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, watch, provide, onMounted, onUnmounted } from 'vue'
import CartPopup from '@/Components/CartPopup.vue'
import Toast from '@/Components/Toast.vue'
import NotificationCenter from '@/Components/NotificationCenter.vue'
import axios from 'axios'

const page = usePage()
const toast = ref(null)

// Локальное хранилище синхронизируется с сервером
const localNotifications = ref([])
const bellOpen = ref(false)
const bellWrapper = ref(null) // Реф для отслеживания кликов снаружи

// Считаем непрочитанные 
const unreadCount = computed(() => localNotifications.value.filter(n => !n.read).length)

// Обновляем список только если прилетели новые данные от Inertia
watch(() => page.props.notifications, (newVal) => {
    localNotifications.value = newVal ? [...newVal] :[]
}, { immediate: true, deep: true })

onMounted(() => {
    document.addEventListener('click', handleGlobalClick)
})

onUnmounted(() => {
    document.removeEventListener('click', handleGlobalClick)
})

// Закрываем панель по клику в любое место
function handleGlobalClick(e) {
    if (bellOpen.value && bellWrapper.value && !bellWrapper.value.contains(e.target)) {
        bellOpen.value = false
    }
}

function openBell() {
    bellOpen.value = true
    if (unreadCount.value > 0) {
        // Оптимистично помечаем прочитанным и шлем запрос на сервер
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
        }, index * 200) // <-- Поставь 200мс или 250мс для очень плавного красивого каскада
    })
    
    // Формула: кол-во элементов * 200мс интервала + 600мс на последнюю анимацию свайпа и схлопывания
    const totalAnimationTime = (items.length * 200) + 600;

    setTimeout(() => {
        axios.delete('/notifications/all').then(() => {
            router.reload({ only: ['notifications', 'adminNotifications'] })
        })
    }, totalAnimationTime)
}

// Теперь Toast вызывается напрямую без пуша в список, так как список обновляется сервером
provide('toast', {
    success: (msg, opts = {}) => toast.value?.add(msg, 'success', opts),
    error:   (msg, opts = {}) => toast.value?.add(msg, 'error', opts),
})

watch(() => page.props.flash?.cart_added, (val) => {
    if (val) toast.value?.add(`${val} добавлен в корзину`, 'success')
})

const user        = computed(() => page.props.auth.user)
const cartCount   = computed(() => page.props.cartCount ?? 0)
const cartItems   = computed(() => page.props.cartItems ??[])
const cartOpen    = ref(false)
const currentPage = computed(() => page.component)

function fmt(p) { return new Intl.NumberFormat('ru-RU').format(p) }

const statusLabels = {
    new: 'Новый', processing: 'В обработке', shipped: 'Отправлен', 
    cancelled: 'Отменён', cancelled_by_user: 'Отменён клиентом',
}

// Подписки: Показываем тост и перезагружаем (Inertia сама подтянет новое уведомление в список)
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
        <nav class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-40 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-[72px]">
                    <Link href="/" class="flex items-center gap-2.5 font-black text-xl text-gray-900 tracking-tight group">
                        <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center text-lg shadow-sm group-hover:scale-105 transition-transform">⚙️</div>
                        <span>ЗПО <span class="text-blue-600">РиТ</span></span>
                    </Link>

                    <form action="/" method="GET" class="hidden md:flex flex-1 max-w-xl mx-12">
                        <div class="relative w-full flex items-center">
                            <svg class="absolute left-4 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="search" name="q" placeholder="Поиск оборудования..." 
                                class="w-full pl-11 pr-4 py-2.5 rounded-2xl bg-gray-100/80 border-transparent focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all text-sm font-medium placeholder-gray-400"/>
                        </div>
                    </form>

                    <div class="flex items-center gap-3">
                        <button @click="cartOpen = true" class="relative flex items-center justify-center w-11 h-11 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[11px] font-bold rounded-full w-5 h-5 flex items-center justify-center shadow-sm border-2 border-white">{{ cartCount }}</span>
                        </button>

                        <div v-if="user" class="relative" ref="bellWrapper">
                            <button
                                @click.stop="bellOpen ? bellOpen = false : openBell()"
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
                        
                            <Transition name="bell-drop">
                                <NotificationCenter
                                    v-if="bellOpen"
                                    :notifications="localNotifications"
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
.bell-drop-enter-active {
    /* Анимация появления стала чуть быстрее и "пружинистее" */
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.bell-drop-leave-active {
    transition: all 0.2s ease-in-out;
}

/* Совмещаем translateX для центрирования с translateY для вылета сверху */
.bell-drop-enter-from,
.bell-drop-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(-12px) scale(0.95);
}
</style>