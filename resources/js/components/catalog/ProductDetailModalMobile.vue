<script setup>
defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
    <ProductDetailModalBase
        :model-value="modelValue"
        :product="product"
        variant="mobile"
        @update:model-value="emit('update:modelValue', $event)"
    />
</template>

<style scoped></style>
