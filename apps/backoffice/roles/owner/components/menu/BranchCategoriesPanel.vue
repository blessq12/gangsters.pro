<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import draggable from "vuedraggable";
import {
    deleteBranchCategory,
    listBranchCategories,
    listBranchSets,
    reorderBranchCategories,
    storeBranchCategory,
    updateBranchCategory,
} from "../../../../api/company.js";
import { useApi } from "../../../../shell/composables/useApi.js";
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
import UiToggle from "../../../../shell/components/ui/UiToggle.vue";
import { fetchAllProducts } from "../../composables/fetchAllProducts.js";
import { useOwnerBranchContext } from "../../composables/useOwnerBranchContext.js";
import OwnerBranchSelector from "../branch/OwnerBranchSelector.vue";

defineProps({
    /** Селектор филиала уже в хабе. */
    embedded: { type: Boolean, default: false },
});

const PER_PAGE = 20;

const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();
const reorderApi = useApi();
const { selectedBranchId, ensureLoaded } = useOwnerBranchContext();

const categories = ref([]);
const products = ref([]);
const sets = ref([]);
const searchQuery = ref("");
const page = ref(1);

const modalOpen = ref(false);
const editingId = ref(null);
const deleteTarget = ref(null);
const addKey = ref("");
const fieldErrors = reactive({});
const form = reactive({
    name: "",
    slug: "",
    sort_order: "0",
    is_active: true,
    is_accompanying: false,
    /** @type {Array<{ entry_kind: 'product'|'set', entry_id: number }>} */
    items: [],
});

const modalTitle = computed(() => (editingId.value ? "Редактировать категорию" : "Новая категория"));
const confirmMessage = computed(() =>
    deleteTarget.value ? `Удалить категорию «${deleteTarget.value.name}»?` : "",
);

const normalizedQuery = computed(() => searchQuery.value.trim().toLowerCase());
/** Drag-and-drop порядка только без поиска — иначе ломается полный порядок. */
const canReorder = computed(() => normalizedQuery.value === "");

const filteredCategories = computed(() => {
    const q = normalizedQuery.value;
    if (q === "") {
        return categories.value;
    }
    return categories.value.filter((row) => String(row.name ?? "").toLowerCase().includes(q));
});

const total = computed(() => filteredCategories.value.length);

const pageItems = computed(() => {
    if (canReorder.value) {
        return categories.value;
    }
    const start = (page.value - 1) * PER_PAGE;
    return filteredCategories.value.slice(start, start + PER_PAGE);
});

watch(searchQuery, () => {
    page.value = 1;
});

const entryOptions = computed(() => [
    ...products.value.map((item) => ({
        key: `product:${item.id}`,
        entry_kind: "product",
        entry_id: Number(item.id),
        label: `Товар: ${item.name}`,
    })),
    ...sets.value.map((item) => ({
        key: `set:${item.id}`,
        entry_kind: "set",
        entry_id: Number(item.id),
        label: `Набор: ${item.name}`,
    })),
]);

const optionByKey = computed(() => new Map(entryOptions.value.map((option) => [option.key, option])));

const availableOptions = computed(() => {
    const used = new Set(form.items.map((item) => `${item.entry_kind}:${item.entry_id}`));
    return entryOptions.value.filter((option) => !used.has(option.key));
});

function itemLabel(item) {
    return optionByKey.value.get(`${item.entry_kind}:${item.entry_id}`)?.label
        ?? `${item.entry_kind === "set" ? "Набор" : "Товар"} #${item.entry_id}`;
}

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

async function load() {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        categories.value = [];
        return;
    }
    try {
        const [categoriesPayload, setsPayload, allProducts] = await loadApi.run(() => Promise.all([
            listBranchCategories(branchId),
            listBranchSets(branchId),
            fetchAllProducts(),
        ]));
        if (branchId !== selectedBranchId.value) {
            return;
        }
        categories.value = Array.isArray(categoriesPayload?.items) ? categoriesPayload.items : [];
        sets.value = Array.isArray(setsPayload?.items) ? setsPayload.items : [];
        products.value = allProducts;
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить категории");
    }
}

function openCreate() {
    editingId.value = null;
    clearErrors();
    addKey.value = "";
    form.name = "";
    form.slug = "";
    form.sort_order = String(categories.value.length);
    form.is_active = true;
    form.is_accompanying = false;
    form.items = [];
    modalOpen.value = true;
}

function openEdit(row) {
    editingId.value = row.id;
    clearErrors();
    addKey.value = "";
    form.name = row.name ?? "";
    form.slug = row.slug ?? "";
    form.sort_order = String(row.sort_order ?? 0);
    form.is_active = Boolean(row.is_active);
    form.is_accompanying = Boolean(row.is_accompanying);
    form.items = (row.items ?? [])
        .slice()
        .sort((a, b) => a.sort_order - b.sort_order)
        .map((item) => ({ entry_kind: item.entry_kind, entry_id: Number(item.entry_id) }));
    modalOpen.value = true;
}

function addItem() {
    const option = optionByKey.value.get(addKey.value);
    if (!option) {
        return;
    }
    form.items.push({ entry_kind: option.entry_kind, entry_id: option.entry_id });
    addKey.value = "";
}

function removeItem(index) {
    form.items.splice(index, 1);
}

function moveItem(index, delta) {
    const target = index + delta;
    if (target < 0 || target >= form.items.length) {
        return;
    }
    const [moved] = form.items.splice(index, 1);
    form.items.splice(target, 0, moved);
}

function validate() {
    clearErrors();
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
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
        name: form.name.trim(),
        slug: form.slug.trim() || null,
        sort_order: Number(form.sort_order),
        is_active: Boolean(form.is_active),
        is_accompanying: Boolean(form.is_accompanying),
        items: form.items.map((item, index) => ({
            entry_kind: item.entry_kind,
            entry_id: item.entry_id,
            sort_order: index,
        })),
    };
    try {
        await saveApi.run(() => (
            id ? updateBranchCategory(branchId, id, body) : storeBranchCategory(branchId, body)
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
        await deleteBranchCategory(branchId, target.id);
        toast.success("Удалено");
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось удалить");
    }
}

async function onReorder() {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        return;
    }
    try {
        await reorderApi.run(() => reorderBranchCategories(
            branchId,
            categories.value.map((row) => Number(row.id)),
        ));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось сохранить порядок");
        await load();
    }
}

watch(selectedBranchId, load);
onMounted(async () => {
    await ensureLoaded();
    await load();
});
</script>

<template>
    <UiCard title="Категории филиала">
        <template #actions>
            <div class="flex flex-wrap items-center gap-2">
                <OwnerBranchSelector v-if="!embedded" />
                <UiButton :disabled="!selectedBranchId" @click="openCreate">
                    + Категория
                </UiButton>
            </div>
        </template>

        <template v-if="selectedBranchId">
            <div class="mb-4 max-w-md">
                <UiInput
                    v-model="searchQuery"
                    type="search"
                    placeholder="Поиск по названию"
                />
            </div>
            <p v-if="loadApi.loading" class="px-4 py-12 text-center text-sm text-bo-muted">
                Загрузка…
            </p>
            <p
                v-else-if="pageItems.length === 0"
                class="border border-bo-border px-4 py-12 text-center text-sm text-bo-muted"
            >
                Ничего не найдено
            </p>
            <draggable
                v-else-if="canReorder"
                v-model="categories"
                item-key="id"
                handle=".drag-handle"
                class="divide-y divide-bo-border border border-bo-border"
                :disabled="reorderApi.loading"
                @end="onReorder"
            >
                <template #item="{ element }">
                    <div class="flex flex-wrap items-center gap-3 px-4 py-3.5 transition-colors hover:bg-white/[0.03]">
                        <span class="drag-handle cursor-grab select-none text-bo-muted active:cursor-grabbing">
                            ⋮⋮
                        </span>
                        <span class="min-w-0 flex-1 text-sm text-bo-fg">
                            {{ element.name }}
                            <span class="ml-2 text-xs text-bo-muted">
                                {{ element.items.length }} поз.
                            </span>
                        </span>
                        <UiBadge v-if="element.is_accompanying" variant="warning" text="сопутствующая" />
                        <UiBadge
                            :variant="element.is_active ? 'success' : 'neutral'"
                            :text="element.is_active ? 'активна' : 'выключена'"
                        />
                        <div class="flex flex-wrap gap-1">
                            <UiButton
                                class="!h-9 !px-2.5"
                                title="Редактировать"
                                aria-label="Редактировать"
                                @click="openEdit(element)"
                            >
                                <ShellIcon name="edit" class="h-4 w-4" />
                            </UiButton>
                            <UiButton
                                class="!h-9 !px-2.5"
                                title="Удалить"
                                aria-label="Удалить"
                                @click="deleteTarget = element"
                            >
                                <ShellIcon name="trash" class="h-4 w-4" />
                            </UiButton>
                        </div>
                    </div>
                </template>
            </draggable>
            <div
                v-else
                class="divide-y divide-bo-border border border-bo-border"
            >
                <div
                    v-for="element in pageItems"
                    :key="element.id"
                    class="flex flex-wrap items-center gap-3 px-4 py-3.5 transition-colors hover:bg-white/[0.03]"
                >
                    <span class="min-w-0 flex-1 text-sm text-bo-fg">
                        {{ element.name }}
                        <span class="ml-2 text-xs text-bo-muted">
                            {{ element.items.length }} поз.
                        </span>
                    </span>
                    <UiBadge v-if="element.is_accompanying" variant="warning" text="сопутствующая" />
                    <UiBadge
                        :variant="element.is_active ? 'success' : 'neutral'"
                        :text="element.is_active ? 'активна' : 'выключена'"
                    />
                    <div class="flex flex-wrap gap-1">
                        <UiButton
                            class="!h-9 !px-2.5"
                            title="Редактировать"
                            aria-label="Редактировать"
                            @click="openEdit(element)"
                        >
                            <ShellIcon name="edit" class="h-4 w-4" />
                        </UiButton>
                        <UiButton
                            class="!h-9 !px-2.5"
                            title="Удалить"
                            aria-label="Удалить"
                            @click="deleteTarget = element"
                        >
                            <ShellIcon name="trash" class="h-4 w-4" />
                        </UiButton>
                    </div>
                </div>
            </div>
            <div v-if="!canReorder && total > 0" class="mt-6">
                <UiPagination
                    :page="page"
                    :per-page="PER_PAGE"
                    :total="total"
                    @update:page="page = $event"
                />
            </div>
        </template>

        <UiModal :open="modalOpen" :title="modalTitle" @close="modalOpen = false">
            <div class="space-y-5">
                <UiFormField label="Название" required :error="fieldErrors.name">
                    <UiInput v-model="form.name" />
                </UiFormField>
                <UiFormField label="Slug (необязательно)">
                    <UiInput v-model="form.slug" />
                </UiFormField>
                <UiFormField label="Порядок" :error="fieldErrors.sort_order">
                    <UiInput v-model="form.sort_order" type="number" />
                </UiFormField>
                <div class="flex flex-wrap gap-3">
                    <UiToggle v-model="form.is_active" label-on="Активна" label-off="Выключена" />
                    <UiToggle
                        v-model="form.is_accompanying"
                        label-on="Сопутствующая"
                        label-off="Обычная"
                    />
                </div>

                <UiFormField label="Позиции">
                    <ul
                        v-if="form.items.length > 0"
                        class="mb-3 divide-y divide-bo-border border border-bo-border"
                    >
                        <li
                            v-for="(item, index) in form.items"
                            :key="`${item.entry_kind}:${item.entry_id}`"
                            class="flex items-center gap-2 px-3 py-2 text-sm"
                        >
                            <span class="min-w-0 flex-1 truncate">{{ itemLabel(item) }}</span>
                            <UiButton
                                class="!h-8 !px-2"
                                :disabled="index === 0"
                                aria-label="Выше"
                                @click="moveItem(index, -1)"
                            >
                                ↑
                            </UiButton>
                            <UiButton
                                class="!h-8 !px-2"
                                :disabled="index === form.items.length - 1"
                                aria-label="Ниже"
                                @click="moveItem(index, 1)"
                            >
                                ↓
                            </UiButton>
                            <UiButton class="!h-8 !px-2" aria-label="Убрать" @click="removeItem(index)">
                                ×
                            </UiButton>
                        </li>
                    </ul>
                    <p v-else class="mb-3 text-sm text-bo-muted">
                        Позиций пока нет
                    </p>
                    <div class="flex gap-2">
                        <select
                            v-model="addKey"
                            class="min-w-0 flex-1 border border-bo-border bg-bo-field px-4 py-2.5 text-sm focus:border-bo-accent focus:outline-none"
                        >
                            <option value="">Добавить позицию…</option>
                            <option
                                v-for="option in availableOptions"
                                :key="option.key"
                                :value="option.key"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <UiButton :disabled="!addKey" @click="addItem">
                            Добавить
                        </UiButton>
                    </div>
                </UiFormField>
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
            title="Удаление категории"
            :message="confirmMessage"
            @confirm="confirmDelete"
            @cancel="deleteTarget = null"
        />
    </UiCard>
</template>
