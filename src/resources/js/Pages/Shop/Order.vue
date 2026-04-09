<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
const chatBox = ref(null)

const props = defineProps({
    order: Object,
})

const messages = ref(props.order.messages ?? [])

watch(messages, async () => {
    await nextTick()
    if (chatBox.value) chatBox.value.scrollTop = chatBox.value.scrollHeight
})

const msgForm = useForm({ message: '' })

let pollInterval = null

const statusMap = {
    new:               { label: 'Новый',           color: 'bg-blue-100 text-blue-700' },
    processing:        { label: 'В обработке',      color: 'bg-yellow-100 text-yellow-700' },
    shipped:           { label: 'Отправлен',        color: 'bg-green-100 text-green-700' },
    cancelled:         { label: 'Отменён',          color: 'bg-red-100 text-red-700' },
    cancelled_by_user: { label: 'Отменён вами',     color: 'bg-gray-100 text-gray-700' },
}

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price)
}

function formatTime(dt) {
    return new Date(dt).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
}

function loadMessages() {
    fetch(`/orders/${props.order.id}/messages`)
        .then(r => r.json())
        .then(data => { messages.value = data })
}

function sendMessage() {
    if (!msgForm.message.trim()) return
    msgForm.post(`/orders/${props.order.id}/messages`, {
        preserveState: true,
        onSuccess: () => {
            msgForm.reset()
            loadMessages()
        }
    })
}

function cancelOrder() {
    if (confirm('Вы уверены, что хотите отменить заказ?')) {
        router.post(`/orders/${props.order.id}/cancel`)
    }
}

onMounted(() => {
    pollInterval = setInterval(() => {
        loadMessages()
        // Обновляем страницу если статус мог измениться
        router.reload({ only: ['order'], preserveScroll: true })
    }, 3000)
})

onUnmounted(() => {
    clearInterval(pollInterval)
})

const contactForm = useForm({
    address: props.order.address,
    phone: props.order.phone,
})
const editingContacts = ref(false)

function saveContacts() {
    contactForm.patch(`/orders/${props.order.id}/contacts`, {
        onSuccess: () => editingContacts.value = false
    })
}

const st = statusMap[props.order.status] ?? { label: props.order.status, color: 'bg-gray-100 text-gray-600' }
</script>

<template>
    <ShopLayout>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Левая часть - детали заказа -->
            <div class="lg:col-span-2">
                <div class="flex items-center gap-3 mb-6">
                    <Link href="/profile" class="text-blue-600 hover:underline text-sm">← Мои заказы</Link>
                    <span class="text-gray-300">|</span>
                    <span class="font-mono text-gray-400 text-sm">{{ order.uuid }}</span>
                    <span :class="['px-3 py-1 rounded-full text-xs font-bold', st.color]">
                        {{ st.label }}
                    </span>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4">
                    <div class="p-6 border-b">
                        <h2 class="font-bold text-lg">Заказ #{{ order.id }}</h2>
                    </div>

                    <div class="p-6">
                        <!-- Контакты -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <div class="text-xs text-gray-400 uppercase font-bold mb-1">Телефон</div>
                                <div v-if="!editingContacts" class="font-medium">{{ order.phone }}</div>
                                <input
                                    v-else
                                    v-model="contactForm.phone"
                                    class="w-full px-3 py-2 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 text-sm focus:outline-none"
                                />
                            </div>
                            <div>
                                <div class="text-xs text-gray-400 uppercase font-bold mb-1">Адрес</div>
                                <div v-if="!editingContacts" class="font-medium">{{ order.address }}</div>
                                <textarea
                                    v-else
                                    v-model="contactForm.address"
                                    rows="2"
                                    class="w-full px-3 py-2 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 text-sm focus:outline-none resize-none"
                                />
                            </div>
                        </div>

                        <div v-if="['new', 'processing'].includes(order.status)" class="mb-6">
                            <div v-if="!editingContacts">
                                <button @click="editingContacts = true" class="text-blue-600 hover:underline text-sm">
                                    ✏️ Изменить контакты
                                </button>
                            </div>
                            <div v-else class="flex gap-2">
                                <button
                                    @click="saveContacts"
                                    :disabled="contactForm.processing"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700 transition disabled:opacity-50"
                                >
                                    Сохранить
                                </button>
                                <button @click="editingContacts = false" class="px-4 py-2 border rounded-xl text-sm hover:bg-gray-50 transition">
                                    Отмена
                                </button>
                            </div>
                        </div>

                        <!-- Товары -->
                        <div class="text-xs text-gray-400 uppercase font-bold mb-3">Состав заказа</div>
                        <div class="flex flex-col gap-3 mb-6">
                            <Link
                                v-for="item in order.items"
                                :key="item.id"
                                :href="`/product/${item.product_id}`"
                                class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition"
                            >
                                <img
                                    :src="item.product?.image ? `/storage/${item.product.image}` : 'https://placehold.co/50?text=?'"
                                    class="w-12 h-12 object-contain bg-white rounded-lg p-1 border"
                                />
                                <div class="flex-grow">
                                    <div class="font-medium text-sm text-gray-900">{{ item.product?.title }}</div>
                                    <div class="text-gray-400 text-xs">{{ item.quantity }} шт.</div>
                                </div>
                                <div class="font-bold text-sm">
                                    {{ formatPrice(item.price_at_purchase * item.quantity) }} ₽
                                </div>
                            </Link>
                        </div>

                        <!-- Итого и кнопка отмены -->
                        <div class="flex justify-between items-center">
                            <button
                                v-if="order.status === 'new'"
                                @click="cancelOrder"
                                class="px-4 py-2 border border-red-300 text-red-500 rounded-xl text-sm hover:bg-red-50 transition"
                            >
                                Отменить заказ
                            </button>
                            <div v-else />

                            <div class="text-right">
                                <div class="text-gray-400 text-sm">Итого:</div>
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ formatPrice(order.total_price) }} ₽
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Правая часть - чат -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col" style="height: 600px;">
                    <div class="p-4 border-b font-bold flex items-center gap-2">
                        💬 Поддержка
                    </div>

                    <!-- Сообщения -->
                    <div class="flex-grow overflow-y-auto p-4 flex flex-col gap-3 bg-gray-50" ref="chatBox">
                        <div v-if="messages.length === 0" class="text-center text-gray-400 text-sm my-auto">
                            История пуста
                        </div>
                        <div
                            v-for="msg in messages"
                            :key="msg.id"
                            :class="[
                                'max-w-4/5 px-4 py-2 rounded-2xl text-sm',
                                msg.sender_role === 'user'
                                    ? 'bg-blue-600 text-white self-end rounded-br-sm'
                                    : 'bg-white text-gray-800 self-start rounded-bl-sm border'
                            ]"
                        >
                            {{ msg.message }}
                            <div :class="['text-xs mt-1', msg.sender_role === 'user' ? 'text-blue-200' : 'text-gray-400']">
                                {{ formatTime(msg.created_at) }}
                            </div>
                        </div>
                    </div>

                    <!-- Инпут -->
                    <div class="p-3 border-t">
                        <div class="flex gap-2">
                            <input
                                v-model="msgForm.message"
                                @keydown.enter.prevent="sendMessage"
                                placeholder="Введите сообщение..."
                                class="flex-grow px-4 py-2 rounded-xl bg-gray-100 border-0 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            />
                            <button
                                @click="sendMessage"
                                :disabled="msgForm.processing"
                                class="px-4 py-2 bg-yellow-400 text-gray-900 rounded-xl font-bold hover:bg-yellow-300 transition text-sm"
                            >
                                →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>