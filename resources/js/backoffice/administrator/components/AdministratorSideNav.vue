<script setup>
import { RouterLink } from "vue-router";

/** Пункты = Domain BC (без Shared). */
const links = [
    { name: "administrator-catalog", label: "Catalog" },
    { name: "administrator-content", label: "Content" },
    { name: "administrator-crm", label: "Crm" },
    { name: "administrator-delivery", label: "Delivery" },
    { name: "administrator-order", label: "Order" },
    { name: "administrator-production", label: "Production" },
    { name: "administrator-staff", label: "Staff" },
    { name: "administrator-warehouse", label: "Warehouse" },
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
