<script setup>
import { computed } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../../../shell/store/authStore.js";
import { useSessionStore } from "../../../shell/store/sessionStore.js";

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const session = useSessionStore();

const tabs = [
    {
        name: "courier-deliveries",
        label: "Листы",
        match: ["courier-deliveries", "courier-route-sheet"],
    },
];

const courierName = computed(() => String(auth.employee?.name ?? "").trim() || "Курьер");

async function logout() {
    await auth.logout();
    session.clear();
    await router.push({ name: "backoffice-login" });
}
</script>

<template>
    <div class="flex h-full min-h-0 flex-1 flex-col bg-bo-canvas text-bo-fg">
        <header
            class="flex shrink-0 items-center justify-between gap-3 border-b border-bo-border bg-bo-surface px-4 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3"
        >
            <div class="min-w-0">
                <p class="truncate text-base font-semibold text-bo-fg">
                    {{ courierName }}
                </p>
            </div>
            <button
                type="button"
                class="inline-flex h-11 shrink-0 items-center px-4 text-sm font-medium text-bo-muted transition-colors hover:bg-white/[0.06] hover:text-bo-fg"
                @click="logout"
            >
                Выход
            </button>
        </header>

        <div class="min-h-0 flex-1 overflow-auto pb-[calc(4.25rem+env(safe-area-inset-bottom))]">
            <RouterView />
        </div>

        <nav
            class="fixed inset-x-0 bottom-0 z-20 flex h-[calc(3.5rem+env(safe-area-inset-bottom))] items-stretch border-t border-bo-border bg-bo-surface pb-[env(safe-area-inset-bottom)]"
            aria-label="Вкладки курьера"
        >
            <RouterLink
                v-for="tab in tabs"
                :key="tab.name"
                :to="{ name: tab.name }"
                class="flex flex-1 items-center justify-center text-base font-medium"
                :class="(tab.match ? tab.match.includes(String(route.name)) : route.name === tab.name)
                    ? 'text-bo-accent'
                    : 'text-bo-muted'"
            >
                {{ tab.label }}
            </RouterLink>
        </nav>
    </div>
</template>
