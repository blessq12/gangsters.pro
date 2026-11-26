<script setup>
import { computed, onMounted, reactive, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    getEmployee,
    storeEmployee,
    toggleEmployeeActive,
    updateEmployee,
} from "../../../api/staff.js";
import { useOwnerBranchContext } from "../composables/useOwnerBranchContext.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiToggle from "../../../shell/components/ui/UiToggle.vue";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();
const actionApi = useApi();
const { branches, selectedBranchId, ensureLoaded, branchLabel } = useOwnerBranchContext();

const isNew = computed(() => route.params.id === "new");
const employeeId = computed(() => (isNew.value ? null : Number(route.params.id)));

const fieldErrors = reactive({});
const form = reactive({
    name: "",
    email: "",
    phone: "",
    role: "administrator",
    branch_id: "",
    password: "",
    is_active: true,
});

const roles = ["owner", "administrator", "kitchen", "courier"];
const branchRequired = computed(() => form.role === "kitchen" || form.role === "courier");

const title = computed(() => {
    if (isNew.value) {
        return "Новый сотрудник";
    }
    const name = String(form.name ?? "").trim();
    return name !== "" ? name : "Сотрудник";
});

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function applyDetail(payload) {
    form.name = payload?.name ?? "";
    form.email = payload?.email ?? "";
    form.phone = payload?.phone ?? "";
    form.role = payload?.role ?? "administrator";
    form.branch_id = payload?.branch_id != null ? String(payload.branch_id) : "";
    form.password = "";
    form.is_active = payload?.is_active ?? true;
    clearErrors();
}

async function load() {
    clearErrors();
    if (isNew.value) {
        applyDetail({
            role: "administrator",
            is_active: true,
            branch_id: selectedBranchId.value || null,
        });
        return;
    }
    try {
        const payload = await loadApi.run(() => getEmployee(employeeId.value));
        applyDetail(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить сотрудника");
        router.push({ name: "owner-staff" });
    }
}

function validate() {
    clearErrors();
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    if (!String(form.email).trim()) {
        fieldErrors.email = "Обязательное поле";
    }
    if (!form.role) {
        fieldErrors.role = "Обязательное поле";
    }
    if (branchRequired.value && !String(form.branch_id).trim()) {
        fieldErrors.branch_id = "Для этой роли нужен филиал";
    }
    if (isNew.value && !String(form.password).trim()) {
        fieldErrors.password = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function save() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }

    const body = {
        name: form.name.trim(),
        email: form.email.trim(),
        phone: form.phone || null,
        role: form.role,
        branch_id: form.branch_id ? Number(form.branch_id) : null,
        is_active: Boolean(form.is_active),
    };
    if (isNew.value || form.password) {
        body.password = form.password;
    }

    try {
        const result = await saveApi.run(() =>
            employeeId.value ? updateEmployee(employeeId.value, body) : storeEmployee(body),
        );
        toast.success(employeeId.value ? "Сохранено" : "Создано");
        if (isNew.value && result?.id) {
            await router.replace({
                name: "owner-staff-employee",
                params: { id: result.id },
            });
            return;
        }
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

async function onToggleActive() {
    if (!employeeId.value) {
        return;
    }
    try {
        await actionApi.run(() => toggleEmployeeActive(employeeId.value));
        toast.success(form.is_active ? "Уволен" : "Активирован");
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

watch(() => route.params.id, load);
watch(
    () => form.role,
    (role) => {
        if (role === "owner" || role === "administrator") {
            return;
        }
        if (!form.branch_id && selectedBranchId.value) {
            form.branch_id = String(selectedBranchId.value);
        }
    },
);

onMounted(async () => {
    await ensureLoaded();
    await load();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    @click="router.push({ name: 'owner-staff' })"
                >
                    К сотрудникам
                </UiButton>
                <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                    {{ title }}
                </h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <UiToggle
                    v-if="!isNew"
                    :model-value="form.is_active"
                    label-on="Активен"
                    label-off="Уволен"
                    :disabled="loadApi.loading || actionApi.loading"
                    @update:model-value="onToggleActive"
                />
                <UiToggle
                    v-else
                    v-model="form.is_active"
                    label-on="Активен"
                    label-off="Уволен"
                    :disabled="loadApi.loading"
                />
                <UiButton :disabled="saveApi.loading || loadApi.loading" @click="save">
                    Сохранить
                </UiButton>
            </div>
        </div>

        <p v-if="loadApi.loading" class="text-sm text-bo-muted">Загрузка…</p>

        <UiCard v-else title="Карточка">
            <div class="grid gap-4 sm:grid-cols-2">
                <UiFormField label="Имя" required :error="fieldErrors.name">
                    <UiInput v-model="form.name" />
                </UiFormField>
                <UiFormField label="Email" required :error="fieldErrors.email">
                    <UiInput v-model="form.email" type="email" />
                </UiFormField>
                <UiFormField label="Телефон">
                    <UiInput v-model="form.phone" type="tel" />
                </UiFormField>
                <UiFormField label="Роль" required :error="fieldErrors.role">
                    <select
                        v-model="form.role"
                        class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                    >
                        <option v-for="role in roles" :key="role" :value="role">
                            {{ role }}
                        </option>
                    </select>
                </UiFormField>
                <UiFormField
                    label="Филиал"
                    :required="branchRequired"
                    :error="fieldErrors.branch_id"
                >
                    <select
                        v-model="form.branch_id"
                        class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm focus:border-bo-accent focus:outline-none"
                    >
                        <option value="">
                            {{ branchRequired ? "Выбери филиал" : "Вся компания" }}
                        </option>
                        <option
                            v-for="branch in branches"
                            :key="branch.id"
                            :value="String(branch.id)"
                        >
                            {{ branchLabel(branch) }}
                        </option>
                    </select>
                </UiFormField>
                <UiFormField label="Пароль" :required="isNew" :error="fieldErrors.password">
                    <UiInput
                        v-model="form.password"
                        type="password"
                        :placeholder="isNew ? '' : 'Оставь пустым, чтобы не менять'"
                    />
                </UiFormField>
            </div>
        </UiCard>
    </div>
</template>
