<script setup>
defineProps({
    sections: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    emptyMessage: {
        type: String,
        default: "Тут пока тихо. Выберите другую категорию.",
    },
    cardsPerRow: {
        type: Number,
        default: 4,
    },
});

const emit = defineEmits(["productImageClick"]);
</script>

<template>
    <CatalogProductsBase
        :sections="sections"
        :loading="loading"
        :empty-message="emptyMessage"
        :cards-per-row="cardsPerRow"
        variant="desktop"
        @product-image-click="emit('productImageClick', $event)"
    />
</template>

<style scoped></style>

