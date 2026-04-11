<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ShopLayout from '@/Layouts/ShopLayout.vue';

// Указываем, что этот Layout сам является дочерним для ShopLayout
defineOptions({ layout: (h, page) => h(ShopLayout, () => page) });

const page = usePage();
const notifications = computed(() => page.props.adminNotifications);
</script>

<template>
    <!-- Это шапка админки, она заменит стандартную шапку ShopLayout -->
    <nav class="bg-white shadow-sm mb-6">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <span class="font-bold text-xl">🛠️ Панель управления</span>
            <div class="flex items-center gap-3">
                <Link href="/admin/orders" class="relative px-4 py-2 border rounded-xl text-sm font-medium hover:bg-gray-50">
                    📦 Заказы
                    <span
                        v-if="notifications?.newOrders > 0 || notifications?.newMessages > 0"
                        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"
                    >
                        {{ (notifications?.newOrders || 0) + (notifications?.newMessages || 0) }}
                    </span>
                </Link>
                <Link href="/admin/categories" class="px-4 py-2 border rounded-xl text-sm font-medium hover:bg-gray-50">
                    📂 Категории
                </Link>
                <Link href="/admin/characteristics" class="px-4 py-2 border rounded-xl text-sm font-medium hover:bg-gray-50">
                    ⚙️ Характеристики
                </Link>
                <Link href="/admin/products/create" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
                    + Добавить товар
                </Link>
                <Link href="/" class="px-4 py-2 border rounded-xl text-sm font-medium hover:bg-gray-50" target="_blank">
                    На сайт ↗
                </Link>
            </div>
        </div>
    </nav>

    <!-- Сюда будет подставлено содержимое конкретной админской страницы -->
    <div class="max-w-7xl mx-auto px-6">
        <slot />
    </div>
</template>