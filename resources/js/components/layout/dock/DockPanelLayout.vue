<script setup>
import { computed, useSlots } from "vue";
import { useAppDesign } from "../../../design/useAppDesign";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: "",
    },
    bodyClass: {
        type: String,
        default: "",
    },
});

const slots = useSlots();
const layout = useAppDesign().components.dockPanels.shared.layout;

const bodyClasses = computed(() =>
    [layout.body, props.bodyClass].filter(Boolean),
);

const hasDescriptionSlot = computed(() => Boolean(slots.description));
const hasDescriptionProp = computed(() => props.description.trim().length > 0);
const showDescription = computed(
    () => hasDescriptionSlot.value || hasDescriptionProp.value,
);
const hasFooter = computed(() => Boolean(slots.footer));
</script>

<template>
    <div :class="layout.root">
        <header :class="layout.header">
            <div :class="layout.headerRow">
                <h2 :class="layout.title">
                    {{ title }}
                </h2>
                <div
                    v-if="$slots.headerActions"
                    class="shrink-0"
                >
                    <slot name="headerActions" />
                </div>
            </div>

            <div
                v-if="showDescription"
                :class="layout.description"
            >
                <slot
                    v-if="hasDescriptionSlot"
                    name="description"
                />
                <p v-else>
                    {{ description }}
                </p>
            </div>
        </header>

        <div :class="bodyClasses">
            <slot />
        </div>

        <footer
            v-if="hasFooter"
            :class="layout.footer"
        >
            <slot name="footer" />
        </footer>
    </div>
</template>
