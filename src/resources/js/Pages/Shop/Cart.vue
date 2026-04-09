<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    items: Array,
})

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price)
}

const total = () => props.items.reduce((sum, item) => {
    return sum + (item.product?.price ?? 0) * item.quantity
}, 0)

function updateQuantity(productId, quantity) {
    if (quantity <= 0) {
        removeItem(productId)
        return
    }
    router.patch(`/cart/${productId}`, { quantity }, {
        preserveScroll: true,
        preserveState: true,
    })
}

function handleInput(productId, e) {
    const val = parseInt(e.target.value)
    if (!isNaN(val)) updateQuantity(productId, val)
}

function removeItem(productId) {
    router.delete(`/cart/${productId}`, {
        preserveScroll: true,
        preserveState: true,
    })
}
</script>

<template>
    <ShopLayout>
        <h1 class="text-2xl font-bold mb-6">🛒 Корзина</h1>

        <div v-if="items.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 flex flex-col gap-4">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="bg-white rounded-2xl p-4 shadow-sm flex items-center gap-4"
                >
                    <img
                        :src="item.product?.image ? `/storage/${item.product.image}` : 'https://placehold.co/80?text=?'"
                        class="w-20 h-20 object-contain rounded-xl bg-gray-50 p-2 flex-shrink-0"
                    />

                    <div class="flex-grow">
                        <Link :href="`/product/${item.product_id}`" class="font-semibold text-gray-900 hover:text-blue-600">
                            {{ item.product?.title }}
                        </Link>
                        <div class="text-blue-600 font-bold mt-1">
                            {{ formatPrice(item.product?.price) }} ₽
                        </div>
                    </div>

                    <!-- Количество -->
                    <div class="flex items-center gap-2">
                        <button
                            @click="updateQuantity(item.product_id, item.quantity - 1)"
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-500 transition font-bold"
                        >
                            −
                        </button>
                        <input
                            type="number"
                            :value="item.quantity"
                            @change="handleInput(item.product_id, $event)"
                            min="1"
                            :max="item.product?.quantity"
                            class="w-12 text-center font-bold border border-gray-200 rounded-lg py-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <button
                            @click="updateQuantity(item.product_id, item.quantity + 1)"
                            :disabled="item.quantity >= item.product?.quantity"
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-blue-100 hover:text-blue-500 transition disabled:opacity-40 font-bold"
                        >
                            +
                        </button>
                    </div>

                    <div class="text-right min-w-24 flex-shrink-0">
                        <div class="font-bold text-gray-900">
                            {{ formatPrice(item.product?.price * item.quantity) }} ₽
                        </div>
                        <button
                            @click="removeItem(item.product_id)"
                            class="text-red-400 hover:text-red-600 text-sm mt-1 transition"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 shadow-sm sticky top-24">
                    <h2 class="text-lg font-bold mb-4">Итого</h2>
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-gray-500">Сумма:</span>
                        <span class="text-2xl font-bold">{{ formatPrice(total()) }} ₽</span>
                    </div>
                    <Link
                        href="/checkout"
                        class="block w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition text-center"
                    >
                        Оформить заказ
                    </Link>
                    <Link href="/" class="block text-center text-gray-400 hover:text-gray-600 text-sm mt-3 transition">
                        Продолжить покупки
                    </Link>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-24">
            <div class="text-6xl mb-4">🛒</div>
            <div class="text-gray-400 text-lg mb-6">Корзина пуста</div>
            <Link href="/" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition">
                Перейти в каталог
            </Link>
        </div>
    </ShopLayout>
</template>