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

const currentCategory = computed(() =>
    props.categories.find(c => c.id === selectedCategoryId.value)
)

const form = useForm({
    name: '',
    category_id: props.currentCategoryId,
})

function changeCategory(id) {
    selectedCategoryId.value = id
    form.category_id = id
    router.get('/admin/characteristics', { category_id: id }, { preserveState: true })
}

function submit() {
    form.post('/admin/characteristics', {
        onSuccess: () => form.name = ''
    })
}

function deleteChar(id) {
    if (confirm('Удалить характеристику?')) {
        router.delete(`/admin/characteristics/${id}`)
    }
}
</script>

<template>
    <div>
        <!-- Выбор категории -->
        <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
            <div class="flex gap-2 flex-wrap">
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="changeCategory(cat.id)"
                    :class="[
                        'px-4 py-2 rounded-xl text-sm font-medium transition',
                        selectedCategoryId === cat.id
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]"
                >
                    {{ cat.name }}
                </button>
            </div>
        </div>

        <!-- Форма добавления -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-4">
            <h2 class="font-bold mb-4">Добавить характеристику для «{{ currentCategory?.name }}»</h2>
            <div class="flex gap-3">
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Грузоподъёмность (т)"
                    class="flex-grow px-4 py-3 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    :class="{ 'ring-2 ring-red-400': form.errors.name }"
                />
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition disabled:opacity-50"
                >
                    Добавить
                </button>
            </div>
        </div>

        <!-- Список -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">ID</th>
                        <th class="text-left px-6 py-3 text-xs font-bold text-gray-400 uppercase">Название</th>
                        <th class="text-right px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-if="!currentCategory?.characteristics?.length">
                        <td colspan="3" class="text-center py-8 text-gray-400">Характеристик нет</td>
                    </tr>
                    <tr v-for="char in currentCategory?.characteristics" :key="char.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-400">#{{ char.id }}</td>
                        <td class="px-6 py-4 font-medium">{{ char.name }}</td>
                        <td class="px-6 py-4 text-right">
                            <button
                                @click="deleteChar(char.id)"
                                class="px-3 py-1.5 border border-red-300 text-red-500 rounded-lg text-xs hover:bg-red-50 transition"
                            >
                                🗑
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>