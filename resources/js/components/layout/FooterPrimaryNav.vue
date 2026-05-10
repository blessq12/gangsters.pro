<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { FOOTER_PRIMARY_NAV } from "../../design/layout/footer.present";

const footer = useAppDesign().components.footer;
</script>

<template>
    <div :class="footer.navLinks">
        <RouterLink
            v-for="item in FOOTER_PRIMARY_NAV"
            :key="item.routeName"
            :to="{ name: item.routeName }"
            :class="footer.navLink"
        >
            {{ item.label }}
        </RouterLink>
    </div>
</template>
