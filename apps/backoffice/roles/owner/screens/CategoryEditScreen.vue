<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import draggable from "vuedraggable";
import {
    getCatalog,
    getCategory,
    storeCategory,
    updateCategory,
} from "../../../api/catalog.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiToggle from "../../../shell/components/ui/UiToggle.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const isNew = computed(() => route.params.id === "new");
const categoryId = computed(() => (isNew.value ? null : Number(route.params.id)));

const catalogItems = ref([]);
const items = ref([]);
const addItemQuery = ref("");
/** @type {import('vue').Ref<{ id: number, kind: string, name: string, key: string }|null>} */
const addItemSelected = ref(null);
const addItemOpen = ref(false);
const fieldErrors = reactive({});
const form = reactive({
    name: "",
    isActive: true,
    isAccompanying: false,
});

const title = computed(() => {
    if (isNew.value) {
        return "Новая категория";
    }
    const name = String(form.name ?? "").trim();
    return name !== "" ? name : "Категория";
});

const itemSuggestions = computed(() => {
    const query = addItemQuery.value.trim().toLowerCase();
    if (query.length < 1) {
        return [];
    }
    const taken = new Set(items.value.map((row) => Number(row.id)));
    return catalogItems.value
        .filter((row) => !taken.has(Number(row.id)))
        .filter((row) => String(row.name ?? "").toLowerCase().includes(query))
        .slice(0, 12);
});

function kindLabel(kind) {
    if (kind === "set") {
        return "Набор";
    }
    return "Товар";
}

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function buildCatalogOptions(catalog) {
    const products = (catalog?.products ?? []).map((row) => ({
        id: row.id,
        kind: "product",
        name: row.name,
        key: `product:${row.id}`,
    }));
    const sets = (catalog?.sets ?? []).map((row) => ({
        id: row.id,
        kind: "set",
        name: row.name,
        key: `set:${row.id}`,
    }));
    return [...products, ...sets];
}

function applyDetail(payload) {
    form.name = payload?.name ?? "";
    form.isActive = payload?.is_active ?? true;
    form.isAccompanying = payload?.is_accompanying ?? false;
    items.value = Array.isArray(payload?.items)
        ? payload.items.map((row) => ({
            id: row.id,
            kind: row.kind,
            name: row.name,
            sort_order: row.sort_order,
        }))
        : [];
}

async function load() {
    clearErrors();
    try {
        const catalog = await getCatalog();
        catalogItems.value = buildCatalogOptions(catalog);
    } catch {
        catalogItems.value = [];
    }

    if (isNew.value) {
        applyDetail(null);
        return;
    }
    try {
        const payload = await loadApi.run(() => getCategory(categoryId.value));
        applyDetail(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить категорию");
        router.push({ name: "owner-catalog" });
    }
}

function validate() {
    clearErrors();
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    return Object.keys(fieldErrors).length === 0;
}

async function save() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    try {
        const body = {
            name: form.name.trim(),
            is_active: Boolean(form.isActive),
            is_accompanying: Boolean(form.isAccompanying),
            items: items.value.map((row, index) => ({
                item_id: Number(row.id),
                sort_order: index,
            })),
        };
        const result = await saveApi.run(async () => (
            categoryId.value ? updateCategory(categoryId.value, body) : storeCategory(body)
        ));
        toast.success(categoryId.value ? "Сохранено" : "Создано");
        if (isNew.value && result?.id) {
            await router.replace({ name: "owner-catalog-category", params: { id: result.id } });
            return;
        }
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

function onAddItemInput(value) {
    addItemQuery.value = value;
    addItemSelected.value = null;
    addItemOpen.value = true;
}

function pickItem(option) {
    addItemSelected.value = {
        id: Number(option.id),
        kind: String(option.kind),
        name: String(option.name ?? ""),
        key: String(option.key),
    };
    addItemQuery.value = String(option.name ?? "");
    addItemOpen.value = false;
}

function addItem() {
    const option = addItemSelected.value;
    if (!option?.id) {
        toast.error("Выбери позицию");
        return;
    }
    if (items.value.some((row) => row.id === option.id)) {
        toast.error("Уже в категории");
        return;
    }
    items.value.push({
        id: option.id,
        kind: option.kind,
        name: option.name,
        sort_order: items.value.length,
    });
    addItemQuery.value = "";
    addItemSelected.value = null;
    addItemOpen.value = false;
}

function removeItem(id) {
    items.value = items.value.filter((row) => row.id !== id);
}

function onItemSearchBlur() {
    window.setTimeout(() => {
        addItemOpen.value = false;
    }, 150);
}

watch(() => route.params.id, load);
onMounted(load);
</script>

<template>
    <div class="category-edit mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <UiButton class="!bg-white/10 !text-bo-fg" @click="router.push({ name: 'owner-catalog' })">
                    К каталогу
                </UiButton>
                <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                    {{ title }}
                </h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <UiToggle
                    v-model="form.isActive"
                    label-on="Активна"
                    label-off="Неактивна"
                    :disabled="loadApi.loading"
                />
                <UiButton :disabled="saveApi.loading || loadApi.loading" @click="save">
                    Сохранить
                </UiButton>
            </div>
        </div>

        <p v-if="loadApi.loading" class="text-sm text-bo-muted">
            Загрузка…
        </p>

        <div v-else class="space-y-4">
            <UiCard title="Основное">
                <div class="space-y-4">
                    <UiFormField label="Название" required :error="fieldErrors.name">
                        <UiInput v-model="form.name" />
                    </UiFormField>
                    <UiToggle
                        v-model="form.isAccompanying"
                        label-on="Сопутствующая"
                        label-off="Обычная"
                        :disabled="loadApi.loading"
                    />
                </div>
            </UiCard>

            <UiCard title="Состав">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-end gap-3">
                        <div class="relative min-w-[220px] flex-1">
                            <UiFormField label="Товар или набор">
                                <UiInput
                                    :model-value="addItemQuery"
                                    placeholder="Начни вводить название"
                                    @update:model-value="onAddItemInput"
                                    @focus="addItemOpen = true"
                                    @blur="onItemSearchBlur"
                                />
                            </UiFormField>
                            <ul
                                v-if="addItemOpen && addItemQuery.trim() && itemSuggestions.length > 0"
                                class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-bo-border bg-bo-field shadow-lg"
                            >
                                <li
                                    v-for="option in itemSuggestions"
                                    :key="option.key"
                                >
                                    <button
                                        type="button"
                                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-bo-fg hover:bg-white/[0.06]"
                                        @mousedown.prevent="pickItem(option)"
                                    >
                                        <span class="shrink-0 text-xs uppercase text-bo-muted">
                                            {{ kindLabel(option.kind) }}
                                        </span>
                                        <span>{{ option.name }}</span>
                                    </button>
                                </li>
                            </ul>
                            <p
                                v-else-if="addItemOpen && addItemQuery.trim() && itemSuggestions.length === 0"
                                class="absolute z-20 mt-1 w-full border border-bo-border bg-bo-field px-3 py-2 text-sm text-bo-muted"
                            >
                                Ничего не найдено
                            </p>
                        </div>
                        <UiButton @click="addItem">
                            Добавить
                        </UiButton>
                    </div>

                    <draggable
                        v-model="items"
                        item-key="id"
                        handle=".drag-handle"
                        class="space-y-2"
                    >
                        <template #item="{ element }">
                            <div class="flex flex-wrap items-center gap-3 border border-bo-border bg-bo-field/30 px-3 py-2">
                                <span class="drag-handle cursor-grab text-bo-muted">⋮⋮</span>
                                <span class="rounded bg-white/10 px-2 py-0.5 text-xs uppercase text-bo-muted">
                                    {{ kindLabel(element.kind) }}
                                </span>
                                <span class="flex-1 text-sm">
                                    {{ element.name }}
                                </span>
                                <UiButton @click="removeItem(element.id)">
                                    Убрать
                                </UiButton>
                            </div>
                        </template>
                    </draggable>
                    <p v-if="items.length === 0" class="text-sm text-bo-muted">
                        Пока пусто
                    </p>
                </div>
            </UiCard>
        </div>
    </div>
</template>

<style scoped>
.category-edit :deep(section > header) {
    padding: 1rem 1.25rem 0.5rem;
    gap: 0.5rem;
}

.category-edit :deep(section > header h2) {
    font-size: 0.95rem;
}

.category-edit :deep(section > div.p-6) {
    padding: 1rem 1.25rem 1.25rem;
}

.category-edit :deep(.mb-2) {
    margin-bottom: 0.5rem;
}

.category-edit :deep(input.mt-2),
.category-edit :deep(.mt-2) {
    margin-top: 0.375rem;
}
</style>
