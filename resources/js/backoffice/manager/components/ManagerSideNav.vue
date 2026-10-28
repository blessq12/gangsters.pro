<script setup>
import { RouterLink } from "vue-router";

const links = [
    { name: "manager-order", label: "Order" },
    { name: "manager-catalog", label: "Catalog" },
    { name: "manager-crm", label: "Crm" },
    { name: "manager-production", label: "Production" },
    { name: "manager-delivery", label: "Delivery" },
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
                class="rounded px-3 py-2 text-sm text-neutral-700 hover:bg-neutral-200"
                active-class="bg-neutral-200 font-medium text-neutral-900"
            >
                {{ link.label }}
            </RouterLink>
        </nav>
    </aside>
</template>
