<script setup>
import UiButton from "./UiButton.vue";
import UiModal from "./UiModal.vue";

defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: "Подтверждение" },
    message: { type: String, default: "" },
});

const emit = defineEmits(["confirm", "cancel"]);
</script>

<template>
    <UiModal :open="open" :title="title" @close="emit('cancel')">
        <p class="text-sm text-bo-fg">
            {{ message }}
        </p>
        <template #footer>
            <UiButton @click="emit('cancel')">Отмена</UiButton>
            <UiButton @click="emit('confirm')">Подтвердить</UiButton>
        </template>
    </UiModal>
</template>
