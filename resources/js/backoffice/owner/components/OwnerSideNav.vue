<script setup>
import { RouterLink } from "vue-router";

const groups = [
    {
        label: "Витрина",
        links: [
            { name: "owner-catalog", label: "Каталог" },
            { name: "owner-marketing", label: "Маркетинг" },
        ],
    },
    {
        label: "Сервис",
        links: [
            { name: "owner-delivery", label: "Доставка" },
            { name: "owner-promotion-policy", label: "Правила акций" },
        ],
    },
    {
        label: "Продажи",
        links: [
            { name: "owner-order", label: "Заказы" },
            { name: "owner-clients", label: "Клиенты" },
        ],
    },
    {
        label: "Компания",
        links: [
            { name: "owner-company", label: "Компания" },
            { name: "owner-documents", label: "Документы" },
            { name: "owner-operators", label: "Операторы" },
        ],
    },
];
</script>

<template>
    <aside
        class="flex w-56 shrink-0 flex-col border-r border-neutral-200 bg-neutral-50"
        aria-label="Контексты"
    >
        <nav class="flex flex-col gap-4 p-3">
            <div
                v-for="group in groups"
                :key="group.label"
                class="flex flex-col gap-1"
            >
                <p class="px-3 text-xs text-neutral-500">{{ group.label }}</p>
                <RouterLink
                    v-for="link in group.links"
                    :key="link.name"
                    :to="{ name: link.name }"
                    class="flex h-9 items-center rounded px-3 text-sm text-neutral-700 hover:bg-neutral-200"
                    active-class="bg-neutral-200 font-medium text-neutral-900"
                >
                    {{ link.label }}
                </RouterLink>
            </div>
        </nav>
    </aside>
</template>
