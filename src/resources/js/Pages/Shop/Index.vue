<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    products: Array,
    categories: Array,
    filters: Object,
    cartItems: [Array, Object], // Разрешаем принимать и массив, и объект
})

const search = ref(props.filters?.q ?? '')

function filterByCategory(code) {
    router.get('/', { cat: code, q: search.value }, { preserveState: true })
}

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price)
}

// 1. Приводим данные корзины к единому формату (массиву)
const normalizedCartItems = computed(() => {
    if (!props.cartItems) return[]
    return Array.isArray(props.cartItems) ? props.cartItems : Object.values(props.cartItems)
})

// 2. Обновляем getCartItem: теперь ищем товар внутри нормализованного массива
function getCartItem(productId) {
    return normalizedCartItems.value.find(item => item.product_id === productId) ?? null
}
</script>

<template>
    <ShopLayout>
        <!-- Категории -->
        <div class="flex flex-wrap gap-2 mb-6">
            <button
                @click="filterByCategory('')"
                :class="[
                    'px-4 py-2 rounded-full text-sm font-medium transition',
                    !filters.cat ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border border-blue-200 hover:bg-blue-50'
                ]"
            >
                Все
            </button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                @click="filterByCategory(cat.code)"
                :class="[
                    'px-4 py-2 rounded-full text-sm font-medium transition',
                    filters.cat === cat.code ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 border border-blue-200 hover:bg-blue-50'
                ]"
            >
                {{ cat.name }}
            </button>
        </div>

        <!-- Поисковая строка если есть результат -->
        <div v-if="filters.q" class="mb-4 text-gray-600">
            Результаты поиска: <strong>{{ filters.q }}</strong>
        </div>

        <!-- Сетка товаров -->
        <div v-if="products.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <div
                v-for="product in products"
                :key="product.id"
                class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all hover:-translate-y-1 flex flex-col overflow-hidden"
            >
                <!-- Фото -->
                <Link :href="`/product/${product.id}`" class="block h-52 bg-white flex items-center justify-center p-4 relative">
                    <span
                        v-if="product.discount > 0"
                        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full"
                    >
                        -{{ product.discount }}%
                    </span>
                    <span
                        v-if="product.quantity === 0"
                        class="absolute top-2 left-2 bg-gray-400 text-white text-xs font-bold px-2 py-1 rounded-full"
                    >
                        Нет в наличии
                    </span>
                    <img
                        :src="product.image ? `/storage/${product.image}` : 'https://placehold.co/300x200?text=Нет+фото'"
                        class="max-h-full max-w-full object-contain"
                        :style="product.quantity === 0 ? 'opacity: 0.5' : ''"
                    />
                </Link>

                <!-- Инфо -->
                <div class="p-4 flex flex-col flex-grow">
                    <Link :href="`/product/${product.id}`" class="font-semibold text-gray-900 hover:text-blue-600 mb-1 text-sm leading-tight">
                        {{ product.title }}
                    </Link>
                    <div class="text-xs text-gray-400 uppercase mb-3">{{ product.category?.name }}</div>

                    <div class="mt-auto flex items-center justify-between">
                        <div>
                            <div v-if="product.discount > 0" class="text-xs text-gray-400 line-through">
                                {{ formatPrice(product.price) }} ₽
                            </div>
                            <div class="text-lg font-bold text-gray-900">
                                {{ formatPrice(product.price_with_discount ?? product.price) }} ₽
                            </div>
                        </div>

                        <!-- Кнопка / количество -->
                        <div v-if="product.quantity > 0">
                            <div v-if="getCartItem(product.id)" class="flex items-center gap-1">
                                <Link
                                    :href="`/cart/${product.id}`"
                                    method="patch"
                                    :data="{ quantity: getCartItem(product.id).quantity - 1 }"
                                    as="button"
                                    class="w-7 h-7 rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-500 transition font-bold text-sm"
                                >
                                    −
                                </Link>
                                <span class="w-6 text-center font-bold text-sm">{{ getCartItem(product.id).quantity }}</span>
                                <Link
                                    :href="`/cart/${product.id}`"
                                    method="patch"
                                    :data="{ quantity: getCartItem(product.id).quantity + 1 }"
                                    as="button"
                                    class="w-7 h-7 rounded-full bg-gray-100 hover:bg-blue-100 hover:text-blue-500 transition font-bold text-sm"
                                >
                                    +
                                </Link>
                            </div>
                            <Link
                                v-else
                                :href="`/cart/${product.id}`"
                                method="post"
                                as="button"
                                class="px-3 py-2 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition"
                            >
                                Купить
                            </Link>
                        </div>
                        
                        <button v-else disabled class="px-3 py-2 bg-gray-200 text-gray-400 text-sm rounded-xl cursor-not-allowed">
                            Нет
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center text-gray-400 py-20 text-lg">
            Товары не найдены
        </div>
    </ShopLayout>
</template>