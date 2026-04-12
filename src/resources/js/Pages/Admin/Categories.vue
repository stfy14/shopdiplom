<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout });
const props = defineProps({ categories: Array })

const form = useForm({ name: '', code: '' })

function submit() {
    form.post('/admin/categories', { onSuccess: () => form.reset() })
}

function deleteCategory(id) {
    if (confirm('Удалить категорию? Все товары и характеристики будут удалены.')) {
        router.delete(`/admin/categories/${id}`)
    }
}
</script>

<template>
    <div>
        <div class="flex items-center gap-4 mb-6">
            <Link href="/admin/products" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </Link>
            <h1 class="text-2xl font-black text-gray-900">Управление категориями</h1>
        </div>

        <!-- Форма добавления -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
            <h2 class="font-black text-lg mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Добавить категорию
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Название</label>
                    <input v-model="form.name" type="text" placeholder="Лебёдки" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm" :class="{ 'border-red-400': form.errors.name }"/>
                    <div v-if="form.errors.name" class="text-red-500 text-xs font-bold mt-1 ml-1">{{ form.errors.name }}</div>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Код (латиница)</label>
                    <input v-model="form.code" type="text" placeholder="winches" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm" :class="{ 'border-red-400': form.errors.code }"/>
                    <div v-if="form.errors.code" class="text-red-500 text-xs font-bold mt-1 ml-1">{{ form.errors.code }}</div>
                </div>
            </div>
            <button @click="submit" :disabled="form.processing" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-sm disabled:opacity-50">
                Добавить категорию
            </button>
        </div>

        <div class="hidden md:grid grid-cols-12 gap-4 px-5 text-xs font-black text-gray-400 uppercase tracking-wider mb-2">
            <div class="col-span-1">ID</div>
            <div class="col-span-4">Название</div>
            <div class="col-span-3">Код</div>
            <div class="col-span-2">Характеристики</div>
            <div class="col-span-2 text-right">Действия</div>
        </div>

        <!-- Список -->
        <div class="flex flex-col gap-3">
            <div v-if="categories.length === 0" class="text-center py-16 text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
                Список категорий пуст
            </div>

            <div v-for="cat in categories" :key="cat.id" class="relative bg-white rounded-2xl shadow-sm border border-gray-100 group transition duration-300 ease-out hover:shadow-md hover:-translate-y-px will-change-transform antialiased p-4 flex flex-col gap-3 md:grid md:grid-cols-12 md:gap-4 md:items-center md:flex-none">
                
                <!-- Строка 1 на мобилках: ID и Кол-во характеристик -->
                <div class="md:col-span-1 flex justify-between items-start md:block">
                    <div class="font-black text-gray-900 text-sm">#{{ cat.id }}</div>
                    
                    <!-- Бейдж характеристик показываем справа вверху на мобильном -->
                    <div class="md:hidden">
                        <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-lg text-[11px] font-bold">
                            {{ cat.characteristics_count }} хар-к
                        </span>
                    </div>
                </div>
                
                <!-- Название -->
                <div class="md:col-span-4 flex items-center gap-3">
                    <div class="w-9 h-9 md:w-10 md:h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                    <div class="font-bold text-gray-900 truncate">{{ cat.name }}</div>
                </div>

                <!-- Строка 2 на мобилках: Код и Действия -->
                <div class="md:col-span-7 flex items-center justify-between md:contents mt-1 md:mt-0">
                    
                    <!-- Код (с иконкой скобок вместо надписи "КОД:") -->
                    <div class="md:col-span-3 flex items-center gap-2">
                        <span class="md:hidden text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        </span>
                        <span class="font-mono text-gray-500 text-sm font-medium">{{ cat.code }}</span>
                    </div>
                    
                    <!-- Кол-во характеристик (Только на десктопе, на мобильном оно вверху) -->
                    <div class="hidden md:block md:col-span-2">
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold">
                            {{ cat.characteristics_count }} шт.
                        </span>
                    </div>

                    <!-- Действия -->
                    <div class="md:col-span-2 flex justify-end">
                        <button @click="deleteCategory(cat.id)" class="w-8 h-8 md:w-9 md:h-9 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-xl flex items-center justify-center transition shadow-sm border border-gray-100" title="Удалить">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>