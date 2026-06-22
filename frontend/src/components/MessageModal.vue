<script setup>
defineProps({
    show: Boolean,
    message: String,
    messageType: String
})
defineEmits(["close"])
</script>
<template>
    <dialog v-if="show" class="modal modal-open" @click.self="$emit('close')">
        <div class="modal-box rounded-xl shadow-lg">
            <p class="py-4 text-center whitespace-pre-line aa">{{ message }}</p>
        </div>
    </dialog>
</template>