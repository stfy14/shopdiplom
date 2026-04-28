<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ categories: Array })

// ── Состояния панелей ──
const activeTab    = ref('view')  // view | add-parent | add-child
const editingId    = ref(null)
const parentForNew = ref(null)

// ── Форма: новая родительская категория ──
const parentForm = useForm({
    name:        '',
    code:        '',
    description: '',
    sort_order:  0,
    image:       null,
})
const parentPreview = ref(null)

function onParentImage(e) {
    const file = e.target.files[0]
    if (!file) return
    parentForm.image = file
    parentPreview.value = URL.createObjectURL(file)
}

function submitParent() {
    parentForm.transform(data => ({ ...data })).post('/admin/categories/parent', {
        forceFormData: true,
        onSuccess: () => { parentForm.reset(); parentPreview.value = null; activeTab.value = 'view' },
    })
}

// ── Форма: новая дочерняя категория ──
const childForm = useForm({
    parent_id:   '',
    name:        '',
    code:        '',
    description: '',
    sort_order:  0,
    image:       null,
})
const childPreview = ref(null)

function openAddChild(parentId) {
    childForm.parent_id = parentId
    parentForNew.value = props.categories.find(c => c.id === parentId)
    activeTab.value = 'add-child'
    childPreview.value = null
    childForm.reset('name', 'code', 'description', 'image')
    childForm.parent_id = parentId
}

function onChildImage(e) {
    const file = e.target.files[0]
    if (!file) return
    childForm.image = file
    childPreview.value = URL.createObjectURL(file)
}

function submitChild() {
    childForm.transform(data => ({ ...data })).post('/admin/categories/child', {
        forceFormData: true,
        onSuccess: () => { childForm.reset(); childPreview.value = null; activeTab.value = 'view' },
    })
}

// ── Редактирование ──
const editForm = useForm({
    name:        '',
    code:        '',
    description: '',
    sort_order:  0,
    image:       null,
})
const editPreview = ref(null)
const editCurrent = ref(null)

function startEdit(cat) {
    editingId.value = cat.id
    editCurrent.value = cat
    editForm.name        = cat.name
    editForm.code        = cat.code
    editForm.description = cat.description ?? ''
    editForm.sort_order  = cat.sort_order ?? 0
    editForm.image       = null
    editPreview.value    = cat.image ? `/storage/${cat.image}` : null
}

function onEditImage(e) {
    const file = e.target.files[0]
    if (!file) return
    editForm.image = file
    editPreview.value = URL.createObjectURL(file)
}

function submitEdit(catId) {
    editForm.transform(data => ({ ...data, _method: 'put' })).post(`/admin/categories/${catId}`, {
        forceFormData: true,
        onSuccess: () => { editingId.value = null; editCurrent.value = null },
    })
}

function deleteCategory(id) {
    if (confirm('Удалить категорию? Все дочерние категории и товары будут удалены.')) {
        router.delete(`/admin/categories/${id}`)
    }
}

function cancelEdit() { editingId.value = null }

// Общее кол-во товаров
const totalProducts = computed(() =>
    props.categories.reduce((s, c) =>
        s + (c.children ?? []).reduce((cs, ch) => cs + (ch.products_count ?? 0), 0), 0)
)
</script>

<template>
    <div>
        <!-- Заголовок -->
        <div class="flex items-center gap-4 mb-6">
            <h1 class="text-2xl font-black text-gray-900">Управление категориями</h1>
            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-lg">{{ totalProducts }} товаров</span>
        </div>

        <!-- Вкладки -->
        <div class="flex gap-2 mb-6 p-1 bg-white border border-gray-100 rounded-xl shadow-sm w-full sm:w-auto inline-flex">
            <button @click="activeTab = 'view'" :class="['flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition', activeTab === 'view' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                Дерево категорий
            </button>
            <button @click="activeTab = 'add-parent'" :class="['flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition', activeTab === 'add-parent' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Новая категория
            </button>
        </div>

        <!-- Форма: новая родительская категория -->
        <div v-if="activeTab === 'add-parent'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 mb-6">
            <h2 class="font-black text-lg mb-6 flex items-center gap-2 text-gray-900">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Добавить родительскую категорию
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Название</label>
                    <input v-model="parentForm.name" type="text" placeholder="Такелажное оборудование" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition" :class="{ 'border-red-400': parentForm.errors.name }"/>
                    <div v-if="parentForm.errors.name" class="text-red-500 text-xs mt-1">{{ parentForm.errors.name }}</div>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Код (латиница)</label>
                    <input v-model="parentForm.code" type="text" placeholder="rigging" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition" :class="{ 'border-red-400': parentForm.errors.code }"/>
                    <div v-if="parentForm.errors.code" class="text-red-500 text-xs mt-1">{{ parentForm.errors.code }}</div>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Порядок сортировки</label>
                    <input v-model="parentForm.sort_order" type="number" min="0" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition"/>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Картинка</label>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img v-if="parentPreview" :src="parentPreview" class="w-full h-full object-cover"/>
                            <svg v-else class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                        </div>
                        <label class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition cursor-pointer shadow-sm">
                            Выбрать файл
                            <input type="file" class="hidden" accept="image/*" @change="onParentImage"/>
                        </label>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Описание</label>
                    <textarea v-model="parentForm.description" rows="2" placeholder="Краткое описание категории..." class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-medium resize-none shadow-sm transition"/>
                </div>
            </div>
            <div class="flex gap-3">
                <button @click="submitParent" :disabled="parentForm.processing" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-sm disabled:opacity-50">
                    Добавить
                </button>
                <button @click="activeTab = 'view'" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition shadow-sm">
                    Отмена
                </button>
            </div>
        </div>

        <!-- Форма: новая дочерняя категория -->
        <div v-if="activeTab === 'add-child'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 mb-6">
            <h2 class="font-black text-lg mb-1 flex items-center gap-2 text-gray-900">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Добавить подкатегорию
            </h2>
            <p class="text-sm text-gray-500 mb-6">Раздел: <span class="font-bold text-gray-700">{{ parentForNew?.name }}</span></p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Название</label>
                    <input v-model="childForm.name" type="text" placeholder="Зажимы для каната" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition" :class="{ 'border-red-400': childForm.errors.name }"/>
                    <div v-if="childForm.errors.name" class="text-red-500 text-xs mt-1">{{ childForm.errors.name }}</div>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Код</label>
                    <input v-model="childForm.code" type="text" placeholder="clamps" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition" :class="{ 'border-red-400': childForm.errors.code }"/>
                    <div v-if="childForm.errors.code" class="text-red-500 text-xs mt-1">{{ childForm.errors.code }}</div>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Порядок</label>
                    <input v-model="childForm.sort_order" type="number" min="0" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition"/>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Картинка</label>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img v-if="childPreview" :src="childPreview" class="w-full h-full object-cover"/>
                            <svg v-else class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                        </div>
                        <label class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition cursor-pointer shadow-sm">
                            Выбрать файл
                            <input type="file" class="hidden" accept="image/*" @change="onChildImage"/>
                        </label>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Описание</label>
                    <textarea v-model="childForm.description" rows="2" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-medium resize-none shadow-sm transition"/>
                </div>
            </div>
            <div class="flex gap-3">
                <button @click="submitChild" :disabled="childForm.processing" class="px-8 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition shadow-sm disabled:opacity-50">
                    Добавить
                </button>
                <button @click="activeTab = 'view'" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition shadow-sm">
                    Отмена
                </button>
            </div>
        </div>

        <!-- Дерево категорий -->
        <div v-if="activeTab === 'view'" class="space-y-4">
            <div v-if="categories.length === 0" class="text-center py-16 text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
                Категорий нет. Создайте первую!
            </div>

            <div v-for="parent in categories" :key="parent.id" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">

                <!-- Строка родительской категории -->
                <div v-if="editingId !== parent.id" class="flex items-center gap-4 p-5 border-b border-gray-50">
                    <div class="w-12 h-12 bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                        <img v-if="parent.image" :src="`/storage/${parent.image}`" class="w-full h-full object-cover"/>
                        <svg v-else class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                    <div class="flex-grow min-w-0">
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="font-black text-gray-900 text-base">{{ parent.name }}</span>
                            <span class="font-mono text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-lg">{{ parent.code }}</span>
                            <span class="text-xs font-bold text-gray-400">{{ parent.children?.length ?? 0 }} подразделов</span>
                        </div>
                        <p v-if="parent.description" class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ parent.description }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button @click="openAddChild(parent.id)" class="px-3 py-2 bg-green-50 hover:bg-green-100 text-green-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span class="hidden sm:inline">Подраздел</span>
                        </button>
                        <button @click="startEdit(parent)" class="w-9 h-9 bg-gray-50 hover:bg-blue-50 text-gray-400 hover:text-blue-600 rounded-xl flex items-center justify-center transition border border-gray-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <button @click="deleteCategory(parent.id)" class="w-9 h-9 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-xl flex items-center justify-center transition border border-gray-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Редактирование родителя -->
                <div v-else class="p-5 border-b border-blue-50 bg-blue-50/30">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">Название</label>
                            <input v-model="editForm.name" type="text" class="w-full px-3 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">Код</label>
                            <input v-model="editForm.code" type="text" class="w-full px-3 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">Картинка</label>
                            <div class="flex items-center gap-2">
                                <div class="w-10 h-10 bg-white border border-gray-200 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    <img v-if="editPreview" :src="editPreview" class="w-full h-full object-cover"/>
                                    <svg v-else class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159"/></svg>
                                </div>
                                <label class="px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 cursor-pointer transition shadow-sm">
                                    Изменить
                                    <input type="file" class="hidden" accept="image/*" @change="onEditImage"/>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">Порядок</label>
                            <input v-model="editForm.sort_order" type="number" class="w-full px-3 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition"/>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">Описание</label>
                            <textarea v-model="editForm.description" rows="2" class="w-full px-3 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-medium resize-none shadow-sm transition"/>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="submitEdit(parent.id)" :disabled="editForm.processing" class="px-6 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition shadow-sm disabled:opacity-50">Сохранить</button>
                        <button @click="cancelEdit" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 text-sm font-bold rounded-xl hover:bg-gray-50 transition shadow-sm">Отмена</button>
                    </div>
                </div>

                <!-- Дочерние категории -->
                <div class="divide-y divide-gray-50">
                    <div v-if="!parent.children || parent.children.length === 0" class="px-5 py-3 text-xs text-gray-400 font-medium italic">
                        Подразделов нет — нажмите «+ Подраздел»
                    </div>

                    <template v-for="child in parent.children" :key="child.id">
                        <!-- Просмотр дочерней -->
                        <div v-if="editingId !== child.id" class="flex items-center gap-3 px-5 py-3 pl-12 hover:bg-gray-50 transition group">
                            <div class="w-9 h-9 bg-gray-50 border border-gray-100 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                                <img v-if="child.image" :src="`/storage/${child.image}`" class="w-full h-full object-cover"/>
                                <svg v-else class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            </div>
                            <div class="w-1.5 h-1.5 rounded-full bg-gray-200 flex-shrink-0"></div>
                            <div class="flex-grow min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-gray-800 text-sm">{{ child.name }}</span>
                                    <span class="font-mono text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ child.code }}</span>
                                    <span class="text-xs font-bold text-gray-400">{{ child.products_count ?? 0 }} товаров</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition flex-shrink-0">
                                <button @click="startEdit(child)" class="w-8 h-8 bg-gray-50 hover:bg-blue-50 text-gray-400 hover:text-blue-600 rounded-xl flex items-center justify-center transition border border-gray-100">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button @click="deleteCategory(child.id)" class="w-8 h-8 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-xl flex items-center justify-center transition border border-gray-100">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Редактирование дочерней -->
                        <div v-else class="px-5 py-4 pl-12 bg-blue-50/30 border-l-4 border-blue-400">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">Название</label>
                                    <input v-model="editForm.name" type="text" class="w-full px-3 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition"/>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">Код</label>
                                    <input v-model="editForm.code" type="text" class="w-full px-3 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition"/>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">Картинка</label>
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 bg-white border border-gray-200 rounded-xl overflow-hidden flex items-center justify-center flex-shrink-0">
                                            <img v-if="editPreview" :src="editPreview" class="w-full h-full object-cover"/>
                                            <svg v-else class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159"/></svg>
                                        </div>
                                        <label class="px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 cursor-pointer transition shadow-sm">
                                            Изменить
                                            <input type="file" class="hidden" accept="image/*" @change="onEditImage"/>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">Описание</label>
                                    <input v-model="editForm.description" type="text" class="w-full px-3 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-medium shadow-sm transition"/>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="submitEdit(child.id)" :disabled="editForm.processing" class="px-5 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition shadow-sm disabled:opacity-50">Сохранить</button>
                                <button @click="cancelEdit" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 text-sm font-bold rounded-xl hover:bg-gray-50 transition shadow-sm">Отмена</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>