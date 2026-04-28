<script setup>
import { Link } from '@inertiajs/vue3'
import ShopLayout from '@/Layouts/ShopLayout.vue'
</script>

<template>
    <ShopLayout>
        <!-- Hero -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-gray-900 via-slate-800 to-blue-900 mb-10 min-h-[380px] md:min-h-[440px] flex items-center">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -right-32 -top-32 w-96 h-96 rounded-full bg-blue-600/10 blur-3xl"></div>
                <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-slate-500/10 blur-3xl"></div>
                <svg class="absolute inset-0 w-full h-full opacity-5" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <div class="relative z-10 px-8 md:px-14 py-14 md:py-20 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-500/20 border border-blue-400/30 rounded-full text-blue-300 text-xs font-bold tracking-wider uppercase mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                    Официальный дилер
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
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold bg-white/20 px-2.5 py-1 rounded-lg self-start mb-3">Более 70 позиций</span>
                    <h3 class="font-black text-base leading-snug mb-3">Такелажное оборудование</h3>
                    <ul class="mt-auto space-y-1">
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Зажимы для каната</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Талрепы</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Стропы</li>
                    </ul>
                    <div class="mt-4 flex items-center gap-1 text-xs font-bold text-white/80 group-hover:text-white transition">
                        Перейти в раздел
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </Link>

                <Link href="/catalog/lifting-equipment" class="bg-gradient-to-br from-blue-800 to-blue-700 rounded-2xl p-6 text-white hover:scale-[1.02] transition-transform duration-200 flex flex-col group">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold bg-white/20 px-2.5 py-1 rounded-lg self-start mb-3">Для производства</span>
                    <h3 class="font-black text-base leading-snug mb-3">Грузоподъёмное оборудование</h3>
                    <ul class="mt-auto space-y-1">
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Тали электрические</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Тали ручные</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Лебёдки, Краны</li>
                    </ul>
                    <div class="mt-4 flex items-center gap-1 text-xs font-bold text-white/80 group-hover:text-white transition">
                        Перейти в раздел
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </Link>

                <Link href="/catalog/hydraulic" class="bg-gradient-to-br from-zinc-700 to-zinc-600 rounded-2xl p-6 text-white hover:scale-[1.02] transition-transform duration-200 flex flex-col group">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold bg-white/20 px-2.5 py-1 rounded-lg self-start mb-3">TOR и другие</span>
                    <h3 class="font-black text-base leading-snug mb-3">Гидравлическое оборудование</h3>
                    <ul class="mt-auto space-y-1">
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Домкраты бутылочные</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Подкатные домкраты</li>
                    </ul>
                    <div class="mt-4 flex items-center gap-1 text-xs font-bold text-white/80 group-hover:text-white transition">
                        Перейти в раздел
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </div>
                </Link>

                <Link href="/catalog/warehouse" class="bg-gradient-to-br from-stone-700 to-stone-600 rounded-2xl p-6 text-white hover:scale-[1.02] transition-transform duration-200 flex flex-col group">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                        </svg>
                    </div>
                    <span class="text-[11px] font-bold bg-white/20 px-2.5 py-1 rounded-lg self-start mb-3">Ручные и электрические</span>
                    <h3 class="font-black text-base leading-snug mb-3">Складское оборудование</h3>
                    <ul class="mt-auto space-y-1">
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Тележки для талей</li>
                        <li class="text-xs text-white/70 flex items-center gap-1.5"><span class="w-1 h-1 rounded-full bg-white/50 flex-shrink-0"></span>Складские тележки</li>
                    </ul>
                    <div class="mt-4 flex items-center gap-1 text-xs font-bold text-white/80 group-hover:text-white transition">
                        Перейти в раздел
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
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
                    Смотреть все
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </Link>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Link href="/catalog/clamps" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md hover:-translate-y-0.5 transition-all group flex items-center gap-5">
                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                        </svg>
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
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                        </svg>
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

        <!-- Преимущества -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 md:p-10">
            <h2 class="text-2xl font-black text-gray-900 mb-8 text-center">Почему выбирают нас</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        </svg>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">Официальный дилер</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Прямые поставки от производителей. Вся продукция сертифицирована и соответствует ГОСТ.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                        </svg>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">Быстрая доставка</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Отправка в день заказа. Доставляем по всей России транспортными компаниями.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">Документы</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Паспорта, сертификаты, счета-фактуры для юридических лиц. Работаем по договору.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                        </svg>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">Гарантия и сервис</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Гарантия от 12 месяцев на всё оборудование. Консультации технических специалистов.</p>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>