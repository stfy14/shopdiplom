<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import ShopLayout from '@/Layouts/ShopLayout.vue'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-md mx-auto">
            <div class="bg-white rounded-2xl shadow-sm p-8">
                <h1 class="text-2xl font-bold mb-6 text-center">Вход</h1>

                <div class="mb-4">
                    <label class="block text-sm text-gray-500 font-medium mb-1">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        class="w-full px-4 py-3 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'ring-2 ring-red-400': form.errors.email }"
                    />
                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm text-gray-500 font-medium mb-1">Пароль</label>
                    <input
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        class="w-full px-4 py-3 bg-gray-50 rounded-xl border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'ring-2 ring-red-400': form.errors.password }"
                    />
                    <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                </div>

                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="w-full py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition disabled:opacity-50"
                >
                    {{ form.processing ? 'Входим...' : 'Войти' }}
                </button>

                <div class="text-center mt-4 text-sm text-gray-400">
                    Нет аккаунта?
                    <Link href="/register" class="text-blue-600 hover:underline">Зарегистрироваться</Link>
                </div>
            </div>
        </div>
    </ShopLayout>
</template>