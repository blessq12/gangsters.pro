<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    /** @type {'responsive' | 'mobile'} */
    variant: {
        type: String,
        required: true,
    },
});

defineEmits(["click"]);

const navbar = useAppDesign().components.navbar;

const baseClass = computed(() =>
    props.variant === "responsive"
        ? navbar.responsive.burgerButton
        : navbar.mobile.burgerButton,
);

const toggleClass = computed(() =>
    props.open
        ? navbar.shared.burgerOpen
        : navbar.shared.burgerClosedHover,
);

const mdiIconClass = navbar.shared.mdiIcon;
const iconGlyph = computed(() =>
    props.open ? "mdi-close" : "mdi-menu",
);
</script>

<template>
    <button
        type="button"
        :class="[baseClass, toggleClass]"
        @click="$emit('click')"
    >
        <i
            :class="[mdiIconClass, iconGlyph]"
        />
    </button>
</template>
