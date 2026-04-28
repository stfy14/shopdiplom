<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import ProductCard from '@/Components/ProductCard.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    products:   Array,
    categories: Array,
    filters:    Object,
})

const search = ref(props.filters?.q ?? '')

function filterByCategory(code) {
    router.get('/search', {
        cat: code || undefined,
        q: search.value || undefined,
    }, { preserveState: true })
}
</script>

<template>
    <ShopLayout :hide-search="true">
        <!-- Хлебные крошки -->
        <div class="flex items-center gap-2 text-sm text-gray-400 font-medium mb-6">
            <Link href="/" class="hover:text-gray-600 transition">Главная</Link>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-bold">Поиск товаров</span>
        </div>

        <!-- Заголовок + результат -->
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-black text-gray-900">
                <template v-if="filters.q">
                    Результаты поиска: <span class="text-blue-600">«{{ filters.q }}»</span>
                </template>
                <template v-else>Все товары</template>
            </h1>
            <p class="text-gray-400 text-sm mt-1 font-medium">
                Найдено {{ products.length }} {{ products.length === 1 ? 'товар' : products.length < 5 ? 'товара' : 'товаров' }}
            </p>
        </div>

        <!-- Фильтр по категориям -->
        <div class="flex flex-wrap gap-2 mb-6 overflow-x-auto pb-1">
            <button
                @click="filterByCategory('')"
                :class="['px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-sm font-bold transition shadow-sm whitespace-nowrap', !filters.cat ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
            >
                Все товары
            </button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                @click="filterByCategory(cat.code)"
                :class="['px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl text-sm font-bold transition shadow-sm whitespace-nowrap', filters.cat === cat.code ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
            >
                {{ cat.name }}
            </button>
        </div>

        <!-- Сетка товаров -->
        <div v-if="products.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
            <ProductCard v-for="product in products" :key="product.id" :product="product" />
        </div>

        <!-- Пусто -->
        <div v-else class="text-center bg-white rounded-3xl py-20 shadow-sm border border-gray-100">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
            </div>
            <div class="text-gray-700 font-black text-lg mb-1">Ничего не найдено</div>
            <div class="text-gray-400 text-sm mb-5">Попробуйте изменить запрос или выбрать другую категорию</div>
            <button @click="filterByCategory('')" class="px-6 py-2.5 bg-gray-900 text-white font-bold rounded-xl text-sm hover:bg-gray-800 transition">
                Показать все товары
            </button>
        </div>
    </ShopLayout>
</template>