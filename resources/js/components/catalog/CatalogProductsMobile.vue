<script setup>
defineProps({
    products: {
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
});

const emit = defineEmits(["productImageClick"]);
</script>

<template>
    <CatalogProductsBase
        :products="products"
        :loading="loading"
        :empty-message="emptyMessage"
        variant="mobile"
        @product-image-click="emit('productImageClick', $event)"
    />
</template>

<style scoped></style>

