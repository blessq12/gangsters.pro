<script setup>
import { computed } from "vue";
import UiButton from "./UiButton.vue";

const props = defineProps({
    page: { type: Number, required: true },
    perPage: { type: Number, required: true },
    total: { type: Number, required: true },
});

const emit = defineEmits(["update:page"]);

const totalPages = computed(() => Math.max(1, Math.ceil(props.total / props.perPage)));

function go(next) {
    const page = Math.min(totalPages.value, Math.max(1, next));
    if (page !== props.page) {
        emit("update:page", page);
    }
}
</script>

<template>
    <div class="flex items-center justify-between gap-4 pt-2 text-sm text-bo-muted">
        <span>Стр. {{ page }} / {{ totalPages }} · всего {{ total }}</span>
        <div class="flex gap-2">
            <UiButton :disabled="page <= 1" @click="go(page - 1)">Назад</UiButton>
            <UiButton :disabled="page >= totalPages" @click="go(page + 1)">Вперёд</UiButton>
        </div>
    </div>
</template>
