<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    /** @type {'responsive' | 'desktop' | 'mobile'} */
    variant: {
        type: String,
        required: true,
    },
});

const navbar = useAppDesign().components.navbar;

const rowClass = computed(() => navbar[props.variant].logoRow);
const logoImgClass = computed(() => navbar[props.variant].logoImg);
</script>

<template>
    <div :class="rowClass">
        <RouterLink
            :to="{ name: 'home' }"
            :class="navbar.shared.logoLink"
        >
            <slot>
                <img
                    src="/images/logo.png"
                    alt="Gangsters"
                    :class="logoImgClass"
                />
            </slot>
        </RouterLink>
    </div>
</template>
