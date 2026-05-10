<script setup>
import DockChrome from "./dock/DockChrome.vue";
import { dockItems } from "./dock/dockRegistry";
</script>

<template>
    <DockChrome
        variant="desktop"
        :dock-items="dockItems"
    />
</template>
