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
    mobileCardViewMode: {
        type: String,
        default: "grid",
    },
});

const emit = defineEmits(["productImageClick"]);
</script>

<template>
    <CatalogProductsBase
        :sections="sections"
        :loading="loading"
        :empty-message="emptyMessage"
        :mobile-card-view-mode="mobileCardViewMode"
        variant="mobile"
        @product-image-click="emit('productImageClick', $event)"
    />
</template>

<style scoped></style>

