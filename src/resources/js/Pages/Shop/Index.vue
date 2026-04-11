<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    products: Array,
    categories: Array,
    filters: Object,
    cartItems: [Array, Object],
})

const search = ref(props.filters?.q ?? '')

function filterByCategory(code) {
    router.get('/', { cat: code, q: search.value }, { preserveState: true })
}

function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }

const normalizedCartItems = computed(() => {
    if (!props.cartItems) return[]
    return Array.isArray(props.cartItems) ? props.cartItems : Object.values(props.cartItems)
})

function getCartItem(productId) {
    return normalizedCartItems.value.find(item => item.product_id === productId) ?? null
}

// Умная функция добавления/удаления
function updateCartQuantity(productId, currentQty, change) {
    const newQty = currentQty + change;
    if (newQty <= 0) {
        router.delete(`/cart/${productId}`, { preserveScroll: true, preserveState: true });
    } else {
        router.patch(`/cart/${productId}`, { quantity: newQty }, { preserveScroll: true, preserveState: true });
    }
}
</script>

<template>
    <ShopLayout>
        <!-- Баннер -->
        <div class="bg-gradient-to-br from-blue-900 to-blue-700 rounded-3xl p-8 md:p-12 mb-8 text-white shadow-lg relative overflow-hidden flex flex-col justify-center min-h-[240px]">
            <div class="relative z-10 max-w-2xl">
                <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-bold tracking-wider uppercase backdrop-blur-sm mb-4 inline-block">Официальный дилер</span>
                <h1 class="text-3xl md:text-4xl font-black mb-4 leading-tight">Промышленное грузоподъемное оборудование</h1>
                <p class="text-blue-100 text-sm md:text-base font-medium">Надежные лебедки, стропы и захваты для любых задач с гарантией качества.</p>
            </div>
            <!-- Декоративный элемент -->
            <div class="absolute -right-20 -top-20 opacity-10 pointer-events-none">
                <svg class="w-96 h-96 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
        </div>

        <!-- Категории -->
        <div class="flex flex-wrap gap-2 mb-8">
            <button @click="filterByCategory('')" :class="['px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-sm', !filters.cat ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']">Все товары</button>
            <button v-for="cat in categories" :key="cat.id" @click="filterByCategory(cat.code)" :class="['px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-sm', filters.cat === cat.code ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']">
                {{ cat.name }}
            </button>
        </div>

        <div v-if="filters.q" class="mb-6 px-2 text-gray-500 font-medium">Результаты по запросу: <span class="text-gray-900 font-bold">«{{ filters.q }}»</span></div>

        <!-- Сетка товаров -->
        <div v-if="products.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            <div v-for="product in products" :key="product.id" class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 flex flex-col overflow-hidden border border-gray-100 group">
                <Link :href="`/product/${product.id}`" class="block h-48 md:h-56 bg-gray-50/50 flex items-center justify-center p-6 relative">
                    <span v-if="product.discount > 0" class="absolute top-3 left-3 bg-red-500 text-white text-xs font-black px-2.5 py-1 rounded-lg shadow-sm z-10">-{{ product.discount }}%</span>
                    <span v-if="product.quantity === 0" class="absolute top-3 left-3 bg-gray-800/80 backdrop-blur-sm text-white text-xs font-bold px-2.5 py-1 rounded-lg z-10">Нет в наличии</span>
                    <img :src="product.image ? `/storage/${product.image}` : 'https://placehold.co/300x200?text=Нет+фото'" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500" :style="product.quantity === 0 ? 'opacity: 0.4; filter: grayscale(1);' : ''" />
                </Link>

                <div class="p-5 flex flex-col flex-grow">
                    <div class="text-xs font-bold text-gray-400 uppercase mb-1.5 tracking-wide">{{ product.category?.name }}</div>
                    <Link :href="`/product/${product.id}`" class="font-bold text-gray-900 hover:text-blue-600 mb-3 text-sm md:text-base leading-tight">{{ product.title }}</Link>

                    <div class="mt-auto flex items-end justify-between gap-2">
                        <div>
                            <div v-if="product.discount > 0" class="text-xs text-gray-400 line-through mb-0.5 font-medium">{{ formatPrice(product.price) }} ₽</div>
                            <div class="text-lg md:text-xl font-black text-gray-900 leading-none">{{ formatPrice(product.price_with_discount ?? product.price) }} ₽</div>
                        </div>

                        <div v-if="product.quantity > 0">
                            <div v-if="getCartItem(product.id)" class="flex items-center bg-gray-100 rounded-xl p-1 border border-gray-200">
                                <button @click="updateCartQuantity(product.id, getCartItem(product.id).quantity, -1)" class="w-7 h-7 rounded-lg bg-white shadow-sm flex items-center justify-center text-gray-600 hover:text-red-500 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                                </button>
                                <span class="w-6 text-center font-bold text-sm text-gray-800">{{ getCartItem(product.id).quantity }}</span>
                                <button @click="updateCartQuantity(product.id, getCartItem(product.id).quantity, 1)" class="w-7 h-7 rounded-lg bg-white shadow-sm flex items-center justify-center text-gray-600 hover:text-blue-600 transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                            <Link v-else :href="`/cart/${product.id}`" method="post" as="button" class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </Link>
                        </div>
                        <div v-else class="px-3 py-2 bg-gray-100 text-gray-400 text-xs font-bold rounded-xl cursor-not-allowed">Нет</div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center bg-white rounded-3xl py-20 shadow-sm border border-gray-100">
            <div class="text-5xl mb-4">🔍</div>
            <div class="text-gray-500 font-medium text-lg">По вашему запросу ничего не найдено</div>
            <button @click="filterByCategory('')" class="mt-4 text-blue-600 font-bold hover:underline">Сбросить фильтры</button>
        </div>
    </ShopLayout>
</template>