<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

defineOptions({ layout: AdminLayout });

const props = defineProps({
    categories: Array,
    currentCategoryId: Number,
})

const selectedCategoryId = ref(props.currentCategoryId)
const currentCategory = computed(() => props.categories.find(c => c.id === selectedCategoryId.value))
const form = useForm({ name: '', category_id: props.currentCategoryId })

function changeCategory(id) {
    selectedCategoryId.value = id
    form.category_id = id
    router.get('/admin/characteristics', { category_id: id }, { preserveState: true })
}

function submit() {
    form.post('/admin/characteristics', { onSuccess: () => form.name = '' })
}

function deleteChar(id) {
    if (confirm('Удалить характеристику?')) {
        router.delete(`/admin/characteristics/${id}`)
    }
}
</script>

<template>
    <div>
        <div class="flex items-center gap-4 mb-6">
            <Link href="/admin/products" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </Link>
            <h1 class="text-2xl font-black text-gray-900">Управление характеристиками</h1>
        </div>

        <!-- Выбор категории -->
        <div class="bg-white rounded-2xl shadow-sm p-4 mb-6 border border-gray-100">
            <div class="flex gap-2 flex-wrap">
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="changeCategory(cat.id)"
                    :class="[
                        'px-4 py-2 rounded-xl text-sm font-bold transition',
                        selectedCategoryId === cat.id
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]"
                >
                    {{ cat.name }}
                </button>
            </div>
        </div>

        <!-- Форма добавления -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
            <h2 class="font-black text-lg mb-4">Добавить для «{{ currentCategory?.name }}»</h2>
            <div class="flex gap-3">
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Например: Грузоподъёмность (т)"
                    class="flex-grow px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm"
                    :class="{ 'border-red-400': form.errors.name }"
                />
                <button @click="submit" :disabled="form.processing" class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition shadow-sm disabled:opacity-50">
                    Добавить
                </button>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 px-5 text-xs font-black text-gray-400 uppercase tracking-wider mb-2">
            <div class="col-span-1">ID</div>
            <div class="col-span-9">Название</div>
            <div class="col-span-2 text-right">Действия</div>
        </div>

        <!-- Список -->
        <div class="flex flex-col gap-3">
             <div v-if="!currentCategory?.characteristics?.length" class="text-center py-16 text-gray-400 bg-white rounded-3xl border border-gray-100">
                Характеристик для этой категории нет
            </div>
            <div v-for="char in currentCategory?.characteristics" :key="char.id" class="grid grid-cols-12 gap-4 items-center bg-white rounded-2xl shadow-sm p-4 transition hover:shadow-md border border-gray-100">
                <div class="col-span-1 text-gray-400 font-bold text-sm">#{{ char.id }}</div>
                <div class="col-span-9 font-bold text-gray-900">{{ char.name }}</div>
                <div class="col-span-2 flex justify-end">
                    <button @click="deleteChar(char.id)" class="w-9 h-9 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-xl flex items-center justify-center transition shadow-sm border border-gray-100" title="Удалить">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>