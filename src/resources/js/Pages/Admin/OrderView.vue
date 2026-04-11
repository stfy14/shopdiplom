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
        .then(() => { 
            editingContacts.value = false;
            toast.success('Контакты сохранены');
        })
        .catch(err => {
            if (err.response?.status === 422) contactForm.setError(err.response.data.errors);
        });
}

function updateStatus() {
    axios.patch(`/admin/orders/${props.order.id}/status`, statusForm.data())
        .then(() => toast.success('Статус успешно обновлен'))
        .catch(() => toast.error('Ошибка обновления статуса'));
}

const sortedMessages = computed(() => [...messages.value].sort((a, b) => a.id - b.id))
const isAtBottom = ref(true)
const unreadCount = ref(0)
const isExpanded = ref(false)

function handleScroll() {
    if (!chatBox.value) return
    const { scrollTop, scrollHeight, clientHeight } = chatBox.value
    isAtBottom.value = scrollHeight - scrollTop - clientHeight < 50
    if (isAtBottom.value) unreadCount.value = 0
}

function scrollToBottom() {
    if (!chatBox.value) return
    chatBox.value.scrollTop = chatBox.value.scrollHeight
    isAtBottom.value = true
    unreadCount.value = 0
}

watch(() => messages.value.length, async (newLen, oldLen) => {
    const newMessagesCount = newLen - oldLen
    if (newMessagesCount > 0) {
        await nextTick()
        if (isAtBottom.value) scrollToBottom()
        else unreadCount.value += newMessagesCount
    }
})

watch(() => props.order.messages, (newMessages) => {
    if (!newMessages) return;
    newMessages.forEach(msg => {
        if (!messages.value.find(m => m.id === msg.id)) messages.value.push(msg);
    });
}, { deep: true })

function resizeTextarea() {
    if (!msgInput.value) return
    const el = msgInput.value
    el.style.height = '44px'
    const scrollHeight = el.scrollHeight
    const newHeight = Math.min(scrollHeight, 120)
    el.style.height = newHeight + 'px'
    isExpanded.value = newHeight > 50
}

function handleEnter(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }
function formatTime(dt) { return new Date(dt).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' }) }

function sendMessage() {
    if (!messageText.value.trim() || isSending.value) return
    const text = messageText.value
    messageText.value = ''
    resizeTextarea()
    isSending.value = true

    const tempId = 'temp_' + Date.now()
    messages.value.push({
        id: tempId, message: text, sender_role: 'admin', created_at: new Date().toISOString(), isTemp: true
    })
    nextTick(() => scrollToBottom())

    axios.post(`/admin/orders/${props.order.id}/messages`, { message: text })
        .then(res => {
            isSending.value = false
            const index = messages.value.findIndex(m => m.id === tempId)
            if (index !== -1) messages.value[index] = res.data
        })
        .catch(() => {
            isSending.value = false
            const index = messages.value.findIndex(m => m.id === tempId)
            if (index !== -1) messages.value.splice(index, 1)
            messageText.value = text
            resizeTextarea()
            toast.error('Ошибка отправки')
        })
}

onMounted(() => {
    window.Echo.private(`order.${props.order.id}`)
        .listen('.NewOrderMessage', (message) => {
            const exists = messages.value.find(m => m.id === message.id)
            if (!exists) {
                const tempIndex = messages.value.findIndex(m => m.isTemp && m.message === message.message)
                if (tempIndex !== -1) messages.value[tempIndex] = message;
                else messages.value.push(message);

                if (message.sender_role === 'user') toast.success('Новое сообщение от клиента');
            }
        })
        .listen('.OrderUpdated', () => {
            router.reload({ only: ['order'], preserveScroll: true })
        })

    setTimeout(scrollToBottom, 300)
})

onUnmounted(() => { window.Echo.leave(`private-order.${props.order.id}`) })

watch(() => props.order.status, (newStatus) => { statusForm.status = newStatus })

const statusMap = {
    new: { label: 'Новый' }, processing: { label: 'В обработке' },
    shipped: { label: 'Отправлен' }, cancelled: { label: 'Отмена (Маг.)' },
    cancelled_by_user: { label: 'Отмена (Клиент)' },
}
const st = computed(() => {
    const s = statusMap[props.order.status] || { label: props.order.status };
    const colorMap = {
        new: 'bg-blue-100 text-blue-700', processing: 'bg-yellow-100 text-yellow-700',
        shipped: 'bg-green-100 text-green-700', cancelled: 'bg-red-100 text-red-700',
        cancelled_by_user: 'bg-gray-100 text-gray-700'
    };
    return { label: s.label, color: colorMap[props.order.status] || 'bg-gray-100 text-gray-600' };
});
</script>

<template>
    <!-- Заголовок страницы -->
    <div class="flex items-center gap-4 mb-6">
        <Link href="/admin/orders" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shadow-sm" title="Назад к заказам">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </Link>
        <div>
            <h1 class="font-black text-2xl text-gray-900">Заказ #{{ order.id }}</h1>
            <div class="text-xs text-gray-400 font-mono mt-0.5">UUID: {{ order.uuid }}</div>
        </div>
        <span :class="['ml-auto px-4 py-1.5 rounded-xl text-sm font-bold', st.color]">{{ st.label }}</span>
    </div>

    <!-- Основная сетка: левая колонка + чат -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- Левая колонка -->
        <div class="lg:col-span-2 flex flex-col gap-6">

            <!-- БЛОК: Статус заказа -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <span class="text-xs text-gray-400 uppercase font-black tracking-wider">Статус заказа</span>
                </div>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <select v-model="statusForm.status" class="flex-grow px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-bold text-gray-800">
                            <option v-for="(data, code) in statusMap" :key="code" :value="code">{{ data.label }}</option>
                        </select>
                        <button @click="updateStatus" :disabled="statusForm.processing" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-sm disabled:opacity-50 whitespace-nowrap">
                            Обновить статус
                        </button>
                    </div>
                </div>
            </div>

            <!-- БЛОК: Контактная информация -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-gray-400 uppercase font-black tracking-wider">Контактная информация</span>
                    <button v-if="!editingContacts" @click="editingContacts = true" class="text-blue-600 hover:text-blue-800 text-xs font-bold transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Правка
                    </button>
                </div>
                <div class="p-6">
                    <!-- Клиент -->
                    <div class="flex items-center gap-4 pb-5 mb-5 border-b border-gray-100">
                        <div class="w-11 h-11 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900">{{ order.user?.name }}</div>
                            <div class="text-sm text-gray-400 font-medium">ID: {{ order.user?.id }}</div>
                        </div>
                    </div>

                    <!-- Контакты и адрес — просмотр -->
                    <div v-if="!editingContacts">
                        <div class="font-black text-gray-900 text-lg mb-1">{{ order.phone }}</div>
                        <div class="text-gray-600 text-sm font-medium leading-snug">г. {{ order.city }}, ул. {{ order.street }}, д. {{ order.house }}</div>
                        <div v-if="order.comment" class="text-sm text-gray-600 mt-3 p-3 bg-gray-50 rounded-xl border border-gray-100 border-l-4 border-l-yellow-400 italic">«{{ order.comment }}»</div>
                    </div>

                    <!-- Контакты и адрес — редактирование -->
                    <div v-else class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Телефон</label>
                            <input :value="contactForm.phone" @input="phoneInput" placeholder="+7..." class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition"/>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Город</label>
                                <input v-model="contactForm.city" placeholder="Город" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition"/>
                            </div>
                            <div>
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Улица</label>
                                <input v-model="contactForm.street" placeholder="Улица" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition"/>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Дом и квартира</label>
                            <input v-model="contactForm.house" placeholder="Дом, квартира" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Комментарий</label>
                            <textarea v-model="contactForm.comment" rows="2" placeholder="Комментарий клиента" class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-medium resize-none transition"></textarea>
                        </div>
                        <div class="flex gap-3 pt-1">
                            <button @click="saveContacts" :disabled="contactForm.processing" class="flex-1 py-3 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition shadow-sm">Сохранить</button>
                            <button @click="editingContacts = false; contactForm.reset()" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-50 transition">Отмена</button>
                        </div>
                        <div v-if="contactForm.errors.phone" class="text-red-500 text-xs font-bold text-center">{{ contactForm.errors.phone }}</div>
                    </div>
                </div>
            </div>

            <!-- БЛОК: Состав заказа -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <span class="text-xs text-gray-400 uppercase font-black tracking-wider">Состав заказа</span>
                </div>
                <div class="p-4 sm:p-6">
                    <!-- Товары -->
                    <div class="flex flex-col gap-3 mb-6">
                        <Link v-for="item in order.items" :key="item.id" :href="`/product/${item.product_id}`" target="_blank"
                            class="flex items-center gap-4 p-3 bg-gray-50 hover:bg-blue-50/50 border border-gray-100 rounded-2xl transition group">
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
                            <div class="font-black text-gray-900 whitespace-nowrap text-sm">
                                {{ formatPrice(item.price_at_purchase * item.quantity) }} ₽
                            </div>
                        </Link>
                    </div>

                    <!-- Итого -->
                    <div class="flex justify-end items-center gap-4 pt-4 border-t border-gray-100">
                        <span class="text-gray-500 font-bold uppercase text-sm tracking-wider">Итого:</span>
                        <span class="text-3xl font-black text-gray-900 tracking-tight">{{ formatPrice(order.total_price) }} ₽</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Чат с клиентом (правая колонка) -->
        <div class="lg:col-span-1 lg:sticky lg:top-6 flex flex-col bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" style="height: calc(100vh - 120px);">
            <!-- Шапка чата -->
            <div class="px-5 py-4 border-b border-gray-100 bg-white flex items-center gap-3 flex-shrink-0 shadow-sm">
                <div class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z" /></svg>
                </div>
                <div>
                    <div class="font-black text-gray-900 leading-tight">Чат с клиентом</div>
                    <div class="text-[11px] text-gray-400 font-bold uppercase tracking-wide mt-0.5">{{ order.user?.name }}</div>
                </div>
            </div>

            <!-- Сообщения -->
            <div class="flex-grow overflow-y-auto p-5 bg-gray-50/50 flex flex-col gap-3 relative" ref="chatBox" @scroll="handleScroll">
                <div v-if="messages.length === 0" class="text-center text-gray-400 text-sm font-medium my-auto">История сообщений пуста</div>

                <div v-for="msg in sortedMessages" :key="msg.id"
                     :class="['max-w-[85%] p-3.5 text-sm relative break-words shadow-sm border',
                             msg.sender_role === 'admin'
                                 ? 'bg-yellow-400 border-yellow-400 text-gray-900 self-end rounded-2xl rounded-br-sm'
                                 : 'bg-white border-gray-200 text-gray-800 self-start rounded-2xl rounded-bl-sm',
                             msg.isTemp ? 'opacity-70' : '']">
                    <span class="whitespace-pre-wrap font-medium">{{ msg.message }}</span>
                    <div :class="['text-[10px] text-right mt-1.5 font-bold', msg.sender_role === 'admin' ? 'text-yellow-700' : 'text-gray-400']">{{ formatTime(msg.created_at) }}</div>
                </div>

                <button v-if="!isAtBottom" @click="scrollToBottom" class="absolute bottom-4 right-4 w-10 h-10 bg-white border border-gray-200 shadow-lg rounded-full flex items-center justify-center text-gray-600 hover:text-yellow-600 transition z-20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-black w-5 h-5 flex items-center justify-center rounded-full">{{ unreadCount }}</span>
                </button>
            </div>

            <!-- Поле ввода -->
            <div class="p-3 bg-white border-t border-gray-100 flex-shrink-0">
                <div :class="['flex items-stretch bg-gray-50 p-1 border border-gray-200 focus-within:border-yellow-400 focus-within:bg-white focus-within:shadow-sm transition-all duration-200', isExpanded ? 'rounded-2xl' : 'rounded-[24px] gap-2']">
                    <textarea
                        ref="msgInput" v-model="messageText" @input="resizeTextarea" @keydown="handleEnter"
                        placeholder="Ответить клиенту..." rows="1"
                        class="flex-grow bg-transparent border-0 outline-none focus:outline-none focus:ring-0 text-[15px] py-[12px] px-4 font-medium resize-none leading-[20px] block"
                        style="height: 44px;">
                    </textarea>
                    <button @click="sendMessage" :disabled="!messageText || !messageText.trim() || isSending"
                        :class="['w-[44px] flex-shrink-0 text-gray-900 flex items-center justify-center disabled:opacity-50 transition-all duration-200', isExpanded ? 'rounded-l-md rounded-r-xl bg-yellow-400 hover:bg-yellow-500' : 'rounded-[22px] bg-yellow-400 hover:bg-yellow-500']">
                        <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>