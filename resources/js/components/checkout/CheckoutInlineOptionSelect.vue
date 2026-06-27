<script setup>
import { useAppDesign } from "../../design/useAppDesign";

defineProps({
    /** @type {readonly { id: string, label: string }[]} */
    options: {
        type: Array,
        required: true,
    },
    selectedId: {
        type: String,
        default: null,
    },
    ariaLabel: {
        type: String,
        required: true,
    },
});

defineEmits(["select"]);

const i = useAppDesign().components.checkout.inlineOption;
</script>

<template>
    <div
        :class="i.group"
        role="group"
        :aria-label="ariaLabel"
    >
        <button
            v-for="option in options"
            :key="option.id"
            type="button"
            :class="[
                i.btn,
                selectedId === option.id ? i.btnSelected : i.btnIdle,
            ]"
            :aria-pressed="selectedId === option.id"
            @click="$emit('select', option.id)"
        >
            {{ option.label }}
        </button>
    </div>
</template>
