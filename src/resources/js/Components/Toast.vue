<script setup>
import { ref } from 'vue'

const toasts = ref([])

function add(message, type = 'success') {
    const id = Date.now()
    toasts.value.push({ id, message, type })
    setTimeout(() => {
        toasts.value = toasts.value.filter(t => t.id !== id)
    }, 3000)
}

defineExpose({ add })
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-2">
        <TransitionGroup name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'px-4 py-3 rounded-xl shadow-lg text-white font-medium text-sm flex items-center gap-2',
                    toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'
                ]"
            >
                <span>{{ toast.type === 'success' ? '✓' : '✕' }}</span>
                {{ toast.message }}
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { opacity: 0; transform: translateY(20px); }
.toast-leave-to { opacity: 0; transform: translateX(100%); }
</style>