<script setup>
import { computed } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { useAuthStore } from "../store/authStore";

const auth = useAuthStore();
const router = useRouter();

const allRoles = [
    { path: "/courier", label: "Курьер", role: "courier" },
    { path: "/production", label: "Кухня", role: "kitchen" },
    { path: "/owner", label: "Владелец", role: "owner" },
    { path: "/administrator", label: "Администратор", role: "administrator" },
];

const roles = computed(() => {
    if (!auth.authRequired) {
        return allRoles;
    }
    if (!auth.role) {
        return [];
    }
    return allRoles.filter((item) => item.role === auth.role);
});

async function logout() {
    await auth.logout();
    await router.push({ name: "backoffice-login" });
}
</script>

<template>
    <header
        class="flex h-12 shrink-0 items-center gap-4 border-b border-neutral-200 bg-white px-4"
    >
        <span class="text-sm font-medium text-neutral-900">Backoffice</span>
        <nav
            v-if="roles.length > 0"
            class="flex items-center gap-1"
            aria-label="Роли"
        >
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
        <div class="ml-auto flex items-center gap-3 text-sm text-neutral-600">
            <span v-if="auth.employee">{{ auth.employee.name }}</span>
            <button
                v-if="auth.authRequired || auth.employee"
                type="button"
                class="underline"
                @click="logout"
            >
                Выйти
            </button>
        </div>
    </header>
</template>
