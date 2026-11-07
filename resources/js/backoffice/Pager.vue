<script setup>
import { computed } from "vue";

const props = defineProps({
    page: {
        type: Number,
        required: true,
    },
    total: {
        type: Number,
        required: true,
    },
    perPage: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(["update:page"]);

const pages = computed(() => Math.max(1, Math.ceil(props.total / props.perPage)));
</script>

<template>
    <div
        v-if="pages > 1"
        class="flex items-center gap-2 text-sm"
    >
        <button
            type="button"
            class="rounded border border-neutral-300 bg-white px-3 py-1.5 disabled:opacity-40"
            :disabled="page <= 1"
            @click="emit('update:page', page - 1)"
        >
            Назад
        </button>
        <span>{{ page }} / {{ pages }}</span>
        <button
            type="button"
            class="rounded border border-neutral-300 bg-white px-3 py-1.5 disabled:opacity-40"
            :disabled="page >= pages"
            @click="emit('update:page', page + 1)"
        >
            Вперёд
        </button>
    </div>
</template>
