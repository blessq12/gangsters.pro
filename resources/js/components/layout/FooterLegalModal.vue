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
    /** @type {{ useHtml: boolean; html?: string; paragraphs?: string[] }} */
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
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <template #header>{{ title }}</template>
        <div
            v-if="doc.useHtml"
            :class="footer.legalHtml"
            v-html="doc.html"
        />
        <div
            v-else
            :class="footer.modalFallback"
        >
            <p
                v-for="(para, i) in doc.paragraphs"
                :key="i"
            >
                {{ para }}
            </p>
        </div>
    </BaseModal>
</template>
