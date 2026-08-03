<script setup>
import { computed, onUnmounted, provide } from "vue";
import { appDesign } from "./design/app.design";
import { AppDesignInjectionKey } from "./design/injectionKeys";
import { useUiStore } from "./modules/shell/store/uiStore";
import MainLayoutMobile from "./layouts/MainLayoutMobile.vue";
import MainLayoutDesktop from "./layouts/MainLayoutDesktop.vue";
import { useAppBootstrap } from "./modules/shell/application/useAppBootstrap";
import ClosedForOrdersModal from "./components/company/ClosedForOrdersModal.vue";
import CatalogSearchLayer from "./components/catalog/CatalogSearchLayer.vue";

const uiStore = useUiStore();
const appBootstrap = useAppBootstrap();

provide(AppDesignInjectionKey, appDesign);

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
    <ClosedForOrdersModal />
    <CatalogSearchLayer />
</template>

<style scoped>
</style>
