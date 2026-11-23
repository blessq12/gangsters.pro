<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../../../shell/store/authStore.js";
import { useSessionStore } from "../../../shell/store/sessionStore.js";

const router = useRouter();
const auth = useAuthStore();
const session = useSessionStore();

const workerName = computed(() => String(auth.employee?.name ?? "").trim() || "Кухня");

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
                    {{ workerName }}
                </p>
                <p class="truncate text-xs text-bo-muted">
                    Кухня
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

        <div class="min-h-0 flex-1 overflow-auto p-6 text-2xl leading-relaxed md:text-3xl">
            <RouterView />
        </div>
    </div>
</template>
