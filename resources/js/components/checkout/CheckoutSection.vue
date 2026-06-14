<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    title: {
        type: String,
        default: "",
    },
    /** plain — заголовок; form — карточка с полями; muted — блок сводки */
    variant: {
        type: String,
        default: "plain",
    },
});

const s = useAppDesign().components.checkout.shared;
const cf = useAppDesign().components.checkout.confirm;

const sectionClass = computed(() => {
    switch (props.variant) {
        case "form":
            return s.guestIsland;
        case "muted":
            return cf.blockMuted;
        default:
            return "space-y-2";
    }
});

const titleClass = computed(() => {
    if (props.variant === "muted") {
        return s.headingCardMuted;
    }
    return s.headingSm;
});
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
