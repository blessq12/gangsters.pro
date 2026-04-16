<script setup>
import { computed, onUnmounted } from "vue";
import { useUiStore } from "./stores/uiStore";
import MainLayoutMobile from "./layouts/MainLayoutMobile.vue";
import MainLayoutDesktop from "./layouts/MainLayoutDesktop.vue";
import { useAppBootstrap } from "./processes/bootstrap/useAppBootstrap";

const uiStore = useUiStore();
const appBootstrap = useAppBootstrap();

if (typeof window !== "undefined") {
    uiStore.initDeviceMode();
}

const layoutComponent = computed(() =>
    uiStore.deviceMode === "desktop" ? MainLayoutDesktop : MainLayoutMobile,
);

onUnmounted(() => {
    appBootstrap.dispose();
});
</script>

<template>
    <component :is="layoutComponent" />
    <ScrollToTopButton />
</template>

<style scoped>
</style>
