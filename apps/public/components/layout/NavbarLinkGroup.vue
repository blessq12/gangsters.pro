<script setup>
import { useRoute } from "vue-router";
import { useAppDesign } from "../../design/useAppDesign";

/** @typedef {{ routeName: string; label: string }} NavLinkLike */

defineProps({
    /** @type {{ routeName: string; label: string }[]} */
    links: {
        type: Array,
        required: true,
    },
    /** Классы на элементе `<nav>` */
    navClass: {
        type: String,
        required: true,
    },
});

const route = useRoute();
const navbar = useAppDesign().components.navbar;

function isActive(name) {
    return route.name === name;
}
</script>

<template>
    <nav :class="navClass">
        <RouterLink
            v-for="item in links"
            :key="item.routeName"
            :to="{ name: item.routeName }"
            :class="[
                navbar.shared.linkTransition,
                isActive(item.routeName)
                    ? navbar.shared.linkActive
                    : navbar.shared.linkInactive,
            ]"
        >
            {{ item.label }}
        </RouterLink>
    </nav>
</template>
