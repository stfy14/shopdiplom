<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const items = computed(() => page.props.cartItems ?? [])

function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }
const total = () => items.value.reduce((sum, item) => sum + (item.product?.price_with_discount ?? 0) * item.quantity, 0)

function updateQuantity(productId, quantity) {
    if (quantity <= 0) { removeItem(productId); return }
    router.patch(`/cart/${productId}`, { quantity }, { preserveScroll: true, preserveState: true })
}
function handleInput(productId, e) {
    const val = parseInt(e.target.value)
    if (!isNaN(val)) updateQuantity(productId, val)
}
function removeItem(productId) { router.delete(`/cart/${productId}`, { preserveScroll: true, preserveState: true }) }
function clearNotification(productId) { router.patch(`/cart/${productId}/clear-notification`, {}, { preserveScroll: true, preserveState: true }) }
</script>

<template>
    <ShopLayout>
        <h1 class="text-2xl font-black mb-6 text-gray-900">Корзина</h1>

        <div v-if="items.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 flex flex-col gap-4">
                <div v-for="item in items" :key="item.id" class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-100 flex flex-col gap-4">
                    
                    <div v-if="item.old_price" class="bg-yellow-50 shadow-sm text-yellow-800 px-4 py-3 rounded-xl text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 border border-yellow-100">
                        <div>
                            <strong class="flex items-center gap-1.5 mb-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Внимание! Цена изменилась
                            </strong>
                            <div class="pl-1 text-yellow-700 font-medium">Была: <span class="line-through">{{ formatPrice(item.old_price) }} ₽</span>, стала: <span class="font-bold">{{ formatPrice(item.product?.price_with_discount) }} ₽</span>.</div>
                            <span v-if="item.price_change_reason" class="text-xs text-yellow-600/90 mt-1 block pl-1">{{ item.price_change_reason }}</span>
                        </div>
                        <button @click="clearNotification(item.product_id)" class="w-full sm:w-auto text-yellow-700 hover:text-yellow-900 font-bold bg-yellow-200/50 hover:bg-yellow-300/80 px-4 py-2.5 sm:py-2 rounded-xl transition">Понятно</button>
                    </div>

                    <!-- ИЗМЕНЕНО: Адаптивная верстка товаров -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex items-center gap-4 w-full sm:w-auto sm:flex-grow">
                            <div class="relative flex-shrink-0">
                                <span v-if="item.product?.discount > 0" class="absolute -top-2 -left-2 bg-red-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-md shadow-sm z-10">-{{ item.product.discount }}%</span>
                                <img :src="item.product?.image ? `/storage/${item.product.image}` : 'https://placehold.co/80?text=?'" class="w-20 h-20 sm:w-24 sm:h-24 object-contain rounded-xl bg-gray-50 border border-gray-100 p-2" />
                            </div>
                            
                            <div class="flex-grow min-w-0">
                                <Link :href="`/product/${item.product_id}`" class="font-bold text-gray-900 hover:text-blue-600 transition text-sm sm:text-base block line-clamp-2">{{ item.product?.title }}</Link>
                                <div class="mt-1 sm:mt-1.5 flex items-center gap-2">
                                    <span class="text-blue-600 font-black text-sm sm:text-base">{{ formatPrice(item.product?.price_with_discount) }} ₽</span>
                                    <span v-if="item.product?.discount > 0" class="text-[11px] sm:text-xs text-gray-400 line-through font-medium">{{ formatPrice(item.product?.price) }} ₽</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-4 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-0 border-gray-50">
                            <div class="flex items-center gap-1.5 bg-gray-50 p-1 rounded-xl border border-gray-100">
                                <button @click="updateQuantity(item.product_id, item.quantity - 1)" class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-white hover:bg-red-50 text-gray-500 hover:text-red-500 shadow-sm flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                                </button>
                                <input type="number" :value="item.quantity" @change="handleInput(item.product_id, $event)" min="1" :max="item.product?.quantity" 
                                    class="w-10 sm:w-12 text-center font-bold bg-transparent border-none p-0 focus:ring-0 text-gray-800 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                <button @click="updateQuantity(item.product_id, item.quantity + 1)" :disabled="item.quantity >= item.product?.quantity" class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-white hover:bg-blue-50 text-gray-500 hover:text-blue-500 shadow-sm flex items-center justify-center transition disabled:opacity-40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                            <div class="text-right sm:min-w-[110px] flex-shrink-0 flex flex-col items-end">
                                <div class="font-black text-gray-900 text-lg">{{ formatPrice((item.product?.price_with_discount ?? 0) * item.quantity) }} ₽</div>
                                <button @click="removeItem(item.product_id)" class="text-gray-400 hover:text-red-500 text-sm mt-0.5 font-bold transition">Удалить</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100 sticky top-24">
                    <h2 class="text-lg font-black mb-6 text-gray-900">Ваш заказ</h2>
                    <div class="flex justify-between items-center mb-6 pb-6 border-b border-gray-100">
                        <span class="text-gray-500 font-bold">Сумма:</span>
                        <span class="text-3xl font-black text-gray-900 tracking-tight">{{ formatPrice(total()) }} ₽</span>
                    </div>
                    <Link href="/checkout" class="block w-full py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition text-center shadow-lg shadow-blue-600/20">Оформить заказ</Link>
                    <Link href="/" class="block text-center text-gray-500 hover:text-blue-600 font-bold text-sm mt-5 transition">Продолжить покупки</Link>
                </div>
            </div>
        </div>

        <div v-else class="text-center bg-white rounded-3xl py-24 shadow-sm border border-gray-100">
            <div class="w-24 h-24 bg-gray-50 border border-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="text-gray-500 font-bold text-lg mb-8">Корзина пуста</div>
            <Link href="/" class="inline-block px-8 py-3.5 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition shadow-md">Перейти в каталог</Link>
        </div>
    </ShopLayout>
</template>