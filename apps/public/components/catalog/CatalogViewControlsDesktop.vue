<script setup>
import { useAppDesign } from "../../design/useAppDesign";

defineProps({
    cardsPerRow: {
        type: Number,
        default: 4,
    },
});

const emit = defineEmits(["update:cardsPerRow"]);

const d = useAppDesign().components.catalog.viewControls.desktop;
</script>

<template>
    <div :class="d.wrapper">
        <span :class="d.label">Карточек:</span>
        <button
            type="button"
            :class="[d.btn, cardsPerRow === 3 ? d.btnActive : d.btnInactive]"
            @click="emit('update:cardsPerRow', 3)"
        >
            3
        </button>
        <button
            type="button"
            :class="[d.btn, cardsPerRow === 4 ? d.btnActive : d.btnInactive]"
            @click="emit('update:cardsPerRow', 4)"
        >
            4
        </button>
    </div>
</template>

<style scoped></style>
