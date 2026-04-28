<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    category:    Object,
    products:    Array,
    breadcrumbs: Array,
    filters:     Object,
})

const search = ref(props.filters?.q ?? '')
const sort   = ref(props.filters?.sort ?? 'default')

function applyFilters() {
    router.get(`/catalog/${props.category.code}`, {
        q:    search.value || undefined,
        sort: sort.value !== 'default' ? sort.value : undefined,
    }, { preserveState: true })
}

const sortOptions = [
    { value: 'default',    label: 'По умолчанию' },
    { value: 'price_asc',  label: 'Сначала дешевле' },
    { value: 'price_desc', label: 'Сначала дороже' },
    { value: 'new',        label: 'Новинки' },
]
</script>

<template>
    <ShopLayout>
        <!-- Хлебные крошки -->
        <div class="flex items-center gap-1.5 text-sm text-gray-400 font-medium mb-6 flex-wrap">
            <template v-for="(crumb, i) in breadcrumbs" :key="i">
                <Link v-if="crumb.href" :href="crumb.href" class="hover:text-gray-600 transition">{{ crumb.title }}</Link>
                <span v-else class="text-gray-900 font-bold">{{ crumb.title }}</span>
                <svg v-if="i < breadcrumbs.length - 1" class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </template>
        </div>

        <!-- Шапка -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-gray-900">{{ category.name }}</h1>
                <p v-if="category.description" class="text-gray-500 text-sm mt-1 max-w-2xl">{{ category.description }}</p>
                <p class="text-gray-400 text-sm mt-1 font-medium">{{ products.length }} {{ products.length === 1 ? 'товар' : products.length < 5 ? 'товара' : 'товаров' }}</p>
            </div>
            <Link
                v-if="category.parent"
                :href="`/catalog/${category.parent.code}`"
                class="flex-shrink-0 flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-gray-900 transition bg-white border border-gray-200 rounded-xl px-4 py-2.5 shadow-sm self-start"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ category.parent.name }}
            </Link>
        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-grow">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input
                    v-model="search"
                    type="search"
                    placeholder="Поиск в разделе..."
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-medium transition"
                    @keydown.enter="applyFilters"
                />
            </div>
            <select
                v-model="sort"
                @change="applyFilters"
                class="px-4 py-2.5 bg-gray-50 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold text-gray-700 transition"
            >
                <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
            <button @click="applyFilters" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition flex-shrink-0">
                Применить
            </button>
        </div>

        <!-- Товары -->
        <div v-if="products.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            <ProductCard v-for="product in products" :key="product.id" :product="product" />
        </div>

        <!-- Пусто -->
        <div v-else class="text-center bg-white rounded-3xl py-20 shadow-sm border border-gray-100">
            <div class="text-5xl mb-4">🔍</div>
            <div class="text-gray-500 font-bold text-lg">По вашему запросу ничего не найдено</div>
            <button @click="search = ''; applyFilters()" class="mt-4 text-blue-600 font-bold hover:underline text-sm">Сбросить фильтры</button>
        </div>
    </ShopLayout>
</template>