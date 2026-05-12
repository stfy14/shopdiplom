<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    category:    Object,
    children:    Array,
    breadcrumbs: Array, // Приходит с сервера для любой глубины
})
</script>

<template>
    <ShopLayout>
        <!-- Хлебные крошки (любая глубина) -->
        <div class="flex items-center gap-1.5 text-sm text-gray-400 font-medium mb-6 flex-wrap">
            <Link href="/" class="hover:text-gray-600 transition">Главная</Link>
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <template v-if="breadcrumbs?.length">
                <template v-for="(crumb, i) in breadcrumbs" :key="i">
                    <Link v-if="crumb.href" :href="crumb.href" class="hover:text-gray-600 transition">{{ crumb.title }}</Link>
                    <span v-else class="text-gray-900 font-bold">{{ crumb.title }}</span>
                    <svg v-if="i < breadcrumbs.length - 1" class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </template>
            </template>
            <template v-else>
                <Link href="/catalog" class="hover:text-gray-600 transition">Каталог</Link>
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-900 font-bold">{{ category.name }}</span>
            </template>
        </div>

        <!-- Шапка категории -->
        <div class="bg-gradient-to-br from-gray-900 to-slate-800 rounded-3xl p-8 md:p-12 mb-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -right-20 -top-20 w-64 h-64 rounded-full bg-blue-500/10 blur-3xl"></div>
                <div class="absolute -left-10 -bottom-10 w-48 h-48 rounded-full bg-white/5 blur-2xl"></div>
            </div>

            <!-- Фоновая картинка категории (если есть) -->
            <div v-if="category.image" class="absolute inset-0 overflow-hidden rounded-3xl">
                <img :src="`/storage/${category.image}`" :alt="category.name" class="w-full h-full object-cover opacity-20" />
            </div>

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-4 flex-wrap">
                    <Link v-if="category.parent" :href="`/catalog/${category.parent.code}`" class="inline-flex items-center gap-2 text-sm text-white/60 hover:text-white/90 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        {{ category.parent.name }}
                    </Link>
                    <Link v-else href="/catalog" class="inline-flex items-center gap-2 text-sm text-white/60 hover:text-white/90 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Все категории
                    </Link>
                </div>

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
                    <!-- ГОСТ / DIN бейджи -->
                    <span v-if="category.gost" class="px-3 py-1.5 bg-amber-400/20 border border-amber-400/30 rounded-lg text-xs font-bold text-amber-200">
                        ГОСТ {{ category.gost }}
                    </span>
                    <span v-if="category.din" class="px-3 py-1.5 bg-sky-400/20 border border-sky-400/30 rounded-lg text-xs font-bold text-sky-200">
                        DIN {{ category.din }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Дочерние категории -->
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
                    <!-- Бейджи поверх картинки -->
                    <div class="absolute top-3 right-3 flex flex-col items-end gap-1">
                        <span class="bg-white shadow-sm border border-gray-100 text-gray-600 text-[11px] font-bold px-2 py-1 rounded-lg">
                            {{ child.products_count }} шт.
                        </span>
                    </div>
                </div>

                <div class="p-5">
                    <h2 class="font-black text-gray-900 group-hover:text-blue-600 transition text-base leading-snug mb-2">
                        {{ child.name }}
                    </h2>

                    <!-- ГОСТ / DIN -->
                    <div v-if="child.gost || child.din" class="flex gap-1.5 mb-2 flex-wrap">
                        <span v-if="child.gost" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">ГОСТ {{ child.gost }}</span>
                        <span v-if="child.din" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200">DIN {{ child.din }}</span>
                    </div>

                    <p v-if="child.description" class="text-sm text-gray-500 line-clamp-2 leading-relaxed">
                        {{ child.description }}
                    </p>

                    <!-- Если у дочерней тоже есть дети — показываем их -->
                    <div v-if="child.children?.length" class="mt-3 flex flex-wrap gap-1">
                        <Link
                            v-for="grandchild in child.children.slice(0, 4)"
                            :key="grandchild.id"
                            :href="`/catalog/${grandchild.code}`"
                            class="text-[11px] font-bold px-2 py-0.5 rounded-lg bg-gray-50 text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition"
                            @click.stop
                        >
                            {{ grandchild.name }}
                        </Link>
                        <span v-if="child.children.length > 4" class="text-[11px] font-bold px-2 py-0.5 text-gray-400">
                            +{{ child.children.length - 4 }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center gap-1.5 text-xs font-bold text-gray-400 group-hover:text-blue-500 transition">
                        {{ child.children?.length ? 'Смотреть подразделы' : 'Смотреть товары' }}
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </div>
            </Link>
        </div>
    </ShopLayout>
</template>