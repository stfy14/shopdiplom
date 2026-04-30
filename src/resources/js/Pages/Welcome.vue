<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3'
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { ref } from 'vue'

const page = usePage()
const callbackSent = ref(page.props.flash?.callback_sent ?? false)

const form = useForm({
    name: '',
    phone: '',
    comment: '',
})

function phoneInput(e) {
    let value = e.target.value.replace(/\D/g, '')
    if (value === '') { form.phone = ''; e.target.value = ''; return }
    let formatted = '+' + value.substring(0, 1)
    if (value.length > 1) formatted += ' (' + value.substring(1, 4)
    if (value.length > 4) formatted += ') ' + value.substring(4, 7)
    if (value.length > 7) formatted += '-' + value.substring(7, 9)
    if (value.length > 9) formatted += '-' + value.substring(9, 11)
    form.phone = formatted
    e.target.value = formatted
}

function submitCallback() {
    form.post('/callback', {
        onSuccess: () => {
            callbackSent.value = true
            form.reset()
        },
    })
}
</script>

<template>
    <ShopLayout>
        <!-- Hero -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-gray-900 via-slate-800 to-blue-900 mb-10 min-h-[380px] md:min-h-[440px] flex items-center">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -right-32 -top-32 w-96 h-96 rounded-full bg-blue-600/10 blur-3xl"></div>
                <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-slate-500/10 blur-3xl"></div>
                <svg class="absolute inset-0 w-full h-full opacity-5" xmlns="http://www.w3.org/2000/svg">
                    <defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/></pattern></defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>
            <div class="relative z-10 px-8 md:px-14 py-14 md:py-20 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-500/20 border border-blue-400/30 rounded-full text-blue-300 text-xs font-bold tracking-wider uppercase mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                    Официальный дилер с 2012 года
                </div>
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-white leading-tight mb-5">
                    Промышленное<br>
                    <span class="text-blue-400">грузоподъёмное</span><br>
                    оборудование
                </h1>
                <p class="text-slate-300 text-base md:text-lg font-medium mb-8 max-w-xl leading-relaxed">
                    Зажимы, талрепы, стропы, тали и лебёдки от ведущих производителей.
                    Сертифицированная продукция с гарантией и документами.
                </p>
                <div class="flex flex-wrap gap-3">
                    <Link href="/catalog" class="px-7 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition shadow-lg shadow-blue-900/40 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Каталог продукции
                    </Link>
                    <Link href="/search" class="px-7 py-3.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold rounded-xl transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Поиск товаров
                    </Link>
                </div>
            </div>
        </div>

        <!-- Категории -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-black text-gray-900">Категории товаров</h2>
                <Link href="/catalog" class="text-blue-600 hover:text-blue-800 text-sm font-bold flex items-center gap-1.5 transition">
                    Все категории
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Link href="/catalog/rigging" class="bg-gradient-to-br from-slate-800 to-slate-700 rounded-2xl p-6 text-white hover:scale-[1.02] transition-transform duration-200 flex flex-col group">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                    </div>
                    <span class="text-[11px] font-bold bg-white/20 px-2.5 py-1 rounded-lg self-start mb-3">Более 70 позиций</span>
                    <h3 class="font-black text-base leading-snug mb-3">Такелажное оборудование</h3>
                    <ul class="mt-auto space-y-1">
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Зажимы для каната</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Талрепы</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Стропы</li>
                    </ul>
                    <div class="mt-4 flex items-center gap-1 text-xs font-bold text-white/80 group-hover:text-white transition">
                        Перейти в раздел <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </Link>
                <Link href="/catalog/lifting-equipment" class="bg-gradient-to-br from-blue-800 to-blue-700 rounded-2xl p-6 text-white hover:scale-[1.02] transition-transform duration-200 flex flex-col group">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                    </div>
                    <span class="text-[11px] font-bold bg-white/20 px-2.5 py-1 rounded-lg self-start mb-3">Для производства</span>
                    <h3 class="font-black text-base leading-snug mb-3">Грузоподъёмное оборудование</h3>
                    <ul class="mt-auto space-y-1">
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Тали электрические</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Тали ручные</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Лебёдки, Краны</li>
                    </ul>
                    <div class="mt-4 flex items-center gap-1 text-xs font-bold text-white/80 group-hover:text-white transition">
                        Перейти в раздел <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </Link>
                <Link href="/catalog/hydraulic" class="bg-gradient-to-br from-zinc-700 to-zinc-600 rounded-2xl p-6 text-white hover:scale-[1.02] transition-transform duration-200 flex flex-col group">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/></svg>
                    </div>
                    <span class="text-[11px] font-bold bg-white/20 px-2.5 py-1 rounded-lg self-start mb-3">TOR и другие</span>
                    <h3 class="font-black text-base leading-snug mb-3">Гидравлическое оборудование</h3>
                    <ul class="mt-auto space-y-1">
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Домкраты бутылочные</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Подкатные домкраты</li>
                    </ul>
                    <div class="mt-4 flex items-center gap-1 text-xs font-bold text-white/80 group-hover:text-white transition">
                        Перейти в раздел <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </Link>
                <Link href="/catalog/warehouse" class="bg-gradient-to-br from-stone-700 to-stone-600 rounded-2xl p-6 text-white hover:scale-[1.02] transition-transform duration-200 flex flex-col group">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    </div>
                    <span class="text-[11px] font-bold bg-white/20 px-2.5 py-1 rounded-lg self-start mb-3">Ручные и электрические</span>
                    <h3 class="font-black text-base leading-snug mb-3">Складское оборудование</h3>
                    <ul class="mt-auto space-y-1">
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Тележки для талей</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Складские тележки</li>
                    </ul>
                    <div class="mt-4 flex items-center gap-1 text-xs font-bold text-white/80 group-hover:text-white transition">
                        Перейти в раздел <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Хиты -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-black text-gray-900">Хиты продаж</h2>
                    <p class="text-gray-500 text-sm mt-0.5">Зажимы и талрепы — самые популярные позиции</p>
                </div>
                <Link href="/catalog/rigging" class="text-blue-600 hover:text-blue-800 text-sm font-bold flex items-center gap-1.5 transition">
                    Смотреть все <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Link href="/catalog/clamps" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md hover:-translate-y-0.5 transition-all group flex items-center gap-5">
                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                    </div>
                    <div class="flex-grow min-w-0">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Такелажное оборудование</div>
                        <div class="font-black text-gray-900 text-lg leading-tight group-hover:text-blue-600 transition">Зажимы для каната</div>
                        <div class="text-sm text-gray-500 mt-1">DIN 741, нержавейка A4, пластинчатые. Диаметры 6–32 мм.</div>
                    </div>
                    <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-500 flex-shrink-0 transition" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
                <Link href="/catalog/turnbuckles" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md hover:-translate-y-0.5 transition-all group flex items-center gap-5">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                    </div>
                    <div class="flex-grow min-w-0">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Такелажное оборудование</div>
                        <div class="font-black text-gray-900 text-lg leading-tight group-hover:text-blue-600 transition">Талрепы</div>
                        <div class="text-sm text-gray-500 mt-1">Крюк-крюк, крюк-кольцо, кольцо-кольцо. Резьба M6–M30.</div>
                    </div>
                    <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-500 flex-shrink-0 transition" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
            </div>
        </div>

        <!-- Контакты + Форма звонка -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12" id="contacts">

            <!-- Контактная информация -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
                <h2 class="text-2xl font-black text-gray-900 mb-6">Контакты</h2>
                <div class="space-y-5">
                    <a href="tel:+73431234567" class="flex items-center gap-4 group">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-all duration-200 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Телефон поддержки</div>
                            <div class="text-xl font-black text-gray-900 group-hover:text-blue-600 transition">+7 (343) 123-45-67</div>
                            <div class="text-xs text-gray-400 font-medium mt-0.5">Пн–Пт: 9:00 – 18:00 (МСК)</div>
                        </div>
                    </a>
                    <div class="h-px bg-gray-100"></div>
                    <a href="mailto:info@zpority.ru" class="flex items-center gap-4 group">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-all duration-200 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Email</div>
                            <div class="text-lg font-black text-gray-900 group-hover:text-blue-600 transition">info@zpority.ru</div>
                            <div class="text-xs text-gray-400 font-medium mt-0.5">Ответим в течение 2 часов</div>
                        </div>
                    </a>
                    <div class="h-px bg-gray-100"></div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm border border-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Адрес магазина</div>
                            <div class="text-base font-black text-gray-900">г. Екатеринбург</div>
                            <div class="text-sm text-gray-500 font-medium">ул. Металлургов, 12, офис 301</div>
                        </div>
                    </div>
                    <div class="h-px bg-gray-100"></div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-50 text-gray-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm border border-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Режим работы</div>
                            <div class="text-base font-black text-gray-900">Пн – Пт: 9:00 – 18:00</div>
                            <div class="text-sm text-gray-500 font-medium">Сб – Вс: выходной</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Форма запроса на звонок -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
                <h2 class="text-2xl font-black text-gray-900 mb-2">Заказать звонок</h2>
                <p class="text-gray-500 text-sm mb-6">Оставьте номер — перезвоним в течение 15 минут в рабочее время</p>

                <!-- Успех -->
                <Transition name="fade">
                    <div v-if="callbackSent" class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="w-16 h-16 bg-green-50 text-green-500 rounded-2xl flex items-center justify-center mb-4 shadow-sm border border-green-100">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="font-black text-gray-900 text-xl mb-2">Заявка отправлена!</div>
                        <p class="text-gray-500 text-sm mb-5">Мы перезвоним вам в течение 15 минут в рабочее время.</p>
                        <button @click="callbackSent = false" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-sm transition">
                            Отправить ещё
                        </button>
                    </div>
                </Transition>

                <div v-if="!callbackSent" class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Ваше имя</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Иван Петров"
                            class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold transition shadow-sm"
                            :class="{ 'border-red-400 bg-red-50': form.errors.name }"
                        />
                        <div v-if="form.errors.name" class="text-red-500 text-xs font-bold mt-1 ml-1">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Номер телефона</label>
                        <input
                            :value="form.phone"
                            @input="phoneInput"
                            type="tel"
                            placeholder="+7 (999) 000-00-00"
                            maxlength="18"
                            class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-bold transition shadow-sm"
                            :class="{ 'border-red-400 bg-red-50': form.errors.phone }"
                        />
                        <div v-if="form.errors.phone" class="text-red-500 text-xs font-bold mt-1 ml-1">{{ form.errors.phone }}</div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Комментарий <span class="text-gray-300 font-medium normal-case">(необязательно)</span></label>
                        <textarea
                            v-model="form.comment"
                            rows="3"
                            placeholder="Например: интересует оптовый заказ зажимов..."
                            class="w-full px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none text-sm font-medium resize-none transition shadow-sm"
                        ></textarea>
                    </div>
                    <button
                        @click="submitCallback"
                        :disabled="form.processing"
                        class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition shadow-md shadow-blue-600/20 disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <svg v-if="!form.processing" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                        {{ form.processing ? 'Отправляем...' : 'Заказать звонок' }}
                    </button>
                    <p class="text-center text-xs text-gray-400">Нажимая кнопку, вы соглашаетесь на обработку персональных данных</p>
                </div>
            </div>
        </div>

        <!-- Преимущества -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 md:p-10">
            <h2 class="text-2xl font-black text-gray-900 mb-8 text-center">Почему выбирают нас</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">Официальный дилер</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Прямые поставки от производителей. Вся продукция сертифицирована и соответствует ГОСТ.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">Быстрая доставка</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Отправка в день заказа. Доставляем по всей России транспортными компаниями.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">Документы</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Паспорта, сертификаты, счета-фактуры для юридических лиц. Работаем по договору.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/></svg>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">Гарантия и сервис</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Гарантия от 12 месяцев на всё оборудование. Консультации технических специалистов.</p>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>