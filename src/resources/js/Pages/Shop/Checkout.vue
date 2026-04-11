<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    items: Array,
    total: Number,
})

const form = useForm({
    city: '',
    street: '',
    house: '',
    comment: '',
    phone: '',
})

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price)
}

function submit() {
    form.post('/checkout')
}

// Маска телефона
function phoneInput(e) {
    let value = e.target.value.replace(/\D/g, '')
    let formatted = '+'
    if (value.length > 0) {
        formatted += value.substring(0, 1)
        if (value.length > 1) formatted += ' (' + value.substring(1, 4)
        if (value.length > 4) formatted += ') ' + value.substring(4, 7)
        if (value.length > 7) formatted += '-' + value.substring(7, 9)
        if (value.length > 9) formatted += '-' + value.substring(9, 11)
    }
    form.phone = formatted
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Оформление заказа</h1>

            <!-- Состав заказа -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                <h2 class="font-bold mb-4 text-gray-700">Ваш заказ</h2>
                <div class="flex flex-col gap-3">
                    <div v-for="item in items" :key="item.id" class="flex items-center gap-3">
                        <img :src="item.product?.image ? `/storage/${item.product.image}` : 'https://placehold.co/50?text=?'" class="w-12 h-12 object-contain rounded-lg bg-gray-50 p-1" />
                        <div class="flex-grow">
                            <div class="font-medium text-sm">{{ item.product?.title }}</div>
                            <div class="text-gray-400 text-xs">{{ item.quantity }} шт.</div>
                        </div>
                        <div class="font-bold text-sm">
                            {{ formatPrice((item.product?.price_with_discount ?? 0) * item.quantity) }} ₽
                        </div>
                    </div>
                </div>

                <div class="border-t mt-4 pt-4 flex justify-between items-center">
                    <span class="text-gray-500">Итого:</span>
                    <span class="text-2xl font-bold text-blue-600">{{ formatPrice(total) }} ₽</span>
                </div>
            </div>

            <!-- Форма -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="font-bold mb-4 text-gray-700">Данные доставки</h2>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-sm text-gray-500 mb-1">Город</label>
                        <input v-model="form.city" type="text" placeholder="Москва" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"/>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-500 mb-1">Улица</label>
                        <input v-model="form.street" type="text" placeholder="Ленина" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"/>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-500 mb-1">Дом, квартира</label>
                        <input v-model="form.house" type="text" placeholder="1, кв. 23" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500"/>
                    </div>
                    <div class="col-span-2">
                         <label class="block text-sm text-gray-500 mb-1">Комментарий (необязательно)</label>
                        <textarea v-model="form.comment" rows="2" placeholder="Код домофона, подъезд..." class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm text-gray-500 mb-1">Телефон</label>
                    <input type="tel" :value="form.phone" @input="phoneInput" placeholder="+7 (999) 000-00-00" maxlength="18" class="w-full px-4 py-2.5 bg-gray-50 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500" />
                </div>

                <button @click="submit" :disabled="form.processing" class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition">
                    Подтвердить заказ
                </button>
            </div>
        </div>
    </ShopLayout>
</template>