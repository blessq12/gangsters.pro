<script setup>
import { onMounted, reactive, ref, watch } from "vue";
import {
    deleteEmployee,
    listEmployees,
    storeEmployee,
    toggleEmployeeActive,
    updateEmployee,
} from "../../../api/staff.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const toast = useToast();
const listApi = useApi();
const saveApi = useApi();
const {
    page,
    perPage,
    total,
    items: rows,
    offset,
    applyPayload,
} = usePagedList();
const modalOpen = ref(false);
const editingId = ref(null);
const fieldErrors = reactive({});
const form = reactive({
    name: "",
    email: "",
    phone: "",
    role: "administrator",
    password: "",
    is_active: true,
});

const confirmOpen = ref(false);
const deleteTarget = ref(null);

const roles = ["owner", "administrator", "kitchen", "courier"];

const columns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "name", label: "Имя" },
    { key: "email", label: "Email" },
    { key: "role", label: "Роль", width: "130px" },
    { key: "is_active", label: "Активен", width: "100px" },
    { key: "actions", label: "", width: "260px" },
];

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function resetForm() {
    form.name = "";
    form.email = "";
    form.phone = "";
    form.role = "administrator";
    form.password = "";
    form.is_active = true;
    clearErrors();
}

async function loadEmployees() {
    try {
        const payload = await listApi.run(() => listEmployees({
            offset: offset.value,
            limit: perPage,
        }));
        applyPayload(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить сотрудников");
    }
}

function openCreate() {
    editingId.value = null;
    resetForm();
    modalOpen.value = true;
}

function openEdit(row) {
    editingId.value = row.id;
    resetForm();
    form.name = row.name ?? "";
    form.email = row.email ?? "";
    form.phone = row.phone ?? "";
    form.role = row.role ?? "administrator";
    form.is_active = Boolean(row.is_active);
    modalOpen.value = true;
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
    if (!editingId.value && !String(form.password).trim()) {
        fieldErrors.password = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function save() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }

    const id = editingId.value;
    const body = {
        name: form.name.trim(),
        email: form.email.trim(),
        phone: form.phone || null,
        role: form.role,
        is_active: Boolean(form.is_active),
    };
    if (!id || form.password) {
        body.password = form.password;
    }

    try {
        await saveApi.run(() =>
            id ? updateEmployee(id, body) : storeEmployee(body),
        );
        toast.success(id ? "Сохранено" : "Создано");
        modalOpen.value = false;
        await loadEmployees();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

async function onToggle(row) {
    try {
        await saveApi.run(() => toggleEmployeeActive(row.id));
        toast.success(row.is_active ? "Деактивирован" : "Активирован");
        await loadEmployees();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

function askDelete(row) {
    deleteTarget.value = row;
    confirmOpen.value = true;
}

async function confirmDelete() {
    const target = deleteTarget.value;
    confirmOpen.value = false;
    deleteTarget.value = null;
    if (!target) {
        return;
    }
    try {
        await saveApi.run(() => deleteEmployee(target.id));
        toast.success("Удалён");
        await loadEmployees();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка удаления");
    }
}

function roleVariant(role) {
    if (role === "owner") {
        return "danger";
    }
    if (role === "administrator") {
        return "warning";
    }
    if (role === "kitchen") {
        return "success";
    }
    return "neutral";
}

watch(page, loadEmployees);
onMounted(loadEmployees);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Сотрудники">
            <template #actions>
                <UiButton @click="openCreate">+ Сотрудник</UiButton>
            </template>

            <UiTable
                :columns="columns"
                :rows="rows"
                :loading="listApi.loading"
                empty-text="Нет сотрудников"
            >
                <template #cell-role="{ value }">
                    <UiBadge :variant="roleVariant(value)" :text="String(value)" />
                </template>
                <template #cell-is_active="{ value }">
                    <UiBadge
                        :variant="value ? 'success' : 'neutral'"
                        :text="value ? 'да' : 'нет'"
                    />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex flex-wrap gap-1">
                        <UiButton @click.stop="openEdit(row)">Редактировать</UiButton>
                        <UiButton @click.stop="onToggle(row)">
                            {{ row.is_active ? "Деактивировать" : "Активировать" }}
                        </UiButton>
                        <UiButton @click.stop="askDelete(row)">Удалить</UiButton>
                    </div>
                </template>
            </UiTable>
            <div class="mt-6">
                <UiPagination
                    :page="page"
                    :per-page="perPage"
                    :total="total"
                    @update:page="page = $event"
                />
            </div>
        </UiCard>

        <UiModal
            :open="modalOpen"
            :title="editingId ? 'Редактировать сотрудника' : 'Новый сотрудник'"
            @close="modalOpen = false"
        >
            <div class="space-y-5">
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
                    label="Пароль"
                    :required="!editingId"
                    :error="fieldErrors.password"
                >
                    <UiInput v-model="form.password" type="password" />
                </UiFormField>
                <UiFormField label="Активен">
                    <input v-model="form.is_active" type="checkbox" class="mt-2">
                </UiFormField>
            </div>
            <template #footer>
                <UiButton @click="modalOpen = false">Отмена</UiButton>
                <UiButton :disabled="saveApi.loading" @click="save">Сохранить</UiButton>
            </template>
        </UiModal>

        <UiConfirmDialog
            :open="confirmOpen"
            title="Удалить сотрудника?"
            :message="deleteTarget
                ? `Удалить ${deleteTarget.name} (${deleteTarget.email})?`
                : ''"
            @confirm="confirmDelete"
            @cancel="confirmOpen = false; deleteTarget = null"
        />
    </div>
</template>
