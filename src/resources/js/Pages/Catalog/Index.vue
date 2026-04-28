<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    categories: Array,
})

function totalProducts(cat) {
    return cat.children?.reduce((sum, child) => sum + (child.products_count ?? 0), 0) ?? 0
}

const iconMap = {
    'rigging':           { bg: 'bg-slate-50', border: 'border-slate-200', icon: 'bg-slate-100 text-slate-600', badge: 'bg-slate-100 text-slate-700' },
    'lifting-equipment': { bg: 'bg-blue-50',  border: 'border-blue-200',  icon: 'bg-blue-100 text-blue-600',  badge: 'bg-blue-100 text-blue-700' },
    'hydraulic':         { bg: 'bg-zinc-50',  border: 'border-zinc-200',  icon: 'bg-zinc-100 text-zinc-600',  badge: 'bg-zinc-100 text-zinc-700' },
    'warehouse':         { bg: 'bg-stone-50', border: 'border-stone-200', icon: 'bg-stone-100 text-stone-600',badge: 'bg-stone-100 text-stone-700' },
}
</script>

<template>
    <ShopLayout>
        <!-- Хлебные крошки -->
        <div class="flex items-center gap-2 text-sm text-gray-400 font-medium mb-6">
            <Link href="/" class="hover:text-gray-600 transition">Главная</Link>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-bold">Каталог</span>
        </div>

        <!-- Заголовок -->
        <div class="mb-10">
            <h1 class="text-3xl font-black text-gray-900 mb-2">Каталог продукции</h1>
            <p class="text-gray-500">Промышленное грузоподъёмное и такелажное оборудование с сертификатами</p>
        </div>

        <!-- Категории -->
        <div class="space-y-10">
            <div v-for="cat in categories" :key="cat.id">
                <!-- Заголовок группы -->
                <div class="flex items-center gap-4 mb-5">
                    <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center border flex-shrink-0', iconMap[cat.code]?.icon ?? 'bg-gray-100 text-gray-500', iconMap[cat.code]?.border ?? 'border-gray-200']">
                        <!-- Такелаж -->
                        <svg v-if="cat.code === 'rigging'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                        </svg>
                        <!-- Грузоподъём -->
                        <svg v-else-if="cat.code === 'lifting-equipment'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                        </svg>
                        <!-- Гидравлика -->
                        <svg v-else-if="cat.code === 'hydraulic'" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                        </svg>
                        <!-- Склад -->
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                        </svg>
                    </div>
                    <div class="flex-grow">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-black text-gray-900">{{ cat.name }}</h2>
                            <span :class="[iconMap[cat.code]?.badge ?? 'bg-gray-100 text-gray-600', 'text-[11px] font-bold px-2.5 py-1 rounded-lg']">
                                {{ totalProducts(cat) }} товаров
                            </span>
                        </div>
                        <p v-if="cat.description" class="text-sm text-gray-500 mt-0.5 line-clamp-1">{{ cat.description }}</p>
                    </div>
                    <Link :href="`/catalog/${cat.code}`" class="hidden sm:flex items-center gap-1.5 text-sm font-bold text-blue-600 hover:text-blue-800 transition flex-shrink-0">
                        Все разделы
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </Link>
                </div>

                <!-- Дочерние -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link
                        v-for="child in cat.children"
                        :key="child.id"
                        :href="`/catalog/${child.code}`"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-blue-100 hover:-translate-y-0.5 transition-all group flex flex-col"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="font-black text-gray-900 group-hover:text-blue-600 transition leading-snug pr-2">{{ child.name }}</h3>
                            <span class="text-[11px] font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded-lg whitespace-nowrap flex-shrink-0">
                                {{ child.products_count }} шт.
                            </span>
                        </div>
                        <p v-if="child.description" class="text-xs text-gray-500 leading-relaxed line-clamp-2 flex-grow">
                            {{ child.description }}
                        </p>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-bold text-gray-400 group-hover:text-blue-500 transition">
                            Посмотреть товары
                            <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>