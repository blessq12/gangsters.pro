<script setup>
import { RouterLink } from "vue-router";

const roles = [
    { path: "/courier", label: "Курьер" },
    { path: "/production", label: "Кухня" },
    { path: "/owner", label: "Владелец" },
    { path: "/administrator", label: "Администратор" },
];
</script>

<template>
    <header
        class="flex h-12 shrink-0 items-center gap-4 border-b border-neutral-200 bg-white px-4"
    >
        <span class="text-sm font-medium text-neutral-900">Backoffice</span>
        <nav class="flex items-center gap-1" aria-label="Роли">
            <RouterLink
                v-for="role in roles"
                :key="role.path"
                :to="role.path"
                class="inline-flex h-9 items-center rounded px-3 text-sm text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900"
                active-class="bg-neutral-200 font-medium text-neutral-900"
            >
                {{ role.label }}
            </RouterLink>
        </nav>
    </header>
</template>
