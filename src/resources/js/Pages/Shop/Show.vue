<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    product: Object,
    breadcrumbs: Array,
})

const page = usePage()
const cartItems = computed(() => page.props.cartItems ?? [])
const cartItem = computed(() => cartItems.value.find(i => i.product_id === props.product.id) ?? null)
const activeImage = ref(null)

function formatPrice(p) { return new Intl.NumberFormat('ru-RU').format(p) }

function addToCart() {
    router.post(`/cart/${props.product.id}`, {}, { preserveScroll: true, preserveState: true })
}

function updateCartQty(change) {
    const current = cartItem.value?.quantity ?? 0
    const newQty = current + change
    if (newQty <= 0) {
        router.delete(`/cart/${props.product.id}`, { preserveScroll: true, preserveState: true })
    } else {
        router.patch(`/cart/${props.product.id}`, { quantity: newQty }, { preserveScroll: true, preserveState: true })
    }
}

const characteristics = computed(() => props.product.characteristics ?? [])
</script>

<template>
    <ShopLayout>
        <!-- Хлебные крошки -->
        <div class="flex items-center gap-1.5 text-sm text-gray-400 font-medium mb-6 flex-wrap">
            <template v-for="(crumb, i) in breadcrumbs" :key="i">
                <Link v-if="crumb.href" :href="crumb.href" class="hover:text-gray-600 transition truncate max-w-[160px]">{{ crumb.title }}</Link>
                <span v-else class="text-gray-700 font-bold truncate max-w-[200px]">{{ crumb.title }}</span>
                <svg v-if="i < breadcrumbs.length - 1" class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </template>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 mb-12">
            <!-- Изображение -->
            <div class="flex flex-col gap-4">
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm flex items-center justify-center p-8 aspect-square relative overflow-hidden">
                    <span v-if="product.discount > 0" class="absolute top-4 left-4 bg-red-500 text-white text-sm font-black px-3 py-1.5 rounded-xl shadow-sm">
                        -{{ product.discount }}%
                    </span>
                    <span v-if="product.quantity === 0" class="absolute top-4 left-4 bg-gray-800 text-white text-sm font-bold px-3 py-1.5 rounded-xl">
                        Нет в наличии
                    </span>
                    <img
                        v-if="product.image"
                        :src="`/storage/${product.image}`"
                        :alt="product.title"
                        class="max-w-full max-h-full object-contain"
                        :style="product.quantity === 0 ? 'opacity:0.4;filter:grayscale(1)' : ''"
                    />
                    <div v-else class="flex flex-col items-center gap-3 text-gray-300">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21"/>
                        </svg>
                        <span class="text-sm font-medium">Фото недоступно</span>
                    </div>
                </div>
            </div>

            <!-- Детали товара -->
            <div class="flex flex-col">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                    <Link v-if="product.category" :href="`/catalog/${product.category.code}`" class="hover:text-blue-500 transition">
                        {{ product.category.name }}
                    </Link>
                </div>

                <h1 class="text-2xl md:text-3xl font-black text-gray-900 leading-snug mb-4">{{ product.title }}</h1>

                <!-- Цена -->
                <div class="flex items-end gap-3 mb-6">
                    <div class="text-4xl font-black text-gray-900">
                        {{ formatPrice(product.price_with_discount ?? product.price) }} ₽
                    </div>
                    <div v-if="product.discount > 0" class="mb-1">
                        <div class="text-lg text-gray-400 line-through font-medium">{{ formatPrice(product.price) }} ₽</div>
                        <div class="text-xs font-bold text-red-500">Скидка {{ product.discount }}%</div>
                    </div>
                </div>

                <!-- Наличие -->
                <div class="flex items-center gap-2 mb-6">
                    <div v-if="product.quantity > 0" class="flex items-center gap-2 px-3 py-1.5 bg-green-50 text-green-700 rounded-xl border border-green-100">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-sm font-bold">В наличии: {{ product.quantity }} шт.</span>
                    </div>
                    <div v-else class="flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-600 rounded-xl border border-red-100">
                        <span class="w-2 h-2 rounded-full bg-red-400"></span>
                        <span class="text-sm font-bold">Нет в наличии</span>
                    </div>
                </div>

                <!-- Кнопки корзины -->
                <div v-if="product.quantity > 0" class="mb-8">
                    <div v-if="cartItem" class="flex items-center gap-4">
                        <div class="flex items-center gap-2 bg-gray-100 p-1.5 rounded-2xl border border-gray-200">
                            <button @click="updateCartQty(-1)" class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-600 hover:text-red-500 hover:bg-red-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                            </button>
                            <span class="w-12 text-center font-black text-gray-900 text-lg">{{ cartItem.quantity }}</span>
                            <button @click="updateCartQty(1)" :disabled="cartItem.quantity >= product.quantity" class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition disabled:opacity-40">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        <Link href="/cart" class="flex-1 py-3 bg-white border-2 border-blue-600 text-blue-600 font-bold rounded-2xl hover:bg-blue-50 transition text-center">
                            Перейти в корзину
                        </Link>
                    </div>
                    <button v-else @click="addToCart" class="w-full py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-600/25 text-lg flex items-center justify-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Добавить в корзину
                    </button>
                </div>
                <div v-else class="mb-8">
                    <div class="w-full py-4 bg-gray-100 text-gray-400 font-bold rounded-2xl text-center cursor-not-allowed">Нет в наличии</div>
                </div>

                <!-- Описание -->
                <div v-if="product.description" class="p-5 bg-gray-50 rounded-2xl border border-gray-100 mb-6">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Описание</h3>
                    <p class="text-sm text-gray-700 leading-relaxed font-medium">{{ product.description }}</p>
                </div>

                <!-- Быстрые плюшки -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center gap-2.5 p-3 bg-white rounded-xl border border-gray-100 shadow-sm">
                        <div class="w-8 h-8 bg-green-50 text-green-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700">Сертифицировано</span>
                    </div>
                    <div class="flex items-center gap-2.5 p-3 bg-white rounded-xl border border-gray-100 shadow-sm">
                        <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700">Быстрая доставка</span>
                    </div>
                    <div class="flex items-center gap-2.5 p-3 bg-white rounded-xl border border-gray-100 shadow-sm">
                        <div class="w-8 h-8 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700">Документы</span>
                    </div>
                    <div class="flex items-center gap-2.5 p-3 bg-white rounded-xl border border-gray-100 shadow-sm">
                        <div class="w-8 h-8 bg-orange-50 text-orange-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700">Гарантия 12 мес.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Характеристики -->
        <div v-if="characteristics.length > 0" class="mb-12">
            <h2 class="text-xl font-black text-gray-900 mb-4">Характеристики</h2>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div
                    v-for="(char, i) in characteristics"
                    :key="char.id"
                    :class="['flex items-center gap-4 px-6 py-4', i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50']"
                >
                    <div class="w-5/12 text-sm font-bold text-gray-500">{{ char.characteristic?.name }}</div>
                    <div class="flex-1 h-px border-b border-dashed border-gray-200"></div>
                    <div class="w-5/12 text-sm font-black text-gray-900 text-right">{{ char.value }}</div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>