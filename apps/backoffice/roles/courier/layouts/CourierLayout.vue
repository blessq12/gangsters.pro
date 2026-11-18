<script setup>
import { RouterLink, useRoute } from "vue-router";

const route = useRoute();

const tabs = [
    { name: "courier-deliveries", label: "Доставки" },
];
</script>

<template>
    <div class="flex min-h-0 flex-1 flex-col">
        <div class="min-h-0 flex-1 overflow-auto pb-16">
            <RouterView />
        </div>
        <nav
            class="fixed inset-x-0 bottom-0 z-10 flex h-14 items-stretch border-t border-bo-border bg-bo-surface"
            aria-label="Вкладки курьера"
        >
            <RouterLink
                v-for="tab in tabs"
                :key="tab.name"
                :to="{ name: tab.name }"
                class="flex flex-1 items-center justify-center text-sm"
                :class="route.name === tab.name ? 'font-medium text-bo-accent' : 'text-bo-muted'"
            >
                {{ tab.label }}
            </RouterLink>
        </nav>
    </div>
</template>
