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
const isCategorySelectOpen = ref(false)

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
const fileName = ref(props.product?.image ? props.product.image.split('/').pop() : null)

function onImageChange(e) {
    const file = e.target.files[0]
    if (!file) {
        if (!form.image) fileName.value = null
        return
    }
    form.image = file
    imagePreview.value = URL.createObjectURL(file)
    fileName.value = file.name
}
</script>

<template>
    <div>
        <!-- Заголовок -->
        <div class="flex items-start sm:items-center gap-4 mb-6">
            <Link href="/admin/products" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shadow-sm flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </Link>
            <h1 class="text-xl sm:text-2xl font-black text-gray-900 leading-tight">{{ isEdit ? 'Редактирование товара' : 'Новый товар' }}</h1>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
            <!-- Название -->
            <div class="mb-6">
                <label class="flex items-center gap-1.5 ml-1 mb-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider leading-none mt-[1px]">Название товара</span>
                </label>
                <input v-model="form.title" type="text" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold transition shadow-sm" :class="{ 'border-red-400': form.errors.title }" />
                <div v-if="form.errors.title" class="text-red-500 text-xs font-bold mt-1 ml-1">{{ form.errors.title }}</div>
            </div>

            <!-- Цена, Остаток, Скидка -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 relative z-10">
                <div>
                    <label class="flex items-center gap-1.5 ml-1 mb-1.5">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider leading-none mt-[1px]">Цена (₽)</span>
                    </label>
                    <input v-model="form.price" type="number" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold transition shadow-sm" />
                </div>
                <div>
                    <label class="flex items-center gap-1.5 ml-1 mb-1.5">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider leading-none mt-[1px]">Остаток (шт)</span>
                    </label>
                    <input v-model="form.quantity" type="number" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold transition shadow-sm" />
                </div>
                <div>
                    <label class="flex items-center gap-1.5 ml-1 mb-1.5">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 4.5h.008v.008h-.008v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider leading-none mt-[1px]">Скидка (%)</span>
                    </label>
                    <input v-model="form.discount" type="number" min="0" max="100" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold transition shadow-sm" />
                </div>
            </div>

            <!-- Категория -->
            <div class="mb-6 relative z-20">
                <div class="flex items-end justify-between mb-1.5">
                    <label class="flex items-center gap-1.5 ml-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider leading-none mt-[1px]">Категория</span>
                    </label>
                    <a href="/admin/categories" target="_blank" class="w-7 h-7 bg-white hover:bg-gray-50 text-gray-400 hover:text-gray-900 rounded-lg flex items-center justify-center transition shadow-sm border border-gray-100" title="Управление категориями">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </a>
                </div>
                <div v-if="isCategorySelectOpen" @click="isCategorySelectOpen = false" class="fixed inset-0 z-10"></div>
                <div class="relative z-20">
                    <button @click="isCategorySelectOpen = !isCategorySelectOpen" type="button" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm font-bold transition shadow-sm flex items-center justify-between text-gray-800">
                        <span class="truncate" :class="!selectedCategory ? 'text-gray-400' : ''">{{ selectedCategory ? selectedCategory.name : '— Выберите категорию —' }}</span>
                        <svg :class="['w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0', isCategorySelectOpen ? 'rotate-180' : '']" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                        <div v-if="isCategorySelectOpen" class="absolute z-10 w-full mt-1 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden max-h-60 overflow-y-auto">
                            <button v-for="cat in categories" :key="cat.id" @click="form.category_id = cat.id; isCategorySelectOpen = false" type="button" :class="['w-full text-left px-4 py-2.5 text-sm font-bold transition flex items-center justify-between', form.category_id === cat.id ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50']">
                                <span class="truncate">{{ cat.name }}</span>
                                <svg v-if="form.category_id === cat.id" class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    </transition>
                </div>
            </div>

            <!-- Описание -->
            <div class="mb-6 relative z-10">
                <label class="flex items-center gap-1.5 ml-1 mb-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" /></svg>
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider leading-none mt-[1px]">Описание</span>
                </label>
                <textarea v-model="form.description" rows="4" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-medium resize-none transition shadow-sm" />
            </div>

            <!-- Характеристики -->
            <div v-if="selectedCategory" class="mb-8 p-6 bg-purple-50 rounded-2xl border border-gray-100 relative z-10 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="text-[11px] font-black text-purple-600 uppercase tracking-wider leading-none mt-[1px]">Характеристики</span>
                    </div>
                    <a :href="`/admin/characteristics?category_id=${selectedCategory.id}`" target="_blank" class="w-7 h-7 bg-white hover:bg-purple-50 text-gray-400 hover:text-purple-600 rounded-lg flex items-center justify-center transition shadow-sm border border-gray-100" title="Управление характеристиками">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                    </a>
                </div>
                <div v-if="selectedCategory.characteristics?.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="char in selectedCategory.characteristics" :key="char.id">
                        <label class="block text-xs font-bold text-gray-500 mb-1 ml-1">{{ char.name }}</label>
                        <input v-model="form.characteristics[char.id]" type="text" class="w-full px-4 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-medium shadow-sm transition" />
                    </div>
                </div>
                <div v-else class="text-sm text-gray-400 font-medium text-center py-2">У этой категории пока нет характеристик</div>
            </div>

            <!-- Изображение -->
            <div class="mb-8 relative z-10">
                <label class="flex items-center gap-1.5 ml-1 mb-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-wider leading-none mt-[1px]">Изображение</span>
                </label>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-5 p-4 sm:p-5 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 bg-white rounded-xl border border-gray-200 shadow-sm flex items-center justify-center overflow-hidden flex-shrink-0">
                        <img v-if="imagePreview" :src="imagePreview" class="max-w-full max-h-full object-contain p-2" />
                        <svg v-else class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="w-full flex-grow min-w-0 flex items-center gap-4">
                        <input type="file" id="image-upload" @change="onImageChange" class="hidden" accept="image/*">
                        <label for="image-upload" class="px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm cursor-pointer whitespace-nowrap">
                            Выберите файл
                        </label>
                        <span class="text-sm text-gray-500 truncate">{{ fileName || 'Файл не выбран' }}</span>
                    </div>
                </div>
            </div>

            <!-- Кнопка отправки -->
            <button @click="submit" :disabled="form.processing" class="relative z-10 w-full py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-md shadow-blue-200 disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ form.processing ? 'Сохраняем...' : (isEdit ? 'Сохранить изменения' : 'Создать товар') }}
            </button>
        </div>
    </div>
</template>