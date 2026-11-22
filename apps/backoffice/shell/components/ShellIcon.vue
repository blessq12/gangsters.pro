<script setup>
import { computed } from "vue";

const props = defineProps({
    name: { type: String, default: "" },
});

/** Реестр иконок: ключ из RoleNavItem.icon → path (viewBox 24×24, stroke). */
const icons = {
    dashboard: "M3 3h7v9H3z M14 3h7v5h-7z M14 12h7v9h-7z M3 16h7v5H3z",
    orders: "M5 3h14v18l-3-2-2 2-2-2-2 2-2-2-3 2z M9 8h6 M9 12h6 M9 16h3",
    catalog: "M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5z M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5",
    menu: "M4 6h16 M4 12h16 M4 18h10 M18 17l2 2 3-4",
    company: "M4 21V5l8-3v19 M12 8h8v13 M2 21h20 M8 9h.01 M8 13h.01 M8 17h.01 M16 12h.01 M16 16h.01",
    delivery: "M2 5h12v11H2z M14 9h4l4 4v3h-8 M5 18.5a2 2 0 1 0 4 0a2 2 0 1 0-4 0 M15 18.5a2 2 0 1 0 4 0a2 2 0 1 0-4 0",
    promotion: "M3 12V3h9l9 9-9 9z M7.5 7.5h.01",
    marketing: "M3 10v4h4l7 5V5L7 10z M18 9a4 4 0 0 1 0 6 M7 14l1.5 6h3L10 15",
    staff: "M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M5 7a4 4 0 1 0 8 0a4 4 0 1 0-8 0 M22 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75",
    clients: "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2 M8 7a4 4 0 1 0 8 0a4 4 0 1 0-8 0",
    warehouse: "M21 8l-9-5-9 5v8l9 5 9-5z M3 8l9 5 9-5 M12 13v8",
    finance: "M3 7a2 2 0 0 1 2-2h13v4 M3 7v12a2 2 0 0 0 2 2h15V9H5a2 2 0 0 1-2-2z M16 15h.01",
    live: "M13 2L4 14h7l-1 8 9-12h-7z",
    "manual-order": "M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z M14 3v6h6 M12 12v6 M9 15h6",
    logistics: "M3 18a3 3 0 1 0 6 0a3 3 0 1 0-6 0 M15 6a3 3 0 1 0 6 0a3 3 0 1 0-6 0 M9 18h6.5a3.5 3.5 0 0 0 0-7h-7a3.5 3.5 0 0 1 0-7H15",
    production: "M12 22c4 0 7-3 7-7 0-4-3-6-4-10-2 2-3 4-3 6-1-1-2-2-2-4-3 3-5 5-5 8 0 4 3 7 7 7z",
    queue: "M8 6h13 M8 12h13 M8 18h13 M3 6h.01 M3 12h.01 M3 18h.01",
    edit: "M12 20h9 M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z",
    trash: "M3 6h18 M8 6V4h8v2 M19 6l-1 14H6L5 6 M10 11v6 M14 11v6",
    check: "M20 6L9 17l-5-5",
    undo: "M3 7v6h6 M3 13a9 9 0 1 0 2.6-6.4",
    external: "M14 3h7v7 M10 14L21 3 M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5",
    dot: "M10 12a2 2 0 1 0 4 0a2 2 0 1 0-4 0",
};

const iconPath = computed(() => icons[props.name] ?? icons.dot);
</script>

<template>
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.6"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <path :d="iconPath" />
    </svg>
</template>
