<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted, watch, nextTick, computed, inject } from 'vue'
import axios from 'axios';

const toast = inject('toast'); // Достаем глобальные тосты

const props = defineProps({
    order: Object,
})

const messages = ref(props.order.messages ??[])
const chatBox = ref(null)
const msgInput = ref(null)
const messageText = ref('')
const isSending = ref(false)

const sortedMessages = computed(() => [...messages.value].sort((a, b) => a.id - b.id))

const contactForm = useForm({
    city: props.order.city, street: props.order.street,
    house: props.order.house, comment: props.order.comment, phone: props.order.phone,
})
const editingContacts = ref(false)

// Подтягиваем данные, если их поменял админ, а форма контактов закрыта
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
    axios.patch(`/orders/${props.order.id}/contacts`, contactForm.data())
        .then(() => { 
            editingContacts.value = false;
            toast.success('Данные успешно сохранены'); // Тост для самого себя
        })
        .catch(err => {
            if (err.response?.status === 422) contactForm.setError(err.response.data.errors);
        });
}

function cancelOrder() {
    if (confirm('Отменить заказ?')) {
        axios.post(`/orders/${props.order.id}/cancel`)
            .then(() => toast.success('Заказ отменен'))
            .catch(() => toast.error('Ошибка отмены заказа'));
    }
}

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

// ПОЛНОСТЬЮ ИСПРАВЛЕННЫЙ РЕСАЙЗ
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

function sendMessage() {
    if (!messageText.value.trim() || isSending.value) return
    const text = messageText.value
    messageText.value = ''
    resizeTextarea() // Сбрасываем высоту поля сразу после отправки
    isSending.value = true

    const tempId = 'temp_' + Date.now()
    messages.value.push({
        id: tempId, message: text, sender_role: 'user', created_at: new Date().toISOString(), isTemp: true
    })
    nextTick(() => scrollToBottom())

    axios.post(`/orders/${props.order.id}/messages`, { message: text })
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
            resizeTextarea() // Возвращаем высоту, если вернули текст
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
                
                // Если написал админ, выводим тост
                if (message.sender_role === 'admin') toast.success('Новое сообщение от поддержки');
            }
        })
        .listen('.OrderUpdated', () => {
            router.reload({ only:['order'], preserveScroll: true })
        })

    setTimeout(scrollToBottom, 300)
})
onUnmounted(() => { window.Echo.leave(`private-order.${props.order.id}`) })

const statusMap = {
    new: { label: 'Новый', color: 'bg-blue-100 text-blue-700' },
    processing: { label: 'В обработке', color: 'bg-yellow-100 text-yellow-700' },
    shipped: { label: 'Отправлен', color: 'bg-green-100 text-green-700' },
    cancelled: { label: 'Отменён', color: 'bg-red-100 text-red-700' },
    cancelled_by_user: { label: 'Отменён вами', color: 'bg-gray-100 text-gray-700' },
}
const st = computed(() => statusMap[props.order.status] ?? { label: props.order.status, color: 'bg-gray-100 text-gray-600' })
function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }
function formatTime(dt) { return new Date(dt).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' }) }
</script>

<template>
    <ShopLayout>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="flex items-center gap-3 mb-6">
                    <Link href="/profile" class="text-blue-600 hover:underline text-sm font-medium">← Мои заказы</Link>
                    <span class="text-gray-300">|</span>
                    <h1 class="font-bold text-xl">Заказ #{{ order.id }}</h1>
                    <span :class="['ml-auto px-3 py-1 rounded-full text-xs font-bold', st.color]">{{ st.label }}</span>
                </div>
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <div class="text-xs text-gray-400 uppercase font-bold mb-1">Получатель</div>
                                <div class="flex items-center gap-2 font-medium">
                                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">👤</div>
                                    {{ $page.props.auth.user.name }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400 uppercase font-bold mb-1">Контакты</div>
                                <div v-if="!editingContacts">
                                    <div class="font-medium">{{ order.phone }}</div>
                                    <div class="text-gray-500 text-sm">г. {{ order.city }}, ул. {{ order.street }}, д. {{ order.house }}</div>
                                    <div v-if="order.comment" class="text-xs text-gray-400 mt-1 italic">"{{ order.comment }}"</div>
                                </div>
                                <div v-else class="space-y-3">
                                    <input :value="contactForm.phone" @input="phoneInput" placeholder="+7..." class="w-full px-4 py-2.5 bg-gray-100 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm transition"/>
                                    <input v-model="contactForm.city" placeholder="Город" class="w-full px-4 py-2.5 bg-gray-100 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm transition"/>
                                    <input v-model="contactForm.street" placeholder="Улица" class="w-full px-4 py-2.5 bg-gray-100 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm transition"/>
                                    <input v-model="contactForm.house" placeholder="Дом, квартира" class="w-full px-4 py-2.5 bg-gray-100 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm transition"/>
                                    <textarea v-model="contactForm.comment" rows="2" placeholder="Комментарий" class="w-full px-4 py-2.5 bg-gray-100 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm resize-none transition"></textarea>
                                </div>
                            </div>
                        </div>
                        <div v-if="['new', 'processing'].includes(order.status)" class="mb-6 border-b pb-6">
                            <button v-if="!editingContacts" @click="editingContacts = true" class="text-blue-600 hover:underline text-sm font-medium">✏️ Изменить контактные данные</button>
                            <div v-else class="flex gap-2">
                                <button @click="saveContacts" :disabled="contactForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">Сохранить</button>
                                <button @click="editingContacts = false" class="px-4 py-2 border rounded-xl text-sm hover:bg-gray-50">Отмена</button>
                            </div>
                            <div v-if="contactForm.errors.phone" class="text-red-500 text-xs mt-1">{{ contactForm.errors.phone }}</div>
                        </div>
                        <h3 class="font-bold mb-4">Состав заказа</h3>
                        <div class="flex flex-col gap-3 mb-6">
                            <Link v-for="item in order.items" :key="item.id" :href="`/product/${item.product_id}`" class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition group">
                                <div class="w-14 h-14 bg-white rounded-lg border flex flex-shrink-0 items-center justify-center p-1"><img :src="item.product?.image ? `/storage/${item.product.image}` : 'https://placehold.co/50?text=?'" class="max-w-full max-h-full object-contain" /></div>
                                <div class="flex-grow">
                                    <div class="font-bold text-gray-900 group-hover:text-blue-600 transition">{{ item.product?.title }}</div>
                                    <div class="text-gray-400 text-xs">Артикул: {{ item.product_id }} • {{ item.quantity }} шт.</div>
                                </div>
                                <span class="bg-white text-gray-900 font-bold px-3 py-1 rounded-full shadow-sm text-sm">{{ formatPrice(item.price_at_purchase * item.quantity) }} ₽</span>
                            </Link>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <button v-if="order.status === 'new'" @click="cancelOrder" class="px-4 py-2 border border-red-200 text-red-500 rounded-full text-sm font-medium hover:bg-red-50">Отменить заказ</button>
                            <div v-else></div>
                            <div class="text-right"><span class="text-gray-500 mr-2">Итого:</span><span class="text-2xl font-black text-blue-600">{{ formatPrice(order.total_price) }} ₽</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Чат -->
            <div class="lg:col-span-1 h-[calc(100vh-100px)] sticky top-20 flex flex-col bg-white rounded-3xl shadow-sm overflow-hidden border">
                <div class="px-5 py-4 border-b bg-white flex items-center gap-3 z-10">
                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-lg">🎧</div>
                    <div><div class="font-bold text-gray-900 leading-tight">Служба поддержки</div><div class="text-xs text-green-500 font-medium">Онлайн</div></div>
                </div>
                <div class="flex-grow overflow-y-auto p-5 bg-gray-50/50 flex flex-col gap-3 relative" ref="chatBox" @scroll="handleScroll">
                    <div v-if="messages.length === 0" class="text-center text-gray-400 text-sm my-auto">Напишите нам, если у вас есть вопросы</div>
                    <div v-for="msg in sortedMessages" :key="msg.id" :class="['max-w-[85%] p-3 text-sm relative break-words', msg.sender_role === 'user' ? 'bg-blue-500 text-white self-end rounded-2xl rounded-br-sm shadow-sm' : 'bg-white text-gray-800 self-start rounded-2xl rounded-bl-sm border shadow-sm', msg.isTemp ? 'opacity-70 transition-opacity duration-300' : '']">
                        <span class="whitespace-pre-wrap">{{ msg.message }}</span>
                        <div :class="['text-[10px] text-right mt-1', msg.sender_role === 'user' ? 'text-blue-100' : 'text-gray-400']">{{ formatTime(msg.created_at) }}</div>
                    </div>
                    <button v-if="!isAtBottom" @click="scrollToBottom" class="absolute bottom-4 right-4 w-10 h-10 bg-white border shadow-lg rounded-full flex items-center justify-center text-gray-600 hover:text-blue-600 transition z-20">
                        ↓<span v-if="unreadCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full">{{ unreadCount }}</span>
                    </button>
                </div>
                <div class="p-3 bg-white border-t">
                    <!-- ИСПРАВЛЕННЫЕ КЛАССЫ РАСШИРЕНИЯ -->
                    <div :class="['flex items-stretch bg-gray-100 p-1 border border-transparent focus-within:border-blue-400 focus-within:bg-white transition-all duration-300 ease-in-out shadow-inner', isExpanded ? 'rounded-2xl gap-0' : 'rounded-[24px] gap-2']">
                        <textarea 
                            ref="msgInput" v-model="messageText" @input="resizeTextarea" @keydown="handleEnter" 
                            placeholder="Сообщение..." rows="1" 
                            class="flex-grow bg-transparent border-0 border-transparent shadow-none outline-none focus:outline-none focus:ring-0 focus:border-transparent text-[15px] py-[12px] px-4 resize-none leading-[20px] m-0 block" 
                            style="height: 44px;">
                        </textarea>
                        <button @click="sendMessage" :disabled="!messageText || !messageText.trim() || isSending" :class="['w-[44px] flex-shrink-0 text-white flex items-center justify-center disabled:opacity-50 disabled:bg-gray-300 transition-all duration-300 ease-in-out', isExpanded ? 'rounded-l-md rounded-r-xl bg-blue-500 hover:bg-blue-600' : 'rounded-[22px] bg-blue-500 hover:bg-blue-600']">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 ml-0.5"><path d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>