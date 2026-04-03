<script setup>
import { computed } from "vue";
import { useUiStore } from "./stores/uiStore";
import MainLayoutMobile from "./layouts/MainLayoutMobile.vue";
import MainLayoutDesktop from "./layouts/MainLayoutDesktop.vue";

const uiStore = useUiStore();

if (typeof window !== "undefined") {
    uiStore.initDeviceMode();
}

const layoutComponent = computed(() =>
    uiStore.deviceMode === "desktop" ? MainLayoutDesktop : MainLayoutMobile,
);
</script>

<template>
    <component :is="layoutComponent" />
    <ScrollToTopButton />
</template>

<style scoped>
</style>
