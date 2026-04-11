<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout });

const props = defineProps({
    categories: Array,
})

const form = useForm({
    name: '',
    code: '',
})

function submit() {
    form.post('/admin/categories', {
        onSuccess: () => form.reset()
    })
}

function deleteCategory(id) {
    if (confirm('Удалить категорию? Все товары и характеристики будут удалены.')) {
        router.delete(`/admin/categories/${id}`)
    }
}
</script>

<template>
    <div>
        <!-- Форма добавления -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
            <h2 class="font-bold mb-4">Добавить категорию</h2>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm text-gray-500 mb-1">Название</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Лебёдки"
                        class="w-full px-4 py-3 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'ring-2 ring-red-400': form.errors.name }"
                    />
                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                </div>
                <div>
                    <label class="block text-sm text-gray-500 mb-1">Код (латиница)</label>
                    <input
                        v-model="form.code"
                        type="text"
                        placeholder="winches"
                        class="w-full px-4 py-3 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'ring-2 ring-red-400': form.errors.code }"
                    />
                    <div v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</div>
                </div>
            </div>
            <button
                @click="submit"
                :disabled="form.processing"
                class="px-6 py-2.5 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition disabled:opacity-50"
            >
                Добавить
            </button>
        </div>

        <!-- Список -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">ID</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Название</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Код</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Характеристик</th>
                        <th class="text-right px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="cat in categories" :key="cat.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-400">#{{ cat.id }}</td>
                        <td class="px-6 py-4 font-medium">{{ cat.name }}</td>
                        <td class="px-6 py-4 font-mono text-gray-500 text-sm">{{ cat.code }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">
                                {{ cat.characteristics_count }} шт.
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button
                                @click="deleteCategory(cat.id)"
                                class="px-3 py-1.5 border border-red-300 text-red-500 rounded-lg text-xs hover:bg-red-50 transition"
                            >
                                🗑 Удалить
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>