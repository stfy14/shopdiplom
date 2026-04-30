<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const emit = defineEmits(['close'])

const normalizedItems = computed(() => page.props.cartItems ??[])

function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }
const total = () => normalizedItems.value.reduce((sum, item) => sum + (item.product?.price_with_discount ?? 0) * item.quantity, 0)
function removeItem(productId) { router.delete(`/cart/${productId}`, { preserveScroll: true, preserveState: true }) }
function clearNotification(productId) { router.patch(`/cart/${productId}/clear-notification`, {}, { preserveScroll: true, preserveState: true }) }
</script>

<template>
    <div class="fixed sm:absolute left-4 right-4 top-[76px] sm:inset-auto sm:top-full sm:right-0 sm:mt-4 sm:w-[400px] bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-gray-100 flex flex-col max-h-[calc(100vh-100px)] overflow-hidden cursor-default" @click.stop>
        
        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between bg-white z-10 flex-shrink-0">
            <h2 class="font-black text-[15px] text-gray-900 flex items-center gap-2.5 tracking-tight">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Корзина
            </h2>
            <button @click="emit('close')" class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-xl hover:bg-red-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-grow overflow-y-auto bg-gray-50/30 p-2">
            <div v-if="normalizedItems.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-400">
                <div class="w-16 h-16 bg-white shadow-sm border border-gray-100 rounded-2xl flex items-center justify-center mb-3">
                    <!-- Грустный смайлик -->
                    <svg class="w-9 h-9 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" stroke-linecap="round"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 15.5c.5-1 1.8-1.5 3.5-1.5s3 .5 3.5 1.5"/>
                        <circle cx="9" cy="10" r="0.8" fill="currentColor" stroke="none"/>
                        <circle cx="15" cy="10" r="0.8" fill="currentColor" stroke="none"/>
                        <path stroke-linecap="round" d="M10 8.5c.3-.5.8-.8 1.5-.5M14 8.5c-.3-.5-.8-.8-1.5-.5" stroke-width="1"/>
                    </svg>
                </div>
                <div class="font-bold text-sm text-gray-500">Корзина пуста</div>
                <div class="text-[11px] text-gray-400 mt-1">Добавьте товары из каталога</div>
            </div>

            <div v-else class="flex flex-col gap-2">
                <div v-for="item in normalizedItems" :key="item.id" class="flex flex-col gap-2 p-3 rounded-2xl transition-all bg-white shadow-sm border border-gray-100/70 hover:border-gray-200">
                    
                    <Transition name="price-fade">
                        <div v-if="item.old_price" class="bg-yellow-50 shadow-sm p-3 rounded-xl text-xs relative mb-1 border border-yellow-100">
                            <strong class="text-yellow-800 block mb-1 text-[12px] flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Цена изменилась!
                            </strong>
                            <div class="text-yellow-700">Была: <span class="line-through opacity-70">{{ formatPrice(item.old_price) }} ₽</span></div>
                            <div class="text-yellow-700">Стала: <span class="font-bold text-blue-600">{{ formatPrice(item.product?.price_with_discount) }} ₽</span></div>
                            <button @click="clearNotification(item.product_id)" class="mt-2 w-full text-center text-yellow-800 bg-yellow-200/50 hover:bg-yellow-300/80 px-2 py-1.5 rounded-lg font-bold transition">Понятно</button>
                        </div>
                    </Transition>
                    
                    <div class="flex items-center gap-3">
                        <div class="relative flex-shrink-0">
                            <span v-if="item.product?.discount > 0" class="absolute -top-1.5 -left-1.5 bg-red-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-md shadow-sm z-10">-{{ item.product.discount }}%</span>
                            <img :src="item.product?.image ? `/storage/${item.product.image}` : 'https://placehold.co/50?text=?'" class="w-14 h-14 object-contain bg-white rounded-xl p-1 shadow-sm border border-gray-100" />
                        </div>

                        <div class="flex-grow min-w-0">
                            <Link :href="`/product/${item.product_id}`" @click="emit('close')" class="font-bold text-sm truncate text-gray-900 hover:text-blue-600 transition block mb-0.5">{{ item.product?.title }}</Link>
                            <div class="flex items-center gap-2">
                                <span class="text-blue-600 text-sm font-black">{{ formatPrice(item.product?.price_with_discount) }} ₽</span>
                                <span v-if="item.product?.discount > 0" class="text-[11px] text-gray-400 line-through font-medium">{{ formatPrice(item.product?.price) }} ₽</span>
                            </div>
                            <div class="text-gray-500 text-[11px] mt-0.5 font-bold">{{ item.quantity }} шт.</div>
                        </div>
                        <button @click="removeItem(item.product_id)" class="text-gray-300 hover:text-red-500 w-8 h-8 flex-shrink-0 flex items-center justify-center rounded-xl hover:bg-red-50 transition" title="Удалить">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="normalizedItems.length > 0" class="p-5 border-t border-gray-50 bg-white flex-shrink-0 z-10">
            <div class="flex justify-between items-center mb-4">
                <span class="text-gray-500 font-bold text-sm">Итого:</span>
                <span class="font-black text-xl text-gray-900">{{ formatPrice(total()) }} ₽</span>
            </div>
            <div class="flex gap-2">
                <Link href="/cart" @click="emit('close')" class="flex-1 py-3 text-center text-gray-600 hover:text-gray-900 font-bold text-sm transition bg-gray-100 rounded-xl hover:bg-gray-200">В корзину</Link>
                <Link href="/checkout" @click="emit('close')" class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition text-center shadow-lg shadow-blue-600/20 text-sm">Оформить</Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.price-fade-enter-active,
.price-fade-leave-active { transition: opacity 0.3s ease; }
.price-fade-enter-from,
.price-fade-leave-to { opacity: 0; }
</style>