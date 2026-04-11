<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

defineOptions({ layout: AdminLayout });

const props = defineProps({
    product: Object,
    categories: Array,
})

const isEdit = !!props.product

const form = useForm({
    title:           props.product?.title ?? '',
    description:     props.product?.description ?? '',
    price:           props.product?.price ?? '',
    quantity:        props.product?.quantity ?? 0,
    discount:        props.product?.discount ?? 0,
    category_id:     props.product?.category_id ?? '',
    image:           null,
    characteristics: {},
})

const selectedCategory = ref(
    props.categories.find(c => c.id === props.product?.category_id) ?? null
)

if (props.product?.characteristics) {
    props.product.characteristics.forEach(c => {
        form.characteristics[c.characteristic_id] = c.value
    })
}

watch(() => form.category_id, (newId) => {
    selectedCategory.value = props.categories.find(c => c.id == newId) ?? null
    form.characteristics = {}
})

function submit() {
    if (isEdit) {
        // При наличии файлов PUT не работает напрямую — нужно POST + _method в теле формы
        form.transform(data => ({ ...data, _method: 'put' }))
            .post(`/admin/products/${props.product.id}`)
    } else {
        form.post('/admin/products')
    }
}

const imagePreview = ref(props.product?.image ? `/storage/${props.product.image}` : null)

function onImageChange(e) {
    const file = e.target.files[0]
    if (!file) return
    form.image = file
    imagePreview.value = URL.createObjectURL(file)
}
</script>

<template>
    <div>
        <div class="bg-white rounded-2xl shadow-sm p-8">
            <h1 class="font-bold text-xl mb-6">{{ isEdit ? 'Редактирование товара' : 'Новый товар' }}</h1>
            
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-500 mb-1">Название товара</label>
                <input
                    v-model="form.title"
                    type="text"
                    class="w-full px-4 py-3 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    :class="{ 'ring-2 ring-red-400': form.errors.title }"
                />
                <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Цена (₽)</label>
                    <input v-model="form.price" type="number" class="w-full px-4 py-3 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Остаток (шт)</label>
                    <input v-model="form.quantity" type="number" class="w-full px-4 py-3 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Скидка (%)</label>
                    <input v-model="form.discount" type="number" min="0" max="100" class="w-full px-4 py-3 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-500 mb-1">Категория</label>
                <select v-model="form.category_id" class="w-full px-4 py-3 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="" disabled>— Выберите категорию —</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-500 mb-1">Описание</label>
                <textarea v-model="form.description" rows="4" class="w-full px-4 py-3 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none" />
            </div>

            <div v-if="selectedCategory?.characteristics?.length > 0" class="mb-5">
                <div class="text-sm font-medium text-gray-500 mb-3 uppercase">Характеристики</div>
                <div class="grid grid-cols-2 gap-3">
                    <div v-for="char in selectedCategory.characteristics" :key="char.id">
                        <label class="block text-xs text-gray-400 mb-1">{{ char.name }}</label>
                        <input v-model="form.characteristics[char.id]" type="text" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm" />
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-500 mb-2">Изображение</label>
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                    <div class="w-20 h-20 bg-white rounded-xl border flex items-center justify-center overflow-hidden flex-shrink-0">
                        <img v-if="imagePreview" :src="imagePreview" class="max-w-full max-h-full object-contain" />
                        <span v-else class="text-3xl">🖼️</span>
                    </div>
                    <input type="file" accept="image/*" @change="onImageChange" class="text-sm" />
                </div>
            </div>

            <button
                @click="submit"
                :disabled="form.processing"
                class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition disabled:opacity-50"
            >
                {{ form.processing ? 'Сохраняем...' : (isEdit ? 'Сохранить изменения' : 'Создать товар') }}
            </button>
        </div>
    </div>
</template>