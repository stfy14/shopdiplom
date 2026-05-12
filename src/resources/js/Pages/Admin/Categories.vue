<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
    categories:    Array,  // Плоский список с полем depth
    allCategories: Array,  // Для выбора родителя
})

// ── Состояния ──────────────────────────────────────────────────────────────
const activeTab    = ref('view')  // view | add
const editingId    = ref(null)
const addParentId  = ref(null)    // null = корневая категория

// Для кастомного селекта
const isParentSelectOpen = ref(false)

// ── Вычислить имя родителя для заголовка формы добавления ──
const addParentName = computed(() => {
    if (!addParentId.value) return 'корневую категорию'
    return props.allCategories.find(c => c.id === addParentId.value)?.name ?? '...'
})

// ── Вычислить имя выбранной категории для самого кастомного селекта ──
const selectedParentName = computed(() => {
    if (!addForm.parent_id) return '— Корневая категория —'
    const cat = props.allCategories.find(c => c.id === addForm.parent_id)
    return cat ? cat.name : '— Корневая категория —'
})

// ── Форма добавления ────────────────────────────────────────────────────────
const addForm = useForm({
    parent_id:   '',
    name:        '',
    code:        '',
    description: '',
    sort_order:  0,
    gost:        '',
    din:         '',
    image:       null,
})
const addPreview = ref(null)

function openAdd(parentId = null) {
    addParentId.value = parentId
    addForm.reset()
    addForm.parent_id = parentId ?? ''
    addPreview.value = null
    activeTab.value = 'add'
}

function onAddImage(e) {
    const file = e.target.files[0]
    if (!file) return
    addForm.image = file
    addPreview.value = URL.createObjectURL(file)
}

function submitAdd() {
    addForm.transform(data => ({ ...data })).post('/admin/categories', {
        forceFormData: true,
        onSuccess: () => { addForm.reset(); addPreview.value = null; activeTab.value = 'view' },
    })
}

// ── Форма редактирования ────────────────────────────────────────────────────
const editForm = useForm({
    name:        '',
    code:        '',
    description: '',
    sort_order:  0,
    gost:        '',
    din:         '',
    image:       null,
})
const editPreview   = ref(null)
const editCurrent   = ref(null)

function startEdit(cat) {
    editingId.value  = cat.id
    editCurrent.value = cat
    editForm.name        = cat.name
    editForm.code        = cat.code
    editForm.description = cat.description ?? ''
    editForm.sort_order  = cat.sort_order ?? 0
    editForm.gost        = cat.gost ?? ''
    editForm.din         = cat.din ?? ''
    editForm.image       = null
    editPreview.value    = cat.image ? `/storage/${cat.image}` : null
    activeTab.value      = 'view'
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

function cancelEdit() { editingId.value = null }

function deleteCategory(id) {
    if (confirm('Удалить категорию? Все вложенные категории и товары будут удалены.')) {
        router.delete(`/admin/categories/${id}`)
    }
}

// ── Дерево с отступами ──────────────────────────────────────────────────────
const indentPx = (depth) => depth * 32

const depthColors = ['border-l-gray-300', 'border-l-blue-300', 'border-l-purple-300', 'border-l-green-300', 'border-l-orange-300']
const depthColor  = (depth) => depth > 0 ? (depthColors[depth] ?? 'border-l-gray-300') : ''

function childrenOf(parentId) {
    return props.categories.filter(c => c.parent_id === parentId)
}

const totalProducts = computed(() =>
    props.categories.filter(c => c.depth === 0).reduce((s, c) => {
        return s
    }, 0)
)
</script>

<template>
    <div>
        <!-- Заголовок -->
        <div class="flex items-center gap-4 mb-6">
            <h1 class="text-2xl font-black text-gray-900">Управление категориями</h1>
            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-lg">{{ categories.length }} категорий</span>
        </div>

        <!-- Вкладки -->
        <div class="flex gap-2 mb-6 p-1 bg-white border border-gray-100 rounded-xl shadow-sm w-full sm:w-auto sm:inline-flex">
            <button @click="activeTab = 'view'; editingId = null" :class="['flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition', activeTab === 'view' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                Дерево категорий
            </button>
            <button @click="openAdd(null)" :class="['flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition', activeTab === 'add' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-blue-600 hover:bg-blue-50']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Новая категория
            </button>
        </div>

        <!-- ── Форма добавления ─────────────────────────────────────────────── -->
        <div v-if="activeTab === 'add'" class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 mb-6">
            <h2 class="font-black text-lg mb-6 flex items-center gap-2 text-gray-900">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Добавить категорию
                <span class="font-medium text-gray-400 text-base">в «{{ addParentName }}»</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                
                <!-- Кастомный селект Родителя -->
                <div class="md:col-span-2 relative z-20">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Родительская категория</label>
                    
                    <div v-if="isParentSelectOpen" @click="isParentSelectOpen = false" class="fixed inset-0 z-10"></div>
                    
                    <div class="relative z-20">
                        <button @click="isParentSelectOpen = !isParentSelectOpen" type="button" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm font-bold transition shadow-sm flex items-center justify-between text-gray-800">
                            <span class="truncate" :class="!addForm.parent_id ? 'text-gray-400' : ''">{{ selectedParentName }}</span>
                            <svg :class="['w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0', isParentSelectOpen ? 'rotate-180' : '']" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        
                        <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                            <div v-if="isParentSelectOpen" class="absolute z-10 w-full mt-1 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden max-h-60 overflow-y-auto">
                                
                                <!-- Корневая категория -->
                                <button @click="addForm.parent_id = ''; isParentSelectOpen = false" type="button" :class="['w-full text-left px-4 py-2.5 text-sm font-bold transition flex items-center justify-between', addForm.parent_id === '' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50']">
                                    <span class="truncate">— Корневая категория —</span>
                                    <svg v-if="addForm.parent_id === ''" class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                
                                <!-- Список категорий (с отступами) -->
                                <button v-for="cat in allCategories" :key="cat.id" @click="addForm.parent_id = cat.id; isParentSelectOpen = false" type="button" :class="['w-full text-left px-4 py-2.5 text-sm font-bold transition flex items-center justify-between', addForm.parent_id === cat.id ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50']">
                                    <span class="truncate whitespace-pre">{{ '│ '.repeat(categories.find(c => c.id === cat.id)?.depth ?? 0) }}{{ cat.name }}</span>
                                    <svg v-if="addForm.parent_id === cat.id" class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </transition>
                    </div>
                </div>

                <div class="relative z-10">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Название</label>
                    <input v-model="addForm.name" type="text" placeholder="Зажимы для каната" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition" :class="{ 'border-red-400': addForm.errors.name }"/>
                    <div v-if="addForm.errors.name" class="text-red-500 text-xs mt-1">{{ addForm.errors.name }}</div>
                </div>
                <div class="relative z-10">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Код (латиница, уникальный)</label>
                    <input v-model="addForm.code" type="text" placeholder="clamps" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition" :class="{ 'border-red-400': addForm.errors.code }"/>
                    <div v-if="addForm.errors.code" class="text-red-500 text-xs mt-1">{{ addForm.errors.code }}</div>
                </div>

                <!-- ГОСТ и DIN -->
                <div class="relative z-10">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">ГОСТ (необязательно)</label>
                    <input v-model="addForm.gost" type="text" placeholder="13186-87" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition" />
                </div>
                <div class="relative z-10">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">DIN (необязательно)</label>
                    <input v-model="addForm.din" type="text" placeholder="741" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition" />
                </div>

                <div class="relative z-10">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Порядок сортировки</label>
                    <input v-model="addForm.sort_order" type="number" min="0" class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition"/>
                </div>
                <div class="relative z-10">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Картинка</label>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img v-if="addPreview" :src="addPreview" class="w-full h-full object-cover"/>
                            <svg v-else class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                        </div>
                        <label class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition cursor-pointer shadow-sm">
                            Выбрать файл
                            <input type="file" class="hidden" accept="image/*" @change="onAddImage"/>
                        </label>
                    </div>
                </div>
                <div class="md:col-span-2 relative z-10">
                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Описание</label>
                    <textarea v-model="addForm.description" rows="2" placeholder="Краткое описание категории..." class="w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-medium resize-none shadow-sm transition"/>
                </div>
            </div>
            <div class="flex gap-3 relative z-10">
                <button @click="submitAdd" :disabled="addForm.processing" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-sm disabled:opacity-50">
                    Добавить
                </button>
                <button @click="activeTab = 'view'" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition shadow-sm">
                    Отмена
                </button>
            </div>
        </div>

        <!-- ── Дерево категорий (плоский список с отступами) ───────────────── -->
        <div v-if="activeTab === 'view'" class="flex flex-col gap-2">
            <div v-if="categories.length === 0" class="text-center py-16 text-gray-400 bg-white rounded-3xl border border-gray-100 shadow-sm">
                Категорий нет. Создайте первую!
            </div>

            <template v-for="cat in categories" :key="cat.id">

                <!-- ── Просмотр узла ── -->
                <div v-if="editingId !== cat.id"
                     :class="['bg-white rounded-2xl shadow-sm border border-gray-100 transition hover:shadow-md group',
                              cat.depth > 0 ? 'border-l-4 ' + depthColor(cat.depth) : '']"
                     :style="{ marginLeft: indentPx(cat.depth) + 'px' }">
                    <div class="flex items-center gap-3 p-4">
                        <!-- Картинка -->
                        <div class="w-10 h-10 bg-gray-50 border border-gray-100 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img v-if="cat.image" :src="`/storage/${cat.image}`" class="w-full h-full object-cover"/>
                            <svg v-else class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        </div>

                        <!-- Инфо -->
                        <div class="flex-grow min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-black text-gray-900">{{ cat.name }}</span>
                                <span class="font-mono text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ cat.code }}</span>
                                <span v-if="cat.gost" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">ГОСТ {{ cat.gost }}</span>
                                <span v-if="cat.din" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200">DIN {{ cat.din }}</span>
                                <span class="text-xs text-gray-400 font-bold">{{ childrenOf(cat.id).length }} подразд.</span>
                            </div>
                            <p v-if="cat.description" class="text-xs text-gray-400 mt-0.5 truncate max-w-lg">{{ cat.description }}</p>
                        </div>

                        <!-- Действия -->
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <button @click="openAdd(cat.id)" class="px-3 py-1.5 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span class="hidden sm:inline">Подраздел</span>
                            </button>
                            <button @click="startEdit(cat)" class="w-8 h-8 bg-gray-50 hover:bg-blue-50 text-gray-400 hover:text-blue-600 rounded-xl flex items-center justify-center transition border border-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <button @click="deleteCategory(cat.id)" class="w-8 h-8 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-xl flex items-center justify-center transition border border-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── Форма редактирования узла ── -->
                <div v-else
                     :class="['bg-blue-50/50 rounded-2xl border-2 border-blue-200 p-5']"
                     :style="{ marginLeft: indentPx(cat.depth) + 'px' }">
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
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">ГОСТ</label>
                            <input v-model="editForm.gost" type="text" placeholder="13186-87" class="w-full px-3 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">DIN</label>
                            <input v-model="editForm.din" type="text" placeholder="741" class="w-full px-3 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">Порядок</label>
                            <input v-model="editForm.sort_order" type="number" class="w-full px-3 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold shadow-sm transition"/>
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
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1 ml-1">Описание</label>
                            <textarea v-model="editForm.description" rows="2" class="w-full px-3 py-2.5 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-medium resize-none shadow-sm transition"/>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="submitEdit(cat.id)" :disabled="editForm.processing" class="px-6 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition shadow-sm disabled:opacity-50">Сохранить</button>
                        <button @click="cancelEdit" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 text-sm font-bold rounded-xl hover:bg-gray-50 transition shadow-sm">Отмена</button>
                    </div>
                </div>

            </template>
        </div>
    </div>
</template>