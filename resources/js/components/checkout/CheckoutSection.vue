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
    if (props.variant === "form" || props.variant === "muted" || props.variant === "inset") {
        return "inset";
    }
    return "default";
});

const sectionClass = computed(() =>
    normalizedVariant.value === "inset" ? s.sectionInset : "space-y-2",
);

const titleClass = computed(() =>
    normalizedVariant.value === "inset" ? s.headingCardMuted : s.headingSm,
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
