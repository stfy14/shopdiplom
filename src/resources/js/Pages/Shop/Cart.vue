<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const items = computed(() => page.props.cartItems ??[])

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
                    
                    <div v-if="item.old_price" class="bg-yellow-50 shadow-sm text-yellow-800 px-4 py-3 rounded-xl text-sm flex justify-between items-start md:items-center">
                        <div>
                            <strong class="flex items-center gap-1.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Внимание! Цена изменилась
                            </strong>
                            <div class="mt-1 pl-1">Была: {{ formatPrice(item.old_price) }} ₽, стала: {{ formatPrice(item.product?.price_with_discount) }} ₽.</div>
                            <span v-if="item.price_change_reason" class="text-xs text-yellow-600 mt-1 block pl-1">{{ item.price_change_reason }}</span>
                        </div>
                        <button @click="clearNotification(item.product_id)" class="text-yellow-700 hover:text-yellow-900 font-bold ml-4 whitespace-nowrap bg-yellow-200/50 hover:bg-yellow-300/50 px-3 py-1.5 rounded-lg transition">Понятно</button>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="relative flex-shrink-0">
                            <span v-if="item.product?.discount > 0" class="absolute -top-2 -left-2 bg-red-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-md shadow-sm z-10">-{{ item.product.discount }}%</span>
                            <img :src="item.product?.image ? `/storage/${item.product.image}` : 'https://placehold.co/80?text=?'" class="w-20 h-20 object-contain rounded-xl bg-gray-50 border border-gray-100 p-2" />
                        </div>
                        
                        <div class="flex-grow">
                            <Link :href="`/product/${item.product_id}`" class="font-bold text-gray-900 hover:text-blue-600 transition">{{ item.product?.title }}</Link>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-blue-600 font-black">{{ formatPrice(item.product?.price_with_discount) }} ₽</span>
                                <span v-if="item.product?.discount > 0" class="text-xs text-gray-400 line-through font-medium">{{ formatPrice(item.product?.price) }} ₽</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 sm:ml-auto">
                            <div class="flex items-center gap-1.5 bg-gray-50 p-1 rounded-xl border border-gray-100">
                                <button @click="updateQuantity(item.product_id, item.quantity - 1)" class="w-8 h-8 rounded-lg bg-white hover:bg-red-50 text-gray-500 hover:text-red-500 shadow-sm flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                                </button>
                                <input type="number" :value="item.quantity" @change="handleInput(item.product_id, $event)" min="1" :max="item.product?.quantity" 
                                    class="w-10 text-center font-bold bg-transparent border-none p-0 focus:ring-0 text-gray-800[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                <button @click="updateQuantity(item.product_id, item.quantity + 1)" :disabled="item.quantity >= item.product?.quantity" class="w-8 h-8 rounded-lg bg-white hover:bg-blue-50 text-gray-500 hover:text-blue-500 shadow-sm flex items-center justify-center transition disabled:opacity-40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                            <div class="text-right min-w-[100px] flex-shrink-0">
                                <div class="font-black text-gray-900 text-lg">{{ formatPrice((item.product?.price_with_discount ?? 0) * item.quantity) }} ₽</div>
                                <button @click="removeItem(item.product_id)" class="text-gray-400 hover:text-red-500 text-sm mt-0.5 font-medium transition">Удалить</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-gray-100 sticky top-24">
                    <h2 class="text-lg font-black mb-6 text-gray-900">Ваш заказ</h2>
                    <div class="flex justify-between items-center mb-6 pb-6 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Сумма:</span>
                        <span class="text-3xl font-black text-gray-900 tracking-tight">{{ formatPrice(total()) }} ₽</span>
                    </div>
                    <Link href="/checkout" class="block w-full py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition text-center shadow-lg shadow-blue-600/20">Оформить заказ</Link>
                    <Link href="/" class="block text-center text-gray-500 hover:text-blue-600 font-bold text-sm mt-5 transition">Продолжить покупки</Link>
                </div>
            </div>
        </div>

        <div v-else class="text-center bg-white rounded-3xl py-24 shadow-sm border border-gray-100">
            <svg class="w-24 h-24 text-gray-300 mb-4 mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.343 1.087-.835l1.823-6.44a1.125 1.125 0 00-.44-1.229l-5.432-4.075a1.125 1.125 0 00-1.518.056L5.64 5.39a1.125 1.125 0 00-.056 1.518l3.65 4.563M7.5 14.25L5.106 5.106M17.25 20.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM7.5 20.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
            <div class="text-gray-500 text-lg mb-8 font-medium">Корзина пуста</div>
            <Link href="/" class="px-8 py-3.5 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition shadow-md">Перейти в каталог</Link>
        </div>
    </ShopLayout>
</template>