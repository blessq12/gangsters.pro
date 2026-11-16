<script setup>
import { RouterLink, useRoute } from "vue-router";

const route = useRoute();

const tabs = [
    { name: "courier-deliveries", label: "Deliveries" },
    { name: "courier-shift", label: "Shift" },
];
</script>

<template>
    <div class="flex min-h-0 flex-1 flex-col">
        <div class="min-h-0 flex-1 overflow-auto pb-16">
            <RouterView />
        </div>
        <nav
            class="fixed inset-x-0 bottom-0 z-10 flex h-14 items-stretch border-t border-slate-200 bg-white"
            aria-label="Courier tabs"
        >
            <RouterLink
                v-for="tab in tabs"
                :key="tab.name"
                :to="{ name: tab.name }"
                class="flex flex-1 items-center justify-center text-sm"
                :class="route.name === tab.name ? 'font-medium text-sky-700' : 'text-slate-500'"
            >
                {{ tab.label }}
            </RouterLink>
        </nav>
    </div>
</template>
