<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({ product: Object })
function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }
</script>

<template>
    <ShopLayout>
        <div class="mb-6">
            <Link href="/" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                В каталог
            </Link>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-0">
                
                <!-- Фото -->
                <div class="md:col-span-6 lg:col-span-5 flex items-center justify-center p-8 md:p-12 bg-gray-50/50 relative">
                    <span v-if="product.discount > 0" class="absolute top-6 left-6 bg-red-500 text-white text-sm font-black px-3 py-1.5 rounded-xl shadow-sm z-10">-{{ product.discount }}%</span>
                    <img :src="product.image ? `/storage/${product.image}` : 'https://placehold.co/400x300?text=Нет+фото'" class="max-h-96 max-w-full object-contain drop-shadow-sm" />
                </div>

                <!-- Инфо -->
                <div class="md:col-span-6 lg:col-span-7 p-8 md:p-10 flex flex-col bg-white">
                    
                    <!-- Верхняя строка: Категория и кнопка ред. -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="text-xs font-bold tracking-widest text-blue-600 uppercase mt-2">{{ product.category?.name }}</div>
                        
                        <!-- БЕЗОПАСНАЯ ПРОВЕРКА РОЛИ АДМИНА ЧЕРЕЗ $page.props -->
                        <Link v-if="$page.props.auth.user?.role === 'admin'" :href="`/admin/products/${product.id}/edit`" class="w-9 h-9 bg-white hover:bg-blue-50 text-gray-400 hover:text-blue-600 rounded-xl flex items-center justify-center transition shadow-sm border border-gray-100 flex-shrink-0" title="Редактировать товар">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </Link>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-black text-gray-900 mb-6 leading-tight">{{ product.title }}</h1>

                    <div class="flex items-end gap-4 mb-8">
                        <div>
                            <div v-if="product.discount > 0" class="text-gray-400 line-through text-sm font-medium mb-1">{{ formatPrice(product.price) }} ₽</div>
                            <div class="text-4xl font-black text-gray-900 tracking-tight">{{ formatPrice(product.price_with_discount ?? product.price) }} ₽</div>
                        </div>
                    </div>

                    <div class="mb-8 flex items-center gap-3">
                        <div v-if="product.quantity > 0" class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 text-green-600 rounded-lg text-sm font-bold border border-green-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            В наличии ({{ product.quantity }} шт.)
                        </div>
                        <div v-else class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg text-sm font-bold">
                            Нет в наличии
                        </div>
                        <div class="text-sm font-medium text-gray-400 border-l pl-3">Артикул: {{ product.id }}</div>
                    </div>

                    <Link v-if="product.quantity > 0" :href="`/cart/${product.id}`" method="post" as="button" class="w-full md:w-auto px-10 py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/30 transition text-center mb-8 flex items-center justify-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Добавить в корзину
                    </Link>

                    <div class="grid grid-cols-2 gap-4 mt-auto pt-6 border-t border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-600">🛡️</div>
                            <div class="text-sm font-medium text-gray-600 leading-tight">Гарантия<br>качества</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-600">🚚</div>
                            <div class="text-sm font-medium text-gray-600 leading-tight">Быстрая<br>доставка</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Характеристики -->
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