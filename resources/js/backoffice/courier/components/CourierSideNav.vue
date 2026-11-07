<script setup>
import { RouterLink } from "vue-router";

const links = [
    { name: "courier-delivery", label: "Листы" },
];
</script>

<template>
    <aside
        class="flex w-56 shrink-0 flex-col border-r border-neutral-200 bg-neutral-50"
        aria-label="Контексты"
    >
        <nav class="flex flex-col gap-1 p-3">
            <RouterLink
                v-for="link in links"
                :key="link.name"
                :to="{ name: link.name }"
                class="flex h-9 items-center rounded px-3 text-sm text-neutral-700 hover:bg-neutral-200"
                active-class="bg-neutral-200 font-medium text-neutral-900"
            >
                {{ link.label }}
            </RouterLink>
        </nav>
    </aside>
</template>
