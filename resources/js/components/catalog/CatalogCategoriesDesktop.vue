<script setup>
defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: [Number, String, null],
        default: null,
    },
    allLabel: {
        type: String,
        default: "Все",
    },
    showAll: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["update:modelValue", "change"]);
</script>

<template>
    <CatalogCategoriesBase
        :categories="categories"
        :model-value="modelValue"
        :all-label="allLabel"
        :show-all="showAll"
        variant="desktop"
        @update:model-value="emit('update:modelValue', $event)"
        @change="emit('change', $event)"
    />
</template>

<style scoped></style>

