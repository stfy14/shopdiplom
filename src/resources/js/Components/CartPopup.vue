<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const props = defineProps({ show: Boolean })
const emit = defineEmits(['close'])

const normalizedItems = computed(() => page.props.cartItems ??[])

function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }
const total = () => normalizedItems.value.reduce((sum, item) => sum + (item.product?.price_with_discount ?? 0) * item.quantity, 0)
function removeItem(productId) { router.delete(`/cart/${productId}`, { preserveScroll: true, preserveState: true }) }
function clearNotification(productId) { router.patch(`/cart/${productId}/clear-notification`, {}, { preserveScroll: true, preserveState: true }) }
</script>

<template>
    <Transition name="popup">
        <div v-if="show" class="fixed inset-0 z-50 flex justify-end">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="emit('close')" />
            
            <!-- Panel -->
            <div class="relative w-full max-w-sm bg-gray-50 h-full shadow-2xl flex flex-col">
                <div class="p-5 border-b border-gray-200 flex items-center justify-between bg-white/80 backdrop-blur-sm sticky top-0 z-10">
                    <h2 class="font-black text-xl text-gray-900">Корзина</h2>
                    <button @click="emit('close')" class="text-gray-400 hover:text-gray-900 text-xl transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex-grow overflow-y-auto p-4">
                    <div v-if="normalizedItems.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                        <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.343 1.087-.835l1.823-6.44a1.125 1.125 0 00-.44-1.229l-5.432-4.075a1.125 1.125 0 00-1.518.056L5.64 5.39a1.125 1.125 0 00-.056 1.518l3.65 4.563M7.5 14.25L5.106 5.106M17.25 20.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM7.5 20.25a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                        <div class="font-bold text-lg">Корзина пуста</div>
                        <div class="text-sm text-gray-500 mt-1">Добавьте товары из каталога</div>
                    </div>

                    <div v-else class="flex flex-col gap-3">
                        <!-- Карточка товара теперь белая с тенью -->
                        <div v-for="item in normalizedItems" :key="item.id" class="flex flex-col gap-2 p-2.5 rounded-2xl transition-all hover:shadow-md border border-gray-100 bg-white shadow-sm">
                            
                            <div v-if="item.old_price" class="bg-yellow-50 shadow-sm p-3 rounded-xl text-xs relative mb-1 border border-yellow-100">
                                <strong class="text-yellow-800 block mb-1 text-[13px]">⚠️ Внимание! Цена изменилась</strong>
                                <div class="text-yellow-700">Была: <span class="line-through opacity-70">{{ formatPrice(item.old_price) }} ₽</span></div>
                                <div class="text-yellow-700">Стала: <span class="font-bold text-blue-600">{{ formatPrice(item.product?.price_with_discount) }} ₽</span></div>
                                <div v-if="item.price_change_reason" class="text-yellow-600/90 mt-1.5 leading-tight font-medium">{{ item.price_change_reason }}</div>
                                <button @click="clearNotification(item.product_id)" class="mt-2 w-full text-center text-yellow-800 bg-yellow-200/50 hover:bg-yellow-300/80 px-2 py-1.5 rounded-lg font-bold transition">Понятно</button>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <div class="relative flex-shrink-0">
                                    <span v-if="item.product?.discount > 0" class="absolute -top-1.5 -left-1.5 bg-red-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-md shadow-sm z-10">-{{ item.product.discount }}%</span>
                                    <img :src="item.product?.image ? `/storage/${item.product.image}` : 'https://placehold.co/50?text=?'" class="w-16 h-16 object-contain bg-white rounded-xl p-1.5 shadow-sm border border-gray-100" />
                                </div>

                                <div class="flex-grow min-w-0">
                                    <Link :href="`/product/${item.product_id}`" @click="emit('close')" class="font-bold text-sm truncate text-gray-900 hover:text-blue-600 transition block mb-0.5">{{ item.product?.title }}</Link>
                                    <div class="flex items-center gap-2">
                                        <span class="text-blue-600 text-sm font-black">{{ formatPrice(item.product?.price_with_discount) }} ₽</span>
                                        <span v-if="item.product?.discount > 0" class="text-[11px] text-gray-400 line-through font-medium">{{ formatPrice(item.product?.price) }} ₽</span>
                                    </div>
                                    <div class="text-gray-500 text-[11px] mt-0.5 font-bold">{{ item.quantity }} шт.</div>
                                </div>
                                <button @click="removeItem(item.product_id)" class="text-gray-300 hover:text-red-500 text-lg flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-50 transition" title="Удалить">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="normalizedItems.length > 0" class="p-6 border-t border-gray-200 bg-white/80 backdrop-blur-sm shadow-[0_-4px_15px_-3px_rgba(0,0,0,0.05)] z-10">
                    <div class="flex justify-between items-center mb-5">
                        <span class="text-gray-500 font-bold">Итого:</span>
                        <span class="font-black text-2xl text-gray-900">{{ formatPrice(total()) }} ₽</span>
                    </div>
                    <Link href="/checkout" @click="emit('close')" class="block w-full py-3.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition text-center shadow-lg shadow-blue-600/20 mb-3">Оформить заказ</Link>
                    <Link href="/cart" @click="emit('close')" class="block w-full py-2.5 text-center text-gray-600 hover:text-gray-900 font-bold text-sm transition bg-gray-100 rounded-xl hover:bg-gray-200">Перейти в корзину</Link>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.popup-enter-active, .popup-leave-active { transition: opacity 0.3s ease; }
.popup-enter-active .relative, .popup-leave-active .relative { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.popup-enter-from, .popup-leave-to { opacity: 0; }
.popup-enter-from .relative { transform: translateX(100%); }
.popup-leave-to .relative { transform: translateX(100%); }
</style>