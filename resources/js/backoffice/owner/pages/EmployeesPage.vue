<script setup>
import { onMounted, ref } from "vue";
import { backofficeRequest } from "../../api";

const ROLE_LABELS = {
    owner: "Владелец",
    administrator: "Администратор",
    kitchen: "Кухня",
    courier: "Курьер",
};

const employees = ref([]);
const draft = ref(empty());
const error = ref("");

function empty() {
    return {
        id: null,
        name: "",
        email: "",
        phone: "",
        role: "administrator",
        password: "",
        is_active: true,
    };
}

onMounted(load);

async function load() {
    error.value = "";
    try {
        const payload = await backofficeRequest("GET", "/api/backoffice/employees");
        employees.value = payload.employees ?? [];
    } catch (reason) {
        error.value = reason instanceof Error ? reason.message : "Не удалось связаться с сервером.";
    }
}

function edit(employee) {
    draft.value = {
        id: employee.id,
        name: employee.name,
        email: employee.email,
        phone: employee.phone ?? "",
        role: employee.role,
        password: "",
        is_active: employee.is_active !== false,
    };
}

async function submit() {
    error.value = "";
    const body = {
        name: draft.value.name,
        email: draft.value.email,
        phone: draft.value.phone || null,
        role: draft.value.role,
        password: draft.value.password || null,
        is_active: draft.value.is_active,
    };
    try {
        if (draft.value.id) {
            await backofficeRequest("PUT", `/api/backoffice/employees/${draft.value.id}`, body);
        } else {
            await backofficeRequest("POST", "/api/backoffice/employees", body);
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
        await backofficeRequest("DELETE", `/api/backoffice/employees/${id}`);
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
        <h1 class="text-xl">Сотрудники</h1>
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
                        <th class="px-3 py-2 font-medium">Роль</th>
                        <th class="px-3 py-2 font-medium" />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="employee in employees"
                        :key="employee.id"
                        class="border-b border-neutral-100"
                    >
                        <td class="px-3 py-2">
                            {{ employee.name }}
                        </td>
                        <td class="px-3 py-2">
                            {{ employee.email }}
                        </td>
                        <td class="px-3 py-2">
                            {{ ROLE_LABELS[employee.role] ?? employee.role }}
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="text-neutral-600 underline"
                                    @click="edit(employee)"
                                >
                                    Изменить
                                </button>
                                <button
                                    type="button"
                                    class="text-red-700 underline"
                                    @click="remove(employee.id)"
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
            class="grid max-w-xl gap-3 rounded border border-neutral-200 bg-white p-4 sm:grid-cols-2"
            @submit.prevent="submit"
        >
            <h2 class="text-sm font-medium sm:col-span-2">
                {{ draft.id ? `Сотрудник #${draft.id}` : "Новый сотрудник" }}
            </h2>
            <label class="block text-sm sm:col-span-2">
                <span class="text-neutral-600">Имя</span>
                <input
                    v-model="draft.name"
                    required
                    class="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
                >
            </label>
            <label class="block text-sm">
                <span class="text-neutral-600">Email</span>
                <input
                    v-model="draft.email"
                    type="email"
                    required
                    class="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
                >
            </label>
            <label class="block text-sm">
                <span class="text-neutral-600">Телефон</span>
                <input
                    v-model="draft.phone"
                    class="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
                >
            </label>
            <label class="block text-sm">
                <span class="text-neutral-600">Роль</span>
                <select
                    v-model="draft.role"
                    required
                    class="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
                >
                    <option
                        v-for="(label, value) in ROLE_LABELS"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
            </label>
            <label class="block text-sm">
                <span class="text-neutral-600">Пароль</span>
                <input
                    v-model="draft.password"
                    type="password"
                    :required="!draft.id"
                    class="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
                >
            </label>
            <label class="flex items-center gap-2 text-sm sm:col-span-2">
                <input
                    v-model="draft.is_active"
                    type="checkbox"
                >
                Активен
            </label>
            <div class="flex gap-2 sm:col-span-2">
                <button
                    type="submit"
                    class="inline-flex h-10 items-center rounded bg-neutral-900 px-4 text-sm text-white"
                >
                    Сохранить
                </button>
                <button
                    type="button"
                    class="inline-flex h-10 items-center rounded border border-neutral-300 px-4 text-sm"
                    @click="draft = empty()"
                >
                    Сброс
                </button>
            </div>
        </form>
    </section>
</template>
