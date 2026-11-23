<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../store/authStore";
import { useSessionStore } from "../store/sessionStore";

const props = defineProps({
    collapsed: { type: Boolean, default: false },
    isDesktop: { type: Boolean, default: true },
    mobileOpen: { type: Boolean, default: false },
});

const emit = defineEmits(["toggle-sidebar"]);

const auth = useAuthStore();
const session = useSessionStore();
const router = useRouter();

const roleLabels = {
    owner: "Владелец",
    administrator: "Администратор",
    kitchen: "Кухня",
    courier: "Курьер",
};

const roleLabel = computed(() => roleLabels[auth.role] ?? "");

const userInitial = computed(() => String(auth.employee?.name ?? "").trim().charAt(0).toUpperCase());

const toggleLabel = computed(() => {
    if (props.isDesktop) {
        return props.collapsed ? "Развернуть меню" : "Свернуть меню";
    }
    return props.mobileOpen ? "Закрыть меню" : "Открыть меню";
});

async function logout() {
    await auth.logout();
    session.clear();
    await router.push({ name: "backoffice-login" });
}
</script>

<template>
    <header
        class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-4 border-b border-bo-border bg-bo-canvas/80 px-4 backdrop-blur-md sm:px-6 lg:px-10"
    >
        <button
            type="button"
            class="flex h-10 w-10 items-center justify-center text-bo-muted transition-colors hover:bg-white/[0.06] hover:text-bo-fg"
            :aria-label="toggleLabel"
            :title="toggleLabel"
            @click="emit('toggle-sidebar')"
        >
            <span class="flex flex-col gap-[5px]" aria-hidden="true">
                <span class="block h-px w-5 bg-current" />
                <span class="block h-px w-5 bg-current" />
                <span class="block h-px w-3.5 bg-current" />
            </span>
        </button>

        <div class="flex min-w-0 items-center gap-2.5 lg:hidden">
            <img
                src="/images/load_logo.svg"
                alt="Gangsters"
                class="h-8 w-auto shrink-0"
            >
            <img
                src="/images/logo_text.svg"
                alt=""
                class="hidden h-4 w-auto min-w-0 min-[400px]:block"
            >
        </div>

        <div class="ml-auto flex min-w-0 items-center gap-4">
            <div v-if="auth.employee" class="flex min-w-0 items-center gap-3">
                <div class="hidden min-w-0 text-right sm:block">
                    <p class="truncate text-sm font-medium text-bo-fg">{{ auth.employee.name }}</p>
                    <p v-if="roleLabel" class="truncate text-xs text-bo-muted">{{ roleLabel }}</p>
                </div>
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center bg-bo-elevated text-sm font-semibold text-bo-fg"
                    aria-hidden="true"
                >
                    {{ userInitial }}
                </span>
            </div>
            <span class="hidden h-6 w-px bg-bo-border sm:block" aria-hidden="true" />
            <button
                type="button"
                class="inline-flex h-10 items-center px-4 text-sm font-medium text-bo-muted transition-colors hover:bg-bo-accent-soft hover:text-bo-fg"
                @click="logout"
            >
                Выход
            </button>
        </div>
    </header>
</template>
