<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

defineOptions({ layout: AdminLayout });

const props = defineProps({ product: Object, categories: Array })
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

const selectedCategory = ref(props.categories.find(c => c.id === props.product?.category_id) ?? null)

if (props.product?.characteristics) {
    props.product.characteristics.forEach(c => { form.characteristics[c.characteristic_id] = c.value })
}

watch(() => form.category_id, (newId) => {
    selectedCategory.value = props.categories.find(c => c.id == newId) ?? null
    form.characteristics = {}
})

function submit() {
    if (isEdit) {
        form.transform(data => ({ ...data, _method: 'put' })).post(`/admin/products/${props.product.id}`)
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
        <div class="flex items-center gap-4 mb-6">
            <Link href="/admin/products" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </Link>
            <h1 class="text-2xl font-black text-gray-900">{{ isEdit ? 'Редактирование товара' : 'Новый товар' }}</h1>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
            <div class="mb-6">
                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Название товара</label>
                <input v-model="form.title" type="text" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm" :class="{ 'border-red-400': form.errors.title }" />
                <div v-if="form.errors.title" class="text-red-500 text-xs font-bold mt-1 ml-1">{{ form.errors.title }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Цена (₽)</label>
                    <input v-model="form.price" type="number" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm" />
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Остаток (шт)</label>
                    <input v-model="form.quantity" type="number" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm" />
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Скидка (%)</label>
                    <input v-model="form.discount" type="number" min="0" max="100" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm" />
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Категория</label>
                <select v-model="form.category_id" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-bold transition shadow-sm">
                    <option value="" disabled>— Выберите категорию —</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Описание</label>
                <textarea v-model="form.description" rows="4" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-medium resize-none transition shadow-sm" />
            </div>

            <div v-if="selectedCategory?.characteristics?.length > 0" class="mb-8 p-6 bg-gray-50 rounded-2xl border border-gray-100">
                <div class="text-[11px] font-black text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Характеристики
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="char in selectedCategory.characteristics" :key="char.id">
                        <label class="block text-xs font-bold text-gray-500 mb-1 ml-1">{{ char.name }}</label>
                        <input v-model="form.characteristics[char.id]" type="text" class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm font-medium shadow-sm transition" />
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Изображение</label>
                <div class="flex items-center gap-5 p-5 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="w-24 h-24 bg-white rounded-xl border border-gray-200 shadow-sm flex items-center justify-center overflow-hidden flex-shrink-0">
                        <img v-if="imagePreview" :src="imagePreview" class="max-w-full max-h-full object-contain p-2" />
                        <svg v-else class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <input type="file" accept="image/*" @change="onImageChange" class="text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-white file:border-gray-200 file:border file:text-gray-700 hover:file:bg-gray-50 file:transition cursor-pointer file:shadow-sm" />
                </div>
            </div>

            <button @click="submit" :disabled="form.processing" class="w-full py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-md shadow-blue-200 disabled:opacity-50">
                {{ form.processing ? 'Сохраняем...' : (isEdit ? 'Сохранить изменения' : 'Создать товар') }}
            </button>
        </div>
    </div>
</template>