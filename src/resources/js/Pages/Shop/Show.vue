<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    product: Object,
    breadcrumbs: Array,
})

const page = usePage()
const cartItems = computed(() => page.props.cartItems ?? [])
const cartItem = computed(() => cartItems.value.find(i => i.product_id === props.product.id) ?? null)

function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }

function updateCartQty(change) {
    const current = cartItem.value?.quantity ?? 0
    const newQty = current + change
    if (newQty <= 0) {
        router.delete(`/cart/${props.product.id}`, { preserveScroll: true, preserveState: true })
    } else {
        router.patch(`/cart/${props.product.id}`, { quantity: newQty }, { preserveScroll: true, preserveState: true })
    }
}
</script>

<template>
    <ShopLayout>
        <!-- Хлебные крошки -->
        <div class="flex items-center gap-1.5 text-sm text-gray-400 font-medium mb-6 flex-wrap">
            <template v-if="breadcrumbs?.length">
                <template v-for="(crumb, i) in breadcrumbs" :key="i">
                    <Link v-if="crumb.href" :href="crumb.href" class="hover:text-gray-600 transition truncate max-w-[160px]">{{ crumb.title }}</Link>
                    <span v-else class="text-gray-700 font-bold truncate max-w-[200px]">{{ crumb.title }}</span>
                    <svg v-if="i < breadcrumbs.length - 1" class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </template>
            </template>
            <template v-else>
                <Link href="/" class="hover:text-gray-600 transition">Главная</Link>
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-700 font-bold">{{ product.title }}</span>
            </template>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-0">

                <!-- Фото -->
                <div class="md:col-span-6 lg:col-span-5 flex items-center justify-center p-8 md:p-12 bg-gray-50/50 relative">
                    <span v-if="product.discount > 0" class="absolute top-6 left-6 bg-red-500 text-white text-sm font-black px-3 py-1.5 rounded-xl shadow-sm z-10">-{{ product.discount }}%</span>
                    <span v-if="product.quantity === 0" class="absolute top-6 left-6 bg-gray-800/80 text-white text-sm font-bold px-3 py-1.5 rounded-xl z-10">Нет в наличии</span>
                    <img
                        :src="product.image ? `/storage/${product.image}` : 'https://placehold.co/400x300?text=Нет+фото'"
                        class="max-h-96 max-w-full object-contain drop-shadow-sm"
                        :style="product.quantity === 0 ? 'opacity:0.4;filter:grayscale(1)' : ''"
                    />
                </div>

                <!-- Инфо -->
                <div class="md:col-span-6 lg:col-span-7 p-8 md:p-10 flex flex-col bg-white">

                    <!-- Категория + кнопка редактирования для админа -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="text-xs font-bold tracking-widest text-blue-600 uppercase mt-2">
                            <Link v-if="product.category" :href="`/catalog/${product.category.code}`" class="hover:text-blue-800 transition">
                                {{ product.category.name }}
                            </Link>
                        </div>

                        <Link
                            v-if="$page.props.auth.user?.role === 'admin'"
                            :href="`/admin/products/${product.id}/edit`"
                            class="w-9 h-9 bg-white hover:bg-blue-50 text-gray-400 hover:text-blue-600 rounded-xl flex items-center justify-center transition shadow-sm border border-gray-100 flex-shrink-0"
                            title="Редактировать товар"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </Link>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-6 leading-tight">{{ product.title }}</h1>

                    <!-- Цена -->
                    <div class="flex items-end gap-4 mb-8">
                        <div>
                            <div v-if="product.discount > 0" class="text-gray-400 line-through text-sm font-medium mb-1">{{ formatPrice(product.price) }} ₽</div>
                            <div class="text-4xl font-black text-gray-900 tracking-tight">{{ formatPrice(product.price_with_discount ?? product.price) }} ₽</div>
                        </div>
                    </div>

                    <!-- Наличие -->
                    <div class="mb-8 flex items-center gap-3 flex-wrap">
                        <div v-if="product.quantity > 0" class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 text-green-600 rounded-lg text-sm font-bold border border-green-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            В наличии ({{ product.quantity }} шт.)
                        </div>
                        <div v-else class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-sm font-bold">
                            Нет в наличии
                        </div>
                        <div class="text-sm font-medium text-gray-400 border-l pl-3">Артикул: {{ product.id }}</div>
                    </div>

                    <!-- Кнопки корзины -->
                    <div v-if="product.quantity > 0" class="mb-8">
                        <!-- Уже в корзине — счётчик + перейти -->
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
                            <Link href="/cart" class="flex-1 py-3.5 bg-white border-2 border-blue-600 text-blue-600 font-bold rounded-xl hover:bg-blue-50 transition text-center">
                                Перейти в корзину
                            </Link>
                        </div>

                        <!-- Не в корзине -->
                        <Link
                            v-else
                            :href="`/cart/${product.id}`"
                            method="post"
                            as="button"
                            class="w-full md:w-auto px-10 py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 transition text-center flex items-center justify-center gap-3"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Добавить в корзину
                        </Link>
                    </div>
                    <div v-else class="mb-8">
                        <div class="w-full px-10 py-4 bg-gray-100 text-gray-400 font-bold rounded-xl text-center cursor-not-allowed">Нет в наличии</div>
                    </div>

                    <!-- Иконки-плюшки -->
                    <div class="grid grid-cols-2 gap-4 mt-auto pt-6 border-t border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            </div>
                            <div class="text-sm font-medium text-gray-600 leading-tight">Гарантия<br>качества</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-50 text-green-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                            </div>
                            <div class="text-sm font-medium text-gray-600 leading-tight">Быстрая<br>доставка</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            </div>
                            <div class="text-sm font-medium text-gray-600 leading-tight">Документы<br>и сертификаты</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            </div>
                            <div class="text-sm font-medium text-gray-600 leading-tight">Поддержка<br>специалистов</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Описание + Характеристики -->
            <div class="grid grid-cols-1 md:grid-cols-2 border-t border-gray-100">
                <div class="p-8 md:p-10 border-b md:border-b-0 md:border-r border-gray-100 bg-gray-50/30">
                    <h2 class="text-lg font-black text-gray-900 mb-4">Описание</h2>
                    <div class="text-gray-600 text-sm leading-relaxed whitespace-pre-wrap">{{ product.description || 'Описание отсутствует' }}</div>
                </div>
                <div class="p-8 md:p-10">
                    <h2 class="text-lg font-black text-gray-900 mb-4">Характеристики</h2>
                    <div v-if="product.characteristics?.length > 0" class="flex flex-col gap-1">
                        <div v-for="char in product.characteristics" :key="char.id" class="flex justify-between items-center py-2.5 border-b border-gray-100 last:border-0">
                            <span class="text-gray-500 text-sm font-medium">{{ char.characteristic?.name }}</span>
                            <span class="font-bold text-sm text-gray-900">{{ char.value }}</span>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-400">Характеристики не указаны</div>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>