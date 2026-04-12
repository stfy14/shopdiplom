<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

defineOptions({ layout: AdminLayout });

const props = defineProps({
    products: Array,
    tab: String,
})

const handleProductUpdated = () => {
    router.reload({ only: ['products'], preserveScroll: true, preserveState: true })
}

onMounted(() => {
    window.Echo.private('admin-notifications')
        .listen('.ProductUpdated', handleProductUpdated)
})

onUnmounted(() => {
    window.Echo.private('admin-notifications')
        .stopListening('.ProductUpdated', handleProductUpdated)
})

function formatPrice(price) { return new Intl.NumberFormat('ru-RU').format(price) }
function deleteProduct(id) {
    if (confirm('Переместить в архив?')) { router.delete(`/admin/products/${id}`) }
}
function restoreProduct(id) {
    router.patch(`/admin/products/${id}/restore`)
}

// Сортировка и пагинация
const currentPage = ref(1)
watch(() => props.tab, () => { currentPage.value = 1 }) // Сброс страницы при смене вкладки

const sortedProducts = computed(() => {
    return [...props.products].sort((a, b) => a.id - b.id)
})

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * 10
    return sortedProducts.value.slice(start, start + 10)
})

const totalPages = computed(() => Math.ceil(sortedProducts.value.length / 10))
</script>

<template>
    <div>
        <div class="flex flex-col lg:flex-row lg:flex-wrap gap-3 lg:gap-4 mb-6 lg:items-center">
            <div class="order-2 lg:order-1 flex w-full lg:w-auto p-1 bg-white border border-gray-100 rounded-xl shadow-sm flex-shrink-0">
                <Link href="/admin/products" :class="['flex-1 lg:flex-none flex justify-center items-center gap-2 px-5 py-2.5 lg:py-2 rounded-lg text-sm font-bold transition', tab === 'active' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']">
                    Активные
                </Link>
                <Link href="/admin/products?tab=deleted" :class="['flex-1 lg:flex-none flex justify-center items-center gap-2 px-5 py-2.5 lg:py-2 rounded-lg text-sm font-bold transition', tab === 'deleted' ? 'bg-red-500 text-white' : 'text-gray-500 hover:text-red-500 hover:bg-red-50']">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Архив
                </Link>
            </div>

            <div class="order-1 lg:order-2 flex gap-2 w-full lg:w-auto flex-shrink-0 lg:ml-auto">
                <Link href="/admin/categories" class="flex-1 lg:flex-none flex justify-center items-center gap-2 px-4 py-2.5 lg:py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <span>Категории</span>
                </Link>
                <Link href="/admin/characteristics" class="flex-1 lg:flex-none flex justify-center items-center gap-2 px-4 py-2.5 lg:py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                    <span class="hidden sm:inline">Характеристики</span>
                    <span class="sm:hidden">Хар-ки</span>
                </Link>
            </div>

            <div class="order-3 lg:order-3 w-full lg:w-auto flex-shrink-0 mt-1 lg:mt-0">
                <Link href="/admin/products/create" class="w-full lg:w-auto px-4 py-3 lg:py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition shadow-sm flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Добавить
                </Link>
            </div>
        </div>

        <div class="hidden md:grid grid-cols-12 gap-4 px-5 text-xs font-black text-gray-400 uppercase tracking-wider mb-2">
            <div class="col-span-2">ID</div>
            <div class="col-span-4">Товар</div>
            <div class="col-span-2 text-center">Наличие</div>
            <div class="col-span-2">Цена</div>
            <div class="col-span-2 text-right">Действия</div>
        </div>

        <div class="flex flex-col gap-3">
            <div v-if="products.length === 0" class="text-center py-16 text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
                Список пуст
            </div>
            
            <div v-for="product in paginatedProducts" :key="product.id" 
                class="relative bg-white rounded-2xl shadow-sm border border-gray-100 group transition duration-300 ease-out hover:shadow-md hover:-translate-y-1 will-change-transform antialiased p-4 flex flex-col gap-3 md:grid md:grid-cols-12 md:gap-4 md:items-center md:flex-none">
                
                <div class="md:col-span-2 flex items-start justify-between md:block">
                    <div>
                        <div class="font-black text-gray-900">#{{ product.id }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ product.category?.name }}</div>
                    </div>
                    <div class="md:hidden text-right">
                        <div class="font-black text-gray-900 text-sm">{{ formatPrice(product.price_with_discount ?? product.price) }} ₽</div>
                        <div v-if="product.discount > 0" class="text-[10px] font-bold text-red-500 mt-0.5">-{{ product.discount }}% скидка</div>
                    </div>
                </div>

                <div class="md:col-span-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-gray-50 rounded-xl flex items-center justify-center border border-gray-100 p-1 flex-shrink-0">
                            <img :src="product.image ? `/storage/${product.image}` : 'https://placehold.co/50?text=?'" class="max-w-full max-h-full object-contain" />
                        </div>
                        <div class="font-bold text-gray-900 leading-tight line-clamp-2">{{ product.title }}</div>
                    </div>
                </div>

                <div class="md:col-span-6 flex items-center justify-between md:contents mt-1 md:mt-0">
                    <div class="md:col-span-2 md:flex md:justify-center">
                        <span :class="['inline-flex px-3 py-1 rounded-lg text-xs font-bold whitespace-nowrap', product.quantity === 0 ? 'bg-red-50 text-red-600' : product.quantity < 5 ? 'bg-yellow-50 text-yellow-700' : 'bg-green-50 text-green-700']">
                            {{ product.quantity }} шт.
                        </span>
                    </div>

                    <div class="hidden md:block md:col-span-2 text-right md:text-left">
                        <div class="font-black text-gray-900 whitespace-nowrap">{{ formatPrice(product.price_with_discount ?? product.price) }} ₽</div>
                        <div v-if="product.discount > 0" class="text-[10px] font-bold text-red-500 mt-0.5">-{{ product.discount }}% скидка</div>
                    </div>

                    <div class="md:col-span-2 flex justify-end gap-2">
                        <template v-if="tab === 'active'">
                            <Link :href="`/admin/products/${product.id}/edit`" class="w-8 h-8 md:w-9 md:h-9 bg-gray-50 hover:bg-blue-50 text-gray-400 hover:text-blue-600 rounded-xl flex items-center justify-center transition shadow-sm border border-gray-100" title="Изменить">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </Link>
                            <button @click="deleteProduct(product.id)" class="w-8 h-8 md:w-9 md:h-9 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-xl flex items-center justify-center transition shadow-sm border border-gray-100" title="Удалить">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </template>
                        <button v-else @click="restoreProduct(product.id)" class="px-4 py-2 bg-green-50 hover:bg-green-100 text-green-700 rounded-xl text-xs font-bold transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H7a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Восстановить
                        </button>
                    </div>
                </div>
            </div>

            <!-- Пагинация -->
            <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 mt-4 mb-2">
                <button @click="currentPage--" :disabled="currentPage === 1" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-900 disabled:opacity-50 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <div class="text-sm font-bold text-gray-700 px-4">
                    Страница {{ currentPage }} из {{ totalPages }}
                </div>
                <button @click="currentPage++" :disabled="currentPage === totalPages" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-900 disabled:opacity-50 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>
</template>