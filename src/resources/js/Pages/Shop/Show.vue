<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    product: Object,
})

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price)
}
</script>

<template>
    <ShopLayout>
        <div class="mb-4">
            <Link href="/" class="text-blue-600 hover:underline text-sm">← Назад в каталог</Link>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
                <!-- Фото -->
                <div class="flex items-center justify-center p-12 bg-white min-h-80">
                    <img
                        :src="product.image ? `/storage/${product.image}` : 'https://placehold.co/400x300?text=Нет+фото'"
                        class="max-h-80 max-w-full object-contain"
                    />
                </div>

                <!-- Инфо -->
                <div class="p-8 flex flex-col">
                    <div class="text-sm text-gray-400 uppercase mb-2">{{ product.category?.name }}</div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ product.title }}</h1>

                    <!-- Цена -->
                    <div class="mb-6">
                        <div v-if="product.discount > 0" class="text-gray-400 line-through text-sm">
                            {{ formatPrice(product.price) }} ₽
                        </div>
                        <div class="text-3xl font-bold text-gray-900">
                            {{ formatPrice(product.price_with_discount ?? product.price) }} ₽
                        </div>
                        <span v-if="product.discount > 0" class="inline-block mt-1 bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded-full">
                            Скидка {{ product.discount }}%
                        </span>
                    </div>

                    <!-- Наличие -->
                    <div class="mb-6">
                        <span v-if="product.quantity > 0" class="text-green-600 font-medium">
                            ✓ В наличии ({{ product.quantity }} шт.)
                        </span>
                        <span v-else class="text-red-500 font-medium">
                            ✗ Нет в наличии
                        </span>
                    </div>

                    <!-- Кнопка -->
                    <Link
                        v-if="product.quantity > 0"
                        :href="`/cart/${product.id}`"
                        method="post"
                        as="button"
                        class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition text-center mb-4"
                    >
                        Добавить в корзину
                    </Link>
                    <button v-else disabled class="w-full py-3 bg-gray-200 text-gray-400 font-bold rounded-xl cursor-not-allowed mb-4">
                        Нет в наличии
                    </button>

                    <!-- Описание -->
                    <div v-if="product.description" class="text-gray-600 text-sm leading-relaxed">
                        {{ product.description }}
                    </div>
                </div>
            </div>

            <!-- Характеристики -->
            <div v-if="product.characteristics?.length > 0" class="border-t p-8">
                <h2 class="text-lg font-bold mb-4">Характеристики</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div
                        v-for="char in product.characteristics"
                        :key="char.id"
                        class="flex justify-between items-center py-2 border-b border-gray-100"
                    >
                        <span class="text-gray-500 text-sm">{{ char.characteristic?.name }}</span>
                        <span class="font-medium text-sm">{{ char.value }}</span>
                    </div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>