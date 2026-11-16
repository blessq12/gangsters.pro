<script setup>
import { useAppDesign } from "../../design/useAppDesign";

defineProps({
    modelValue: {
        type: Boolean,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    /** @type {{ useHtml: boolean; html?: string; empty?: boolean }} */
    doc: {
        type: Object,
        required: true,
    },
});

defineEmits(["update:modelValue"]);

const footer = useAppDesign().components.footer;
</script>

<template>
    <BaseModal
        :model-value="modelValue"
        size="lg"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <template #header>{{ title }}</template>
        <div
            v-if="doc.useHtml"
            :class="footer.legalHtml"
            v-html="doc.html"
        />
        <p
            v-else
            :class="footer.modalFallback"
        >
            Документ временно недоступен
        </p>
    </BaseModal>
</template>
