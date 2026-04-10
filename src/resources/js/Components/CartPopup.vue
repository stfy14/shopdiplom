<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    items:[Array, Object], // Разрешаем принимать и массив, и объект
    show: Boolean,
})

const emit = defineEmits(['close'])

// Приводим данные к массиву в любом случае
const normalizedItems = computed(() => {
    if (!props.items) return[]
    return Array.isArray(props.items) ? props.items : Object.values(props.items)
})

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price)
}

const total = () => normalizedItems.value.reduce((sum, item) => {
    return sum + (item.product?.price ?? 0) * item.quantity
}, 0)

function removeItem(productId) {
    router.delete(`/cart/${productId}`, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {},
    })
}
</script>

<template>
    <Transition name="popup">
        <div v-if="show" class="fixed inset-0 z-50 flex justify-end">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-black/30" @click="emit('close')" />

            <!-- Панель -->
            <div class="relative w-full max-w-sm bg-white h-full shadow-2xl flex flex-col">
                <div class="p-5 border-b flex items-center justify-between">
                    <h2 class="font-bold text-lg">🛒 Корзина</h2>
                    <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
                </div>

                <div class="flex-grow overflow-y-auto p-4">
                    <!-- Заменили items на normalizedItems -->
                    <div v-if="normalizedItems.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                        <div class="text-5xl mb-3">🛒</div>
                        <div>Корзина пуста</div>
                    </div>

                    <div v-else class="flex flex-col gap-3">
                        <div
                            v-for="item in normalizedItems"
                            :key="item.id"
                            class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50"
                        >
                            <img
                                :src="item.product?.image ? `/storage/${item.product.image}` : 'https://placehold.co/50?text=?'"
                                class="w-14 h-14 object-contain bg-gray-50 rounded-lg p-1"
                            />
                            <div class="flex-grow min-w-0">
                                <div class="font-medium text-sm truncate">{{ item.product?.title }}</div>
                                <div class="text-blue-600 text-sm font-bold">
                                    {{ formatPrice(item.product?.price) }} ₽
                                </div>
                                <div class="text-gray-400 text-xs">{{ item.quantity }} шт.</div>
                            </div>
                            <button @click="removeItem(item.product_id)" class="text-red-400 hover:text-red-600 text-lg flex-shrink-0">
                                ✕
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Заменили items на normalizedItems -->
                <div v-if="normalizedItems.length > 0" class="p-4 border-t">
                    <div class="flex justify-between mb-4">
                        <span class="text-gray-500">Итого:</span>
                        <span class="font-bold text-xl">{{ formatPrice(total()) }} ₽</span>
                    </div>
                    <Link
                        href="/checkout"
                        @click="emit('close')"
                        class="block w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition text-center mb-2"
                    >
                        Оформить заказ
                    </Link>
                    <Link
                        href="/cart"
                        @click="emit('close')"
                        class="block w-full py-2 text-center text-gray-500 hover:text-blue-600 text-sm transition"
                    >
                        Открыть корзину →
                    </Link>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.popup-enter-active, .popup-leave-active { transition: opacity 0.2s ease; }
.popup-enter-active .relative, .popup-leave-active .relative { transition: transform 0.25s ease; }
.popup-enter-from, .popup-leave-to { opacity: 0; }
.popup-enter-from .relative { transform: translateX(100%); }
.popup-leave-to .relative { transform: translateX(100%); }
</style>