<script setup>
import DockChrome from "./DockChrome.vue";
import { dockItems } from "../../dock/dockRegistry";
</script>

<template>
    <DockChrome
        variant="mobile"
        :dock-items="dockItems"
    />
</template>
