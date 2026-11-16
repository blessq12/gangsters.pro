<script setup>
import { useAuthStore } from "../store/authStore";

const auth = useAuthStore();
</script>

<template>
    <section class="mx-auto w-full max-w-2xl flex-1 px-4 py-10">
        <h1 class="text-xl font-medium">Backoffice</h1>
        <p class="mt-2 text-sm text-neutral-500">
            Каркас SPA. Бизнес-экраны и роли убраны.
        </p>
        <p
            v-if="auth.employee"
            class="mt-4 text-sm text-neutral-700"
        >
            Сессия: {{ auth.employee.name }}
            <span v-if="auth.employee.role">({{ auth.employee.role }})</span>
        </p>
        <p
            v-else
            class="mt-4 text-sm text-neutral-500"
        >
            Нет активной сессии сотрудника.
        </p>
    </section>
</template>
