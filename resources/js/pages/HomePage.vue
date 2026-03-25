<script setup>
import { computed } from "vue";
import { useUiStore } from "../stores/uiStore";
import HomePageMobile from "./HomePageMobile.vue";
import HomePageDesktop from "./HomePageDesktop.vue";

const uiStore = useUiStore();

const component = computed(() =>
    uiStore.deviceMode === "desktop" ? HomePageDesktop : HomePageMobile,
);
</script>

<template>
    <component :is="component" />
</template>

<style scoped></style>
