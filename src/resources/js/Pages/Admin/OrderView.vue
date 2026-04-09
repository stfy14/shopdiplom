<script setup>
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    order: Object,
})

const messages = ref(props.order.messages ?? [])
const msgForm = useForm({ message: '' })
const statusForm = useForm({ status: props.order.status })
let pollInterval = null

const statusMap = {
    new:               { label: 'Новый',          color: 'bg-blue-100 text-blue-700' },
    processing:        { label: 'В обработке',     color: 'bg-yellow-100 text-yellow-700' },
    shipped:           { label: 'Отправлен',       color: 'bg-green-100 text-green-700' },
    cancelled:         { label: 'Отмена (Маг.)',   color: 'bg-red-100 text-red-700' },
    cancelled_by_user: { label: 'Отмена (Клиент)', color: 'bg-gray-100 text-gray-700' },
}

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price)
}

function formatTime(dt) {
    return new Date(dt).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
}

function updateStatus() {
    statusForm.patch(`/admin/orders/${props.order.id}/status`)
}

function loadMessages() {
    fetch(`/admin/orders/${props.order.id}/messages`)
        .then(r => r.json())
        .then(data => { messages.value = data })
}

function sendMessage() {
    if (!msgForm.message.trim()) return
    msgForm.post(`/admin/orders/${props.order.id}/messages`, {
        preserveState: true,
        onSuccess: () => {
            msgForm.reset()
            loadMessages()
        }
    })
}

onMounted(() => {
    pollInterval = setInterval(loadMessages, 3000)
})

onUnmounted(() => {
    clearInterval(pollInterval)
})

const st = statusMap[props.order.status] ?? { label: props.order.status, color: 'bg-gray-100 text-gray-600' }
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow-sm mb-6">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center gap-4">
                <Link href="/admin/orders" class="w-10 h-10 border rounded-xl flex items-center justify-center hover:bg-gray-50">
                    ←
                </Link>
                <div>
                    <h1 class="font-bold text-xl">Управление заказом</h1>
                    <div class="text-xs text-gray-400 font-mono">UUID: {{ order.uuid }}</div>
                </div>
                <span :class="['ml-auto px-3 py-1 rounded-full text-sm font-bold', st.color]">
                    {{ st.label }}
                </span>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Детали заказа -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4">
                        <div class="p-6 border-b">
                            <h2 class="font-bold text-lg">Заказ #{{ order.id }}</h2>
                        </div>
                        <div class="p-6">
                            <!-- Смена статуса -->
                            <div class="flex gap-3 mb-6 p-4 bg-gray-50 rounded-xl">
                                <select
                                    v-model="statusForm.status"
                                    class="flex-grow px-4 py-2.5 bg-white rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option v-for="(data, code) in statusMap" :key="code" :value="code">
                                        {{ data.label }}
                                    </option>
                                </select>
                                <button
                                    @click="updateStatus"
                                    :disabled="statusForm.processing"
                                    class="px-6 py-2.5 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition disabled:opacity-50"
                                >
                                    Обновить
                                </button>
                            </div>

                            <!-- Клиент -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <div class="text-xs text-gray-400 uppercase font-bold mb-1">Покупатель</div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">👤</div>
                                        <span class="font-medium">{{ order.user?.name }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 uppercase font-bold mb-1">Контакты</div>
                                    <div class="font-medium">{{ order.phone }}</div>
                                    <div class="text-gray-400 text-sm">{{ order.address }}</div>
                                </div>
                            </div>

                            <hr class="mb-6" />

                            <!-- Товары -->
                            <div class="text-xs text-gray-400 uppercase font-bold mb-3">Состав заказа</div>
                            <div class="flex flex-col gap-2 mb-6">
                                <Link
                                    v-for="item in order.items"
                                    :key="item.id"
                                    :href="`/product/${item.product_id}`"
                                    target="_blank"
                                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition"
                                >
                                    <img
                                        :src="item.product?.image ? `/storage/${item.product.image}` : 'https://placehold.co/50?text=?'"
                                        class="w-12 h-12 object-contain bg-white rounded-lg p-1 border"
                                    />
                                    <div class="flex-grow">
                                        <div class="font-medium text-sm text-gray-900">
                                            {{ item.product?.title }}
                                            <span class="text-xs text-gray-400 ml-1">↗</span>
                                        </div>
                                        <div class="text-gray-400 text-xs">{{ item.quantity }} шт.</div>
                                    </div>
                                    <div class="font-bold text-sm">
                                        {{ formatPrice(item.price_at_purchase * item.quantity) }} ₽
                                    </div>
                                </Link>
                            </div>

                            <div class="text-right">
                                <span class="text-gray-400 mr-2">Итого:</span>
                                <span class="text-2xl font-bold text-blue-600">{{ formatPrice(order.total_price) }} ₽</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Чат -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden flex flex-col" style="height: 600px;">
                        <div class="p-4 border-b font-bold flex items-center gap-2">
                            💬 Чат с клиентом
                        </div>

                        <div class="flex-grow overflow-y-auto p-4 flex flex-col gap-3 bg-gray-50">
                            <div v-if="messages.length === 0" class="text-center text-gray-400 text-sm my-auto">
                                История пуста
                            </div>
                            <div
                                v-for="msg in messages"
                                :key="msg.id"
                                :class="[
                                    'max-w-4/5 px-4 py-2 rounded-2xl text-sm',
                                    msg.sender_role === 'admin'
                                        ? 'bg-yellow-400 text-gray-900 self-end rounded-br-sm'
                                        : 'bg-white text-gray-800 self-start rounded-bl-sm border'
                                ]"
                            >
                                {{ msg.message }}
                                <div :class="['text-xs mt-1', msg.sender_role === 'admin' ? 'text-yellow-700' : 'text-gray-400']">
                                    {{ formatTime(msg.created_at) }}
                                </div>
                            </div>
                        </div>

                        <div class="p-3 border-t">
                            <div class="flex gap-2">
                                <input
                                    v-model="msgForm.message"
                                    @keydown.enter.prevent="sendMessage"
                                    placeholder="Введите сообщение..."
                                    class="flex-grow px-4 py-2 rounded-xl bg-gray-100 border-0 focus:outline-none focus:ring-2 focus:ring-yellow-400 text-sm"
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
        </div>
    </div>
</template>