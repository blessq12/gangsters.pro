<script setup>
import { onMounted, ref } from "vue";
import { backofficeRequest } from "../../api";

const operators = ref([]);
const draft = ref(empty());
const error = ref("");

function empty() {
    return { id: null, name: "", email: "", password: "" };
}

onMounted(load);

async function load() {
    error.value = "";
    try {
        const payload = await backofficeRequest("GET", "/api/backoffice/operators");
        operators.value = payload.operators ?? [];
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}

function edit(operator) {
    draft.value = { id: operator.id, name: operator.name, email: operator.email, password: "" };
}

async function submit() {
    error.value = "";
    const body = { name: draft.value.name, email: draft.value.email, password: draft.value.password || null };
    try {
        if (draft.value.id) {
            await backofficeRequest("PUT", `/api/backoffice/operators/${draft.value.id}`, body);
        } else {
            await backofficeRequest("POST", "/api/backoffice/operators", body);
        }
        draft.value = empty();
        await load();
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}

async function remove(id) {
    error.value = "";
    try {
        await backofficeRequest("DELETE", `/api/backoffice/operators/${id}`);
        if (draft.value.id === id) {
            draft.value = empty();
        }
        await load();
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}
</script>

<template>
    <section class="space-y-4">
        <h1 class="text-xl">Операторы</h1>
        <p
            v-if="error"
            class="text-sm text-red-700"
        >
            {{ error }}
        </p>
        <div class="overflow-x-auto rounded border border-neutral-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500">
                    <tr>
                        <th class="px-3 py-2 font-medium">Имя</th>
                        <th class="px-3 py-2 font-medium">Email</th>
                        <th class="px-3 py-2 font-medium" />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="operator in operators"
                        :key="operator.id"
                        class="border-b border-neutral-100 last:border-0"
                    >
                        <td class="px-3 py-2">{{ operator.name }}</td>
                        <td class="px-3 py-2">{{ operator.email }}</td>
                        <td class="px-3 py-2">
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
                                    @click="edit(operator)"
                                >
                                    Править
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
                                    @click="remove(operator.id)"
                                >
                                    Удалить
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <form
            class="grid gap-3 sm:grid-cols-2"
            @submit.prevent="submit"
        >
            <h2 class="text-sm font-medium sm:col-span-2">{{ draft.id ? `Оператор #${draft.id}` : "Новый оператор" }}</h2>
            <input
                v-model="draft.name"
                required
                placeholder="Имя"
                class="h-9 rounded border border-neutral-300 px-3 text-sm"
            >
            <input
                v-model="draft.email"
                required
                type="email"
                placeholder="Email"
                class="h-9 rounded border border-neutral-300 px-3 text-sm"
            >
            <input
                v-model="draft.password"
                :required="!draft.id"
                type="password"
                placeholder="Пароль"
                class="h-9 rounded border border-neutral-300 px-3 text-sm"
            >
            <div class="flex gap-2">
                <button
                    type="submit"
                    class="inline-flex h-9 items-center rounded bg-neutral-900 px-3 text-sm text-white"
                >
                    Сохранить
                </button>
                <button
                    type="button"
                    class="inline-flex h-9 items-center rounded border border-neutral-300 px-3 text-sm"
                    @click="draft = empty()"
                >
                    Новый
                </button>
            </div>
        </form>
    </section>
</template>
