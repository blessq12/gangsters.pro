<script setup>
import { useAppDesign } from "../../design/useAppDesign";

defineProps({
    viewMode: {
        type: String,
        default: "grid", // grid | horizontal
    },
});

const emit = defineEmits(["update:viewMode"]);

const d = useAppDesign().components.catalog.viewControls.mobile;
</script>

<template>
    <div :class="d.wrapper">
        <button
            type="button"
            :class="[d.btn, viewMode === 'grid' ? d.btnActive : d.btnInactive]"
            @click="emit('update:viewMode', 'grid')"
        >
            Сетка
        </button>
        <button
            type="button"
            :class="[d.btn, viewMode === 'horizontal' ? d.btnActive : d.btnInactive]"
            @click="emit('update:viewMode', 'horizontal')"
        >
            Горизонтально
        </button>
    </div>
</template>

<style scoped></style>
