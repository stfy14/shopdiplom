<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3'
import { onMounted, onUnmounted } from 'vue'

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
</script>

<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
            <div class="flex gap-2 p-1 bg-white border border-gray-100 rounded-xl shadow-sm inline-flex">
                <Link href="/admin/products" :class="['flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition', tab === 'active' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']">
                    Активные
                </Link>
                <Link href="/admin/products?tab=deleted" :class="['flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition', tab === 'deleted' ? 'bg-red-500 text-white' : 'text-gray-500 hover:text-red-500 hover:bg-red-50']">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Архив
                </Link>
            </div>
            
            <div class="flex gap-2">
                <Link href="/admin/categories" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <span class="hidden sm:inline">Категории</span>
                </Link>
                <Link href="/admin/characteristics" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                    <span class="hidden sm:inline">Характеристики</span>
                </Link>
                <Link href="/admin/products/create" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Добавить
                </Link>
            </div>
        </div>

        <!-- Наличие и Цена поменяны местами -->
        <div class="grid grid-cols-12 gap-4 px-5 text-xs font-black text-gray-400 uppercase tracking-wider mb-2">
            <div class="col-span-1">ID</div>
            <div class="col-span-5">Товар</div>
            <div class="col-span-2">Наличие</div>
            <div class="col-span-2">Цена</div>
            <div class="col-span-2 text-right">Действия</div>
        </div>

        <div class="flex flex-col gap-3">
            <div v-if="products.length === 0" class="text-center py-16 text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
                Список пуст
            </div>
            
            <div v-for="product in products" :key="product.id" class="grid grid-cols-12 gap-4 items-center bg-white rounded-2xl shadow-sm p-4 transition hover:shadow-md hover:-translate-y-px will-change-transform border border-gray-100 group">
                <div class="col-span-1 text-gray-400 font-bold text-sm">#{{ product.id }}</div>
                
                <div class="col-span-5 flex items-center gap-4">
                    <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center border border-gray-100 p-1 flex-shrink-0">
                        <img :src="product.image ? `/storage/${product.image}` : 'https://placehold.co/50?text=?'" class="max-w-full max-h-full object-contain" />
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-gray-900 truncate">{{ product.title }}</div>
                        <div class="text-xs text-blue-600 font-bold uppercase mt-0.5">{{ product.category?.name }}</div>
                    </div>
                </div>

                <!-- Наличие теперь перед Ценой -->
                <div class="col-span-2">
                    <span :class="['px-2.5 py-1 rounded-lg text-xs font-bold border', product.quantity === 0 ? 'bg-red-50 border-red-100 text-red-600' : product.quantity < 5 ? 'bg-yellow-50 border-yellow-100 text-yellow-700' : 'bg-green-50 border-green-100 text-green-700']">
                        {{ product.quantity }} шт.
                    </span>
                </div>

                <!-- Цена теперь после Наличия -->
                <div class="col-span-2">
                    <div class="font-black text-gray-900">{{ formatPrice(product.price_with_discount ?? product.price) }} ₽</div>
                    <div v-if="product.discount > 0" class="text-[11px] font-bold text-red-500">-{{ product.discount }}% скидка</div>
                </div>

                <div class="col-span-2 flex justify-end gap-2">
                    <template v-if="tab === 'active'">
                        <Link :href="`/admin/products/${product.id}/edit`" class="w-9 h-9 bg-gray-50 hover:bg-blue-50 text-gray-400 hover:text-blue-600 rounded-xl flex items-center justify-center transition shadow-sm border border-gray-100" title="Изменить">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </Link>
                        <button @click="deleteProduct(product.id)" class="w-9 h-9 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-xl flex items-center justify-center transition shadow-sm border border-gray-100" title="Удалить">
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
    </div>
</template>