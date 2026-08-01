<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    title: {
        type: String,
        default: "",
    },
    /** default — заголовок + контент; inset — поля в рамке */
    variant: {
        type: String,
        default: "default",
    },
});

const s = useAppDesign().components.checkout.shared;

const normalizedVariant = computed(() => {
    if (props.variant === "form") {
        return "form";
    }
    if (props.variant === "muted" || props.variant === "inset") {
        return "inset";
    }
    return "default";
});

const sectionClass = computed(() => {
    if (normalizedVariant.value === "form") {
        return s.sectionForm;
    }
    if (normalizedVariant.value === "inset") {
        return s.sectionInset;
    }
    return "space-y-2";
});

const titleClass = computed(() =>
    normalizedVariant.value === "default" ? s.headingSm : s.headingCardMuted,
);
</script>

<template>
    <section :class="sectionClass">
        <p
            v-if="title"
            :class="titleClass"
        >
            {{ title }}
        </p>
        <slot />
    </section>
</template>
