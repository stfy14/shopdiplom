<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted, computed, watch, nextTick, inject } from 'vue'
import axios from 'axios';

defineOptions({ layout: AdminLayout });
const toast = inject('toast');
const props = defineProps({ order: Object })

const messages = ref(props.order.messages ?? [])
const chatBox = ref(null)
const msgInput = ref(null)
const messageText = ref('')
const isSending = ref(false)
const isSelectOpen = ref(false)

const statusForm = useForm({ status: props.order.status })
const contactForm = useForm({
    city: props.order.city, street: props.order.street,
    house: props.order.house, comment: props.order.comment, phone: props.order.phone,
})
const editingContacts = ref(false)

watch(() => props.order, (newOrder) => {
    if (!editingContacts.value) {
        contactForm.city = newOrder.city; contactForm.street = newOrder.street;
        contactForm.house = newOrder.house; contactForm.comment = newOrder.comment; contactForm.phone = newOrder.phone;
    }
}, { deep: true });

function phoneInput(e) {
    let value = e.target.value.replace(/\D/g, '')
    if (value === '') { contactForm.phone = ''; e.target.value = ''; return; }
    let formatted = '+' + value.substring(0, 1)
    if (value.length > 1) formatted += ' (' + value.substring(1, 4)
    if (value.length > 4) formatted += ') ' + value.substring(4, 7)
    if (value.length > 7) formatted += '-' + value.substring(7, 9)
    if (value.length > 9) formatted += '-' + value.substring(9, 11)
    contactForm.phone = formatted; e.target.value = formatted;
}

function saveContacts() {
    axios.patch(`/admin/orders/${props.order.id}/contacts`, contactForm.data())
        .then(() => { editingContacts.value = false; toast.success('Контакты сохранены'); })
        .catch(err => { if (err.response?.status === 422) contactForm.setError(err.response.data.errors); });
}

function updateStatus() {
    axios.patch(`/admin/orders/${props.order.id}/status`, statusForm.data())
        .then(() => toast.success('Статус успешно обновлен'))
        .catch(() => toast.error('Ошибка обновления статуса'));
}

const sortedMessages = computed(() => [...messages.value].sort((a, b) => a.id - b.id))
const isAtBottom = ref(true)
const unreadCount = ref(0)

function handleScroll() {
    if (!chatBox.value) return
    const { scrollTop, scrollHeight, clientHeight } = chatBox.value
    isAtBottom.value = scrollHeight - scrollTop - clientHeight < 50
    if (isAtBottom.value) unreadCount.value = 0
}

function scrollToBottom() {
    if (!chatBox.value) return
    chatBox.value.scrollTop = chatBox.value.scrollHeight
    isAtBottom.value = true; unreadCount.value = 0
}

watch(() => messages.value.length, async (n, o) => {
    if (n - o > 0) { await nextTick(); if (isAtBottom.value) scrollToBottom(); else unreadCount.value += n - o; }
})

watch(() => props.order.messages, (nm) => {
    if (!nm) return;
    nm.forEach(msg => { if (!messages.value.find(m => m.id === msg.id)) messages.value.push(msg); });
}, { deep: true })

function resizeTextarea(e) {
    const el = e ? e.target : null;
    if (!el) return;
    el.style.height = '44px';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function handleEnter(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } }
function formatPrice(p) { return new Intl.NumberFormat('ru-RU').format(p) }
function formatTime(dt) { return new Date(dt).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' }) }

function sendMessage() {
    if (!messageText.value.trim() || isSending.value) return
    const text = messageText.value
    messageText.value = '';
    isSending.value = true;
    document.querySelectorAll('textarea').forEach(ta => ta.style.height = '44px');

    const tempId = 'temp_' + Date.now()
    messages.value.push({ id: tempId, message: text, sender_role: 'admin', created_at: new Date().toISOString(), isTemp: true })
    nextTick(() => scrollToBottom())

    axios.post(`/admin/orders/${props.order.id}/messages`, { message: text })
        .then(res => { isSending.value = false; const i = messages.value.findIndex(m => m.id === tempId); if (i !== -1) messages.value[i] = res.data; })
        .catch(() => { isSending.value = false; const i = messages.value.findIndex(m => m.id === tempId); if (i !== -1) messages.value.splice(i, 1); messageText.value = text; toast.error('Ошибка отправки'); })
}

onMounted(() => {
    window.Echo.private(`order.${props.order.id}`)
        .listen('.NewOrderMessage', (message) => {
            const exists = messages.value.find(m => m.id === message.id)
            if (!exists) {
                const ti = messages.value.findIndex(m => m.isTemp && m.message === message.message)
                if (ti !== -1) messages.value[ti] = message; else messages.value.push(message);
                if (message.sender_role === 'user') toast.success('Новое сообщение от клиента');
            }
        })
        .listen('.OrderUpdated', () => { router.reload({ only: ['order'], preserveScroll: true }) })
    setTimeout(scrollToBottom, 300)
})
onUnmounted(() => { window.Echo.leave(`private-order.${props.order.id}`) })
watch(() => props.order.status, (s) => { statusForm.status = s })

const statusMap = {
    new:               { label: 'Новый' },
    processing:        { label: 'В обработке' },
    shipped:           { label: 'Отправлен' },
    completed:         { label: 'Завершён' },
    cancelled:         { label: 'Отмена (Маг.)' },
    cancelled_by_user: { label: 'Отмена (Клиент)' },
}
const st = computed(() => {
    const colorMap = {
        new:               'bg-blue-100 text-blue-700',
        processing:        'bg-yellow-100 text-yellow-700',
        shipped:           'bg-green-100 text-green-700',
        completed:         'bg-emerald-100 text-emerald-700',
        cancelled:         'bg-red-100 text-red-700',
        cancelled_by_user: 'bg-gray-100 text-gray-700',
    };
    return { label: statusMap[props.order.status]?.label ?? props.order.status, color: colorMap[props.order.status] ?? 'bg-gray-100 text-gray-600' };
});
</script>

<template>
    <!-- Заголовок -->
    <div class="flex items-center gap-4 mb-6">
        <Link href="/admin/orders" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </Link>
        <div>
            <h1 class="font-black text-2xl text-gray-900">Заказ #{{ order.id }}</h1>
            <div class="text-xs text-gray-400 font-mono mt-0.5">UUID: {{ order.uuid }}</div>
        </div>
        <span :class="['ml-auto px-4 py-1.5 rounded-xl text-sm font-bold', st.color]">{{ st.label }}</span>
    </div>

    <!-- Основная сетка -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- ЛЕВЫЙ БЛОК -->
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">

            <!-- ── Статус заказа ── -->
            <div class="mb-8">
                <div class="mb-4">
                    <span class="text-xs text-gray-400 uppercase font-black tracking-wider">Статус заказа</span>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 relative">
                    <div v-if="isSelectOpen" @click="isSelectOpen = false" class="fixed inset-0 z-10"></div>
                    <div class="relative flex-grow z-20">
                        <button @click="isSelectOpen = !isSelectOpen" type="button" class="w-full h-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm font-bold transition shadow-sm flex items-center justify-between text-gray-800">
                            <span class="truncate">{{ statusMap[statusForm.status]?.label }}</span>
                            <svg :class="['w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0', isSelectOpen ? 'rotate-180' : '']" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                            <div v-if="isSelectOpen" class="absolute z-10 w-full mt-1 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                                <button v-for="(data, code) in statusMap" :key="code" @click="statusForm.status = code; isSelectOpen = false" type="button" :class="['w-full text-left px-4 py-2.5 text-sm font-bold transition flex items-center justify-between', statusForm.status === code ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50']">
                                    <span class="truncate">{{ data.label }}</span>
                                    <svg v-if="statusForm.status === code" class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </transition>
                    </div>
                    <button @click="updateStatus" :disabled="statusForm.processing" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-sm disabled:opacity-50 whitespace-nowrap z-0 relative">
                        Обновить статус
                    </button>
                </div>
            </div>

            <hr class="border-gray-100 my-8" />

            <!-- ── Контактная информация ── -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs text-gray-400 uppercase font-black tracking-wider">Контактная информация</span>
                    <button v-if="!editingContacts" @click="editingContacts = true" class="text-blue-600 hover:text-blue-800 text-xs font-bold transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Правка
                    </button>
                </div>
                <div>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-11 h-11 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900">{{ order.user?.name }}</div>
                            <div class="text-sm text-gray-400 font-medium">ID: {{ order.user?.id }}</div>
                        </div>
                    </div>
                    <div v-if="!editingContacts">
                        <div class="font-black text-gray-900 text-lg mb-1">{{ order.phone }}</div>
                        <div class="text-gray-600 text-sm font-medium">г. {{ order.city }}, ул. {{ order.street }}, д. {{ order.house }}</div>
                        <div v-if="order.comment" class="text-sm text-gray-600 mt-3 p-3 bg-gray-50 rounded-xl border-l-4 border-l-yellow-400 italic">«{{ order.comment }}»</div>
                    </div>
                    <div v-else class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Телефон</label>
                            <input :value="contactForm.phone" @input="phoneInput" placeholder="+7..." class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm"/>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Город</label>
                                <input v-model="contactForm.city" placeholder="Город" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm"/>
                            </div>
                            <div>
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Улица</label>
                                <input v-model="contactForm.street" placeholder="Улица" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Дом и квартира</label>
                            <input v-model="contactForm.house" placeholder="Дом, квартира" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Комментарий</label>
                            <textarea v-model="contactForm.comment" rows="2" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-medium resize-none transition shadow-sm"></textarea>
                        </div>
                        <div class="flex gap-3 pt-1">
                            <button @click="saveContacts" :disabled="contactForm.processing" class="flex-1 py-3 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition shadow-sm">Сохранить</button>
                            <button @click="editingContacts = false; contactForm.reset()" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-50 transition shadow-sm">Отмена</button>
                        </div>
                        <div v-if="contactForm.errors.phone" class="text-red-500 text-xs font-bold text-center mt-1">{{ contactForm.errors.phone }}</div>
                    </div>
                </div>
            </div>

            <hr class="border-gray-100 my-8" />

            <!-- ── Состав заказа ── -->
            <div>
                <div class="mb-4">
                    <span class="text-xs text-gray-400 uppercase font-black tracking-wider">Состав заказа</span>
                </div>
                <div>
                    <div class="flex flex-col gap-3">
                        <Link v-for="item in order.items" :key="item.id" :href="`/product/${item.product_id}`" target="_blank"
                            class="flex items-center gap-4 bg-white rounded-2xl shadow-sm p-4 transition hover:shadow-md hover:-translate-y-px will-change-transform border border-gray-100 group">
                            <div class="w-14 h-14 bg-white rounded-xl border border-gray-100 shadow-sm flex flex-shrink-0 items-center justify-center p-1.5">
                                <img :src="item.product?.image ? `/storage/${item.product.image}` : 'https://placehold.co/50?text=?'" class="max-w-full max-h-full object-contain" />
                            </div>
                            <div class="flex-grow min-w-0">
                                <div class="font-bold text-gray-900 group-hover:text-blue-600 transition truncate flex items-center gap-1.5">
                                    {{ item.product?.title || 'Товар удален' }}
                                    <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </div>
                                <div class="text-gray-500 text-xs font-medium mt-0.5">Артикул: {{ item.product_id }} · {{ item.quantity }} шт.</div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="font-black text-gray-900 whitespace-nowrap text-sm">{{ formatPrice(item.price_at_purchase * item.quantity) }} ₽</div>
                                <div v-if="item.product?.discount > 0" class="text-[11px] font-bold text-red-500 mt-0.5">-{{ item.product.discount }}% скидка</div>
                            </div>
                        </Link>
                    </div>
                    <div class="flex justify-end items-center gap-4 pt-6 mt-6 border-t border-gray-100">
                        <span class="text-gray-500 font-bold uppercase text-sm tracking-wider">Итого:</span>
                        <span class="text-3xl font-black text-gray-900 tracking-tight">{{ formatPrice(order.total_price) }} ₽</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ПРАВЫЙ БЛОК — чат (Десктоп) -->
        <div class="hidden lg:flex lg:col-span-1 flex-col bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" style="position: sticky; top: 88px; height: calc(100vh - 88px - 32px);">
            <div class="px-5 py-4 bg-white shadow-sm relative z-10 flex items-center gap-3 flex-shrink-0">
                <div class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z" /></svg>
                </div>
                <div>
                    <div class="font-black text-gray-900 leading-tight">Чат с клиентом</div>
                    <div class="text-[11px] text-gray-400 font-bold uppercase tracking-wide mt-0.5">{{ order.user?.name }}</div>
                </div>
            </div>
            <div class="flex-grow overflow-y-auto p-5 bg-gray-50/50 flex flex-col gap-3 relative" ref="chatBox" @scroll="handleScroll">
                <div v-if="messages.length === 0" class="text-center text-gray-400 text-sm font-medium my-auto">История сообщений пуста</div>
                <div v-for="msg in sortedMessages" :key="msg.id"
                     :class="['max-w-[85%] p-3.5 text-sm break-words shadow-sm',
                             msg.sender_role === 'admin' ? 'bg-yellow-400 text-gray-900 self-end rounded-2xl rounded-br-sm' : 'bg-white text-gray-800 self-start rounded-2xl rounded-bl-sm',
                             msg.isTemp ? 'opacity-70' : '']">
                    <span class="whitespace-pre-wrap font-medium">{{ msg.message }}</span>
                    <div :class="['text-[10px] text-right mt-1.5 font-bold', msg.sender_role === 'admin' ? 'text-yellow-700' : 'text-gray-400']">{{ formatTime(msg.created_at) }}</div>
                </div>
                <button v-if="!isAtBottom" @click="scrollToBottom" class="absolute bottom-4 right-4 w-10 h-10 bg-white border border-gray-200 shadow-lg rounded-full flex items-center justify-center text-gray-600 hover:text-yellow-600 transition z-20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full">{{ unreadCount }}</span>
                </button>
            </div>
            <div class="p-3 bg-white border-t border-gray-100 flex-shrink-0">
                <div class="relative flex items-stretch bg-white border border-gray-200 rounded-xl shadow-sm focus-within:border-yellow-400 focus-within:ring-1 focus-within:ring-yellow-400 transition-all">
                    <textarea v-model="messageText" @input="resizeTextarea" @keydown="handleEnter"
                        placeholder="Ответить клиенту..." rows="1"
                        class="flex-grow bg-transparent border-0 outline-none focus:ring-0 text-[15px] py-[10px] px-4 font-medium resize-none leading-[24px] block rounded-xl"
                        style="height: 44px;"></textarea>
                    <button @click="sendMessage" :disabled="!messageText || !messageText.trim() || isSending"
                        class="w-[36px] flex-shrink-0 m-1 rounded-lg bg-yellow-400 hover:bg-yellow-500 text-gray-900 flex items-center justify-center disabled:opacity-50 transition">
                        <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Чат на мобильных -->
        <div class="lg:hidden flex flex-col bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" style="height: 480px;">
            <div class="px-5 py-4 bg-white shadow-sm relative z-10 flex items-center gap-3 flex-shrink-0">
                <div class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z" /></svg>
                </div>
                <div class="font-black text-gray-900">Чат с клиентом</div>
            </div>
            <div class="flex-grow overflow-y-auto p-4 bg-gray-50/50 flex flex-col gap-3">
                <div v-if="messages.length === 0" class="text-center text-gray-400 text-sm font-medium my-auto">История сообщений пуста</div>
                <div v-for="msg in sortedMessages" :key="msg.id + '_m'"
                     :class="['max-w-[85%] p-3.5 text-sm break-words shadow-sm',
                             msg.sender_role === 'admin' ? 'bg-yellow-400 text-gray-900 self-end rounded-2xl rounded-br-sm' : 'bg-white text-gray-800 self-start rounded-2xl rounded-bl-sm']">
                    <span class="whitespace-pre-wrap font-medium">{{ msg.message }}</span>
                    <div :class="['text-[10px] text-right mt-1.5 font-bold', msg.sender_role === 'admin' ? 'text-yellow-700' : 'text-gray-400']">{{ formatTime(msg.created_at) }}</div>
                </div>
            </div>
            <div class="p-3 bg-white border-t border-gray-100 flex-shrink-0">
                <div class="relative flex items-stretch bg-white border border-gray-200 rounded-xl shadow-sm focus-within:border-yellow-400 focus-within:ring-1 focus-within:ring-yellow-400 transition-all">
                    <textarea v-model="messageText" @input="resizeTextarea" @keydown="handleEnter" placeholder="Ваше сообщение..." rows="1"
                        class="flex-grow bg-transparent border-0 outline-none focus:ring-0 text-[15px] py-[10px] px-4 font-medium resize-none leading-[24px] block rounded-xl"
                        style="height: 44px;"></textarea>
                    <button @click="sendMessage" :disabled="!messageText || !messageText.trim() || isSending"
                        class="w-[36px] flex-shrink-0 m-1 rounded-lg bg-yellow-400 hover:bg-yellow-500 text-gray-900 flex items-center justify-center disabled:opacity-50 transition">
                        <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>