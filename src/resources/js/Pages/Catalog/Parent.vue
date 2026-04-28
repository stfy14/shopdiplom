<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    category: Object,
    children: Array,
})
</script>

<template>
    <ShopLayout>
        <!-- Хлебные крошки -->
        <div class="flex items-center gap-2 text-sm text-gray-400 font-medium mb-6">
            <Link href="/" class="hover:text-gray-600 transition">Главная</Link>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <Link href="/catalog" class="hover:text-gray-600 transition">Каталог</Link>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-bold">{{ category.name }}</span>
        </div>

        <!-- Шапка категории -->
        <div class="bg-gradient-to-br from-gray-900 to-slate-800 rounded-3xl p-8 md:p-12 mb-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -right-20 -top-20 w-64 h-64 rounded-full bg-blue-500/10 blur-3xl"></div>
                <div class="absolute -left-10 -bottom-10 w-48 h-48 rounded-full bg-white/5 blur-2xl"></div>
            </div>
            <div class="relative z-10">
                <Link href="/catalog" class="inline-flex items-center gap-2 text-sm text-white/60 hover:text-white/90 transition mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Все категории
                </Link>
                <h1 class="text-2xl md:text-3xl font-black mb-3">{{ category.name }}</h1>
                <p v-if="category.description" class="text-white/70 text-sm md:text-base max-w-2xl leading-relaxed">
                    {{ category.description }}
                </p>
                <div class="mt-5 flex items-center gap-3 flex-wrap">
                    <span class="px-3 py-1.5 bg-white/10 border border-white/10 rounded-lg text-xs font-bold">
                        {{ children.length }} {{ children.length === 1 ? 'подраздел' : children.length < 5 ? 'подраздела' : 'подразделов' }}
                    </span>
                    <span class="px-3 py-1.5 bg-blue-500/30 border border-blue-400/30 rounded-lg text-xs font-bold text-blue-200">
                        {{ children.reduce((s, c) => s + (c.products_count ?? 0), 0) }} товаров
                    </span>
                </div>
            </div>
        </div>

        <!-- Подкатегории -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <Link
                v-for="child in children"
                :key="child.id"
                :href="`/catalog/${child.code}`"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-200 group overflow-hidden"
            >
                <!-- Картинка или заглушка -->
                <div class="h-36 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center relative overflow-hidden">
                    <img
                        v-if="child.image"
                        :src="`/storage/${child.image}`"
                        :alt="child.name"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div v-else class="flex flex-col items-center gap-2 text-gray-300">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                    <!-- Бейдж -->
                    <span class="absolute top-3 right-3 bg-white shadow-sm border border-gray-100 text-gray-600 text-[11px] font-bold px-2 py-1 rounded-lg">
                        {{ child.products_count }} шт.
                    </span>
                </div>

                <div class="p-5">
                    <h2 class="font-black text-gray-900 group-hover:text-blue-600 transition text-base leading-snug mb-2">
                        {{ child.name }}
                    </h2>
                    <p v-if="child.description" class="text-sm text-gray-500 line-clamp-2 leading-relaxed">
                        {{ child.description }}
                    </p>
                    <div class="mt-4 flex items-center gap-1.5 text-xs font-bold text-gray-400 group-hover:text-blue-500 transition">
                        Смотреть товары
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </div>
            </Link>
        </div>
    </ShopLayout>
</template>