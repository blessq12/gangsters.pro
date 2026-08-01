<script setup>
import { useAppDesign } from "../../design/useAppDesign";

defineProps({
    /**
     * @type {readonly {
     *   id: string,
     *   label: string,
     *   icon?: string,
     * }[]}
     */
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

const m = useAppDesign().components.checkout.methodState;
</script>

<template>
    <div
        :class="m.shell"
        role="radiogroup"
        :aria-label="ariaLabel"
    >
        <button
            v-for="option in options"
            :key="option.id"
            type="button"
            role="radio"
            :aria-checked="selectedId === option.id"
            :class="[
                m.cell,
                m.cellDivider,
                selectedId === option.id ? m.cellSelected : m.cellIdle,
            ]"
            @click="$emit('select', option.id)"
        >
            <i
                v-if="option.icon"
                :class="[option.icon, m.icon]"
                aria-hidden="true"
            />
            <span :class="m.label">
                {{ option.label }}
            </span>
        </button>
    </div>
</template>
