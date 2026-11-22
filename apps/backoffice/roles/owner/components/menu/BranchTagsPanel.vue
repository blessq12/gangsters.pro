<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import {
    deleteBranchTag,
    listBranchTags,
    storeBranchTag,
    updateBranchTag,
} from "../../../../api/company.js";
import { useApi } from "../../../../shell/composables/useApi.js";
import { useClientList } from "../../../../shell/composables/useClientList.js";
import { useToast } from "../../../../shell/composables/useToast.js";
import ShellIcon from "../../../../shell/components/ShellIcon.vue";
import UiBadge from "../../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../../shell/components/ui/UiConfirmDialog.vue";
import UiFormField from "../../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../../shell/components/ui/UiTable.vue";
import UiToggle from "../../../../shell/components/ui/UiToggle.vue";
import { useOwnerBranchContext } from "../../composables/useOwnerBranchContext.js";
import OwnerBranchSelector from "../branch/OwnerBranchSelector.vue";

defineProps({
    /** Селектор филиала уже в хабе. */
    embedded: { type: Boolean, default: false },
});

const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();
const { selectedBranchId, ensureLoaded } = useOwnerBranchContext();

const {
    searchQuery,
    page,
    perPage,
    total,
    pageItems,
    setItems,
    allItems,
} = useClientList({
    perPage: 20,
    getHaystack: (row) => `${row.code ?? ""} ${row.label ?? ""}`,
});

const modalOpen = ref(false);
const editingId = ref(null);
const deleteTarget = ref(null);
const fieldErrors = reactive({});
const form = reactive({
    code: "",
    label: "",
    color: "#999999",
    sort_order: "0",
    is_active: true,
});

const columns = [
    { key: "code", label: "Код" },
    { key: "label", label: "Подпись" },
    { key: "color", label: "Цвет", width: "120px" },
    { key: "sort_order", label: "Порядок", width: "100px" },
    { key: "is_active", label: "Активен", width: "100px" },
    { key: "actions", label: "", width: "112px" },
];

const modalTitle = computed(() => (editingId.value ? "Редактировать тег" : "Новый тег"));
const confirmMessage = computed(() =>
    deleteTarget.value ? `Удалить тег «${deleteTarget.value.label}»?` : "",
);

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

async function load() {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        setItems([]);
        return;
    }
    try {
        const payload = await loadApi.run(() => listBranchTags(branchId));
        if (branchId !== selectedBranchId.value) {
            return;
        }
        setItems(Array.isArray(payload?.items) ? payload.items : []);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить теги");
    }
}

function openCreate() {
    editingId.value = null;
    clearErrors();
    form.code = "";
    form.label = "";
    form.color = "#999999";
    form.sort_order = String(allItems.value.length);
    form.is_active = true;
    modalOpen.value = true;
}

function openEdit(row) {
    editingId.value = row.id;
    clearErrors();
    form.code = row.code ?? "";
    form.label = row.label ?? "";
    form.color = row.color ?? "#999999";
    form.sort_order = String(row.sort_order ?? 0);
    form.is_active = Boolean(row.is_active);
    modalOpen.value = true;
}

function validate() {
    clearErrors();
    if (!String(form.code).trim()) {
        fieldErrors.code = "Обязательное поле";
    }
    if (!String(form.label).trim()) {
        fieldErrors.label = "Обязательное поле";
    }
    const order = Number(form.sort_order);
    if (form.sort_order === "" || !Number.isInteger(order) || order < 0) {
        fieldErrors.sort_order = "Целое число ≥ 0";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function save() {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        toast.error("Выбери филиал");
        return;
    }
    if (!validate()) {
        toast.error("Проверьте поля");
        return;
    }
    const id = editingId.value;
    const body = {
        code: form.code.trim(),
        label: form.label.trim(),
        color: form.color.trim() || "#999999",
        is_active: Boolean(form.is_active),
        sort_order: Number(form.sort_order),
    };
    try {
        await saveApi.run(() => (
            id ? updateBranchTag(branchId, id, body) : storeBranchTag(branchId, body)
        ));
        toast.success(id ? "Сохранено" : "Создано");
        modalOpen.value = false;
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

async function confirmDelete() {
    const target = deleteTarget.value;
    const branchId = selectedBranchId.value;
    deleteTarget.value = null;
    if (!target || !branchId) {
        return;
    }
    try {
        await deleteBranchTag(branchId, target.id);
        toast.success("Удалено");
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось удалить");
    }
}

watch(selectedBranchId, load);
onMounted(async () => {
    await ensureLoaded();
    await load();
});
</script>

<template>
    <UiCard title="Теги филиала">
        <template #actions>
            <div class="flex flex-wrap items-center gap-2">
                <OwnerBranchSelector v-if="!embedded" />
                <UiButton :disabled="!selectedBranchId" @click="openCreate">
                    + Тег
                </UiButton>
            </div>
        </template>

        <template v-if="selectedBranchId">
            <div class="mb-4 max-w-md">
                <UiInput
                    v-model="searchQuery"
                    type="search"
                    placeholder="Поиск по коду или подписи"
                />
            </div>

            <UiTable
                :columns="columns"
                :rows="pageItems"
                :loading="loadApi.loading"
                empty-text="Ничего не найдено"
            >
                <template #cell-color="{ value }">
                    <span class="inline-flex items-center gap-2">
                        <span
                            class="inline-block h-4 w-4 border border-bo-border"
                            :style="{ backgroundColor: value }"
                        />
                        {{ value }}
                    </span>
                </template>
                <template #cell-is_active="{ value }">
                    <UiBadge
                        :variant="value ? 'success' : 'neutral'"
                        :text="value ? 'да' : 'нет'"
                    />
                </template>
                <template #cell-actions="{ row }">
                    <div class="flex flex-wrap gap-1">
                        <UiButton
                            class="!h-9 !px-2.5"
                            title="Редактировать"
                            aria-label="Редактировать"
                            @click.stop="openEdit(row)"
                        >
                            <ShellIcon name="edit" class="h-4 w-4" />
                        </UiButton>
                        <UiButton
                            class="!h-9 !px-2.5"
                            title="Удалить"
                            aria-label="Удалить"
                            @click.stop="deleteTarget = row"
                        >
                            <ShellIcon name="trash" class="h-4 w-4" />
                        </UiButton>
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
        </template>

        <UiModal :open="modalOpen" :title="modalTitle" @close="modalOpen = false">
            <div class="space-y-5">
                <UiFormField label="Код" required :error="fieldErrors.code">
                    <UiInput v-model="form.code" />
                </UiFormField>
                <UiFormField label="Подпись" required :error="fieldErrors.label">
                    <UiInput v-model="form.label" />
                </UiFormField>
                <UiFormField label="Цвет">
                    <UiInput v-model="form.color" placeholder="#999999" />
                </UiFormField>
                <UiFormField label="Порядок" :error="fieldErrors.sort_order">
                    <UiInput v-model="form.sort_order" type="number" />
                </UiFormField>
                <UiToggle v-model="form.is_active" label-on="Активен" label-off="Выключен" />
            </div>

            <template #footer>
                <UiButton class="!bg-white/10 !text-bo-fg" @click="modalOpen = false">
                    Отмена
                </UiButton>
                <UiButton :disabled="saveApi.loading" @click="save">
                    Сохранить
                </UiButton>
            </template>
        </UiModal>

        <UiConfirmDialog
            :open="deleteTarget !== null"
            title="Удаление тега"
            :message="confirmMessage"
            @confirm="confirmDelete"
            @cancel="deleteTarget = null"
        />
    </UiCard>
</template>
