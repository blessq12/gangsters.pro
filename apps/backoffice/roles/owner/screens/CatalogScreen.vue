<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import draggable from "vuedraggable";
import {
    deleteCategory,
    deleteProduct,
    deleteSet,
    deleteTag,
    listCategories,
    listProducts,
    listSets,
    listTags,
    reorderCategories,
    storeTag,
    updateTag,
} from "../../../api/catalog.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import ShellIcon from "../../../shell/components/ShellIcon.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

defineProps({
    /** Без внешней рамки страницы — для хаба «Меню». */
    embedded: { type: Boolean, default: false },
});

const router = useRouter();
const toast = useToast();
const catalogApi = useApi();
const saveApi = useApi();
const reorderApi = useApi();
const deleteApi = useApi();
const {
    page,
    perPage,
    total,
    items: rows,
    offset,
    resetPage,
    applyPayload,
} = usePagedList();

const tab = ref("products");

const modalOpen = ref(false);
const editingId = ref(null);
const fieldErrors = reactive({});
const form = reactive({
    code: "",
    label: "",
    color: "#999999",
    is_active: true,
});

const confirmOpen = ref(false);
/** @type {import('vue').Ref<{ kind: string, id: number, title: string }|null>} */
const deleteTarget = ref(null);

const tabs = [
    { id: "categories", label: "Категории" },
    { id: "products", label: "Товары" },
    { id: "sets", label: "Наборы" },
    { id: "tags", label: "Теги" },
];

const columnsByTab = {
    products: [
        { key: "name", label: "Название" },
        { key: "price", label: "Цена", width: "90px" },
        { key: "status", label: "Статус", width: "110px" },
        { key: "actions", label: "", width: "112px" },
    ],
    sets: [
        { key: "name", label: "Название" },
        { key: "price", label: "Цена", width: "90px" },
        { key: "status", label: "Статус", width: "110px" },
        { key: "actions", label: "", width: "112px" },
    ],
    tags: [
        { key: "code", label: "Код" },
        { key: "label", label: "Подпись" },
        { key: "is_active", label: "Активен", width: "100px" },
        { key: "actions", label: "", width: "112px" },
    ],
};

const deleteConfirmMessage = computed(() => {
    const target = deleteTarget.value;
    if (!target) {
        return "";
    }
    return `Удалить «${target.title}»? Это действие нельзя отменить.`;
});

const currentColumns = computed(() => columnsByTab[tab.value] ?? []);
const modalTitle = computed(() => (editingId.value ? "Редактировать тег" : "Новый тег"));

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function resetForm() {
    form.code = "";
    form.label = "";
    form.color = "#999999";
    form.is_active = true;
    clearErrors();
}

async function loadTab() {
    try {
        const query = { offset: offset.value, limit: perPage };
        let payload;
        if (tab.value === "products") {
            payload = await catalogApi.run(() => listProducts(query));
        } else if (tab.value === "sets") {
            payload = await catalogApi.run(() => listSets(query));
        } else if (tab.value === "categories") {
            payload = await catalogApi.run(() => listCategories(query));
        } else {
            payload = await catalogApi.run(() => listTags(query));
        }
        applyPayload(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить каталог");
    }
}

function openCreate(kind) {
    if (kind === "product") {
        router.push({ name: "owner-catalog-product", params: { id: "new" } });
        return;
    }
    if (kind === "set") {
        router.push({ name: "owner-catalog-set", params: { id: "new" } });
        return;
    }
    if (kind === "category") {
        router.push({ name: "owner-catalog-category", params: { id: "new" } });
        return;
    }
    editingId.value = null;
    resetForm();
    modalOpen.value = true;
}

function openEdit(kind, row) {
    if (kind === "product") {
        router.push({ name: "owner-catalog-product", params: { id: row.id } });
        return;
    }
    if (kind === "set") {
        router.push({ name: "owner-catalog-set", params: { id: row.id } });
        return;
    }
    if (kind === "category") {
        router.push({ name: "owner-catalog-category", params: { id: row.id } });
        return;
    }
    editingId.value = row.id;
    resetForm();
    form.code = row.code ?? "";
    form.label = row.label ?? "";
    form.color = row.color ?? "#999999";
    form.is_active = Boolean(row.is_active);
    modalOpen.value = true;
}

function validateTag() {
    clearErrors();
    if (!String(form.code).trim()) {
        fieldErrors.code = "Обязательное поле";
    }
    if (!String(form.label).trim()) {
        fieldErrors.label = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function saveTag() {
    if (!validateTag()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    const id = editingId.value;
    try {
        await saveApi.run(async () => {
            const body = {
                code: form.code.trim(),
                label: form.label.trim(),
                color: form.color || "#999999",
                is_active: Boolean(form.is_active),
            };
            return id ? updateTag(id, body) : storeTag(body);
        });
        toast.success(id ? "Сохранено" : "Создано");
        modalOpen.value = false;
        await loadTab();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

function askDelete(kind, row) {
    if (kind === "product" && row.is_system) {
        toast.error("Системный товар нельзя удалить");
        return;
    }
    const title = row.name || row.label || row.code || `#${row.id}`;
    deleteTarget.value = { kind, id: Number(row.id), title: String(title) };
    confirmOpen.value = true;
}

async function confirmDelete() {
    const target = deleteTarget.value;
    confirmOpen.value = false;
    deleteTarget.value = null;
    if (!target?.id) {
        return;
    }
    try {
        await deleteApi.run(async () => {
            if (target.kind === "product") {
                return deleteProduct(target.id);
            }
            if (target.kind === "set") {
                return deleteSet(target.id);
            }
            if (target.kind === "category") {
                return deleteCategory(target.id);
            }
            return deleteTag(target.id);
        });
        toast.success("Удалено");
        await loadTab();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось удалить");
    }
}

async function onCategoriesReorder() {
    const pageIds = rows.value.map((row) => Number(row.id)).filter((id) => id > 0);
    if (pageIds.length === 0) {
        return;
    }
    try {
        const allPayload = await listCategories({ offset: 0, limit: Math.max(total.value, pageIds.length) });
        const allIds = (allPayload?.items ?? []).map((row) => Number(row.id)).filter((id) => id > 0);
        const start = offset.value;
        const next = [
            ...allIds.slice(0, start),
            ...pageIds,
            ...allIds.slice(start + pageIds.length),
        ];
        await reorderApi.run(() => reorderCategories(next));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось сохранить порядок");
        await loadTab();
    }
}

watch(tab, () => {
    resetPage();
    loadTab();
});
watch(page, loadTab);
onMounted(loadTab);
</script>

<template>
    <div
        :class="embedded
            ? 'space-y-8'
            : 'mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10'"
    >
        <UiCard title="Справочник">
            <template #actions>
                <UiButton @click="openCreate('product')">
                    + Товар
                </UiButton>
                <UiButton @click="openCreate('set')">
                    + Набор
                </UiButton>
                <UiButton @click="openCreate('category')">
                    + Категория
                </UiButton>
                <UiButton @click="openCreate('tag')">
                    + Тег
                </UiButton>
            </template>

            <div class="mb-8 flex flex-wrap gap-2">
                <button
                    v-for="item in tabs"
                    :key="item.id"
                    type="button"
                    class="h-10 px-4 text-sm font-medium transition-colors duration-150"
                    :class="tab === item.id
                        ? 'bg-bo-accent text-white'
                        : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'"
                    @click="tab = item.id"
                >
                    {{ item.label }}
                </button>
            </div>

            <div v-if="tab === 'categories'">
                <p v-if="catalogApi.loading" class="px-4 py-12 text-center text-sm text-bo-muted">
                    Загрузка…
                </p>
                <p
                    v-else-if="rows.length === 0"
                    class="border border-bo-border px-4 py-12 text-center text-sm text-bo-muted"
                >
                    Пусто
                </p>
                <draggable
                    v-else
                    v-model="rows"
                    item-key="id"
                    handle=".drag-handle"
                    class="divide-y divide-bo-border border border-bo-border"
                    :disabled="reorderApi.loading"
                    @end="onCategoriesReorder"
                >
                    <template #item="{ element }">
                        <div class="flex flex-wrap items-center gap-3 px-4 py-3.5 transition-colors hover:bg-white/[0.03]">
                            <span class="drag-handle cursor-grab select-none text-bo-muted active:cursor-grabbing">
                                ⋮⋮
                            </span>
                            <span class="min-w-0 flex-1 text-sm text-bo-fg">
                                {{ element.name }}
                            </span>
                            <UiBadge
                                :variant="element.is_active ? 'success' : 'neutral'"
                                :text="element.is_active ? 'да' : 'нет'"
                            />
                            <div class="flex flex-wrap gap-1">
                                <UiButton
                                    class="!h-9 !px-2.5"
                                    title="Редактировать"
                                    aria-label="Редактировать"
                                    @click="openEdit('category', element)"
                                >
                                    <ShellIcon name="edit" class="h-4 w-4" />
                                </UiButton>
                                <UiButton
                                    class="!h-9 !px-2.5"
                                    title="Удалить"
                                    aria-label="Удалить"
                                    @click="askDelete('category', element)"
                                >
                                    <ShellIcon name="trash" class="h-4 w-4" />
                                </UiButton>
                            </div>
                        </div>
                    </template>
                </draggable>
            </div>

            <UiTable
                v-else
                :columns="currentColumns"
                :rows="rows"
                :loading="catalogApi.loading"
                empty-text="Пусто"
            >
                <template #cell-status="{ value }">
                    <UiBadge
                        :variant="value === 'active' ? 'success' : 'neutral'"
                        :text="String(value)"
                    />
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
                            @click.stop="openEdit(
                                tab === 'products' ? 'product'
                                    : tab === 'sets' ? 'set' : 'tag',
                                row,
                            )"
                        >
                            <ShellIcon name="edit" class="h-4 w-4" />
                        </UiButton>
                        <UiButton
                            class="!h-9 !px-2.5"
                            title="Удалить"
                            aria-label="Удалить"
                            :disabled="tab === 'products' && row.is_system"
                            @click.stop="askDelete(
                                tab === 'products' ? 'product'
                                    : tab === 'sets' ? 'set' : 'tag',
                                row,
                            )"
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
        </UiCard>

        <UiModal :open="modalOpen" :title="modalTitle" @close="modalOpen = false">
            <div class="space-y-5">
                <UiFormField label="Код" required :error="fieldErrors.code">
                    <UiInput v-model="form.code" />
                </UiFormField>
                <UiFormField label="Подпись" required :error="fieldErrors.label">
                    <UiInput v-model="form.label" />
                </UiFormField>
                <UiFormField label="Цвет">
                    <UiInput v-model="form.color" />
                </UiFormField>
                <UiFormField label="Активен">
                    <input v-model="form.is_active" type="checkbox" class="mt-2">
                </UiFormField>
            </div>

            <template #footer>
                <UiButton @click="modalOpen = false">
                    Отмена
                </UiButton>
                <UiButton :disabled="saveApi.loading" @click="saveTag">
                    Сохранить
                </UiButton>
            </template>
        </UiModal>

        <UiConfirmDialog
            :open="confirmOpen"
            title="Удаление"
            :message="deleteConfirmMessage"
            @confirm="confirmDelete"
            @cancel="confirmOpen = false; deleteTarget = null"
        />
    </div>
</template>
