<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ShopLayout from '@/Layouts/ShopLayout.vue';

const page = usePage();
const notifications = computed(() => page.props.adminNotifications);
</script>

<template>
    <ShopLayout>
        <!-- Шапка панели управления -->
        <nav class="bg-white shadow-sm mb-8 rounded-2xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <div class="flex items-center gap-2 mr-2">
                        <div class="w-8 h-8 bg-gray-900 text-white rounded-lg flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span class="font-black text-lg text-gray-900 tracking-tight">Управление</span>
                    </div>

                    <Link href="/admin" class="px-4 py-2 border rounded-xl text-sm font-bold transition flex items-center gap-2" :class="[$page.url === '/admin' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 hover:bg-gray-50']">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Главная
                    </Link>

                    <Link href="/admin/products" class="px-4 py-2 border rounded-xl text-sm font-bold transition flex items-center gap-2" :class="[$page.url.startsWith('/admin/products') || $page.url.startsWith('/admin/categories') || $page.url.startsWith('/admin/characteristics') ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 hover:bg-gray-50']">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Товары
                    </Link>

                    <Link href="/admin/orders" class="relative px-4 py-2 border rounded-xl text-sm font-bold transition flex items-center gap-2" :class="[$page.url.startsWith('/admin/orders') ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 hover:bg-gray-50']">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Заказы
                        <span v-if="notifications?.newOrders > 0 || notifications?.newMessages > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] rounded-full w-5 h-5 flex items-center justify-center font-black shadow-sm">
                            {{ (notifications?.newOrders || 0) + (notifications?.newMessages || 0) }}
                        </span>
                    </Link>
                </div>

                <Link href="/" class="px-5 py-2 bg-blue-50 text-blue-600 rounded-xl text-sm font-bold hover:bg-blue-100 transition flex items-center justify-center gap-2" target="_blank">
                    На сайт
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </Link>
            </div>
        </nav>

        <slot />
    </ShopLayout>
</template>