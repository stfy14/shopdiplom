<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const notifications = page.props.adminNotifications

const props = defineProps({
    products: Array,
    tab: String,
})

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price)
}

function deleteProduct(id) {
    if (confirm('Переместить в архив?')) {
        router.delete(`/admin/products/${id}`)
    }
}

function restoreProduct(id) {
    router.patch(`/admin/products/${id}/restore`)
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow-sm mb-6">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <span class="font-bold text-xl">🛠️ Панель управления</span>
                <div class="flex gap-3">
                    <Link href="/admin/orders" class="relative px-4 py-2 bg-gray-900 text-white rounded-xl text-sm font-medium">
                        📦 Заказы
                        <span
                            v-if="notifications?.newOrders > 0"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
                        >
                            {{ notifications.newOrders }}
                        </span>
                    </Link>
                    <Link href="/admin/categories" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50">
                        📂 Категории
                    </Link>
                    <Link href="/admin/characteristics" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50">
                        ⚙️ Характеристики
                    </Link>
                    <Link href="/admin/products/create" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
                        + Добавить товар
                    </Link>
                    <Link href="/" class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50">
                        На сайт
                    </Link>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-6">
            <!-- Табы -->
            <div class="flex gap-2 mb-4">
                <Link
                    href="/admin"
                    :class="[
                        'px-4 py-2 rounded-xl text-sm font-medium transition',
                        tab === 'active' ? 'bg-blue-600 text-white' : 'bg-white border hover:bg-gray-50'
                    ]"
                >
                    Активные
                </Link>
                <Link
                    href="/admin?tab=deleted"
                    :class="[
                        'px-4 py-2 rounded-xl text-sm font-medium transition',
                        tab === 'deleted' ? 'bg-red-500 text-white' : 'bg-white border border-red-200 text-red-500 hover:bg-red-50'
                    ]"
                >
                    🗑 Архив
                </Link>
            </div>

            <!-- Таблица -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">ID</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Фото</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Название</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Цена</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Остаток</th>
                            <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Категория</th>
                            <th class="text-right px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-if="products.length === 0">
                            <td colspan="7" class="text-center py-12 text-gray-400">Список пуст</td>
                        </tr>
                        <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-400 text-sm">#{{ product.id }}</td>
                            <td class="px-6 py-4">
                                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center overflow-hidden">
                                    <img
                                        :src="product.image ? `/storage/${product.image}` : 'https://placehold.co/50?text=?'"
                                        class="max-w-full max-h-full object-contain"
                                    />
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium">{{ product.title }}</td>
                            <td class="px-6 py-4 font-bold">{{ formatPrice(product.price) }} ₽</td>
                            <td class="px-6 py-4">
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs font-bold',
                                    product.quantity === 0 ? 'bg-red-100 text-red-600' :
                                    product.quantity < 5 ? 'bg-yellow-100 text-yellow-600' :
                                    'bg-green-100 text-green-600'
                                ]">
                                    {{ product.quantity }} шт.
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">
                                    {{ product.category?.name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link
                                        v-if="tab === 'active'"
                                        :href="`/admin/products/${product.id}/edit`"
                                        class="px-3 py-1.5 border border-blue-300 text-blue-600 rounded-lg text-xs hover:bg-blue-50 transition"
                                    >
                                        ✏️ Изменить
                                    </Link>
                                    <button
                                        v-if="tab === 'active'"
                                        @click="deleteProduct(product.id)"
                                        class="px-3 py-1.5 border border-red-300 text-red-500 rounded-lg text-xs hover:bg-red-50 transition"
                                    >
                                        🗑
                                    </button>
                                    <button
                                        v-else
                                        @click="restoreProduct(product.id)"
                                        class="px-3 py-1.5 border border-green-300 text-green-600 rounded-lg text-xs hover:bg-green-50 transition"
                                    >
                                        ↩️ Восстановить
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>