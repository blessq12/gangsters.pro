<script setup>
import DockChrome from "./dock/DockChrome.vue";
import { dockItems } from "./dock/dockKit";
</script>

<template>
    <DockChrome :dock-items="dockItems" />
</template>
