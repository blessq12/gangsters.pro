<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import draggable from "vuedraggable";
import {
    getCatalog,
    getSet,
    storeSet,
    updateSet,
} from "../../../api/catalog.js";
import CatalogPricesEditor from "../components/catalog/CatalogPricesEditor.vue";
import CatalogProductImagesDraft from "../components/catalog/CatalogProductImagesDraft.vue";
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
const setId = computed(() => (isNew.value ? null : Number(route.params.id)));

const products = ref([]);
const lines = ref([]);
const prices = ref([{ type: "regular", amount: "" }]);
const draftImages = ref([]);
const deletedImageIds = ref([]);
const addProductQuery = ref("");
/** @type {import('vue').Ref<{ id: number, name: string }|null>} */
const addProductSelected = ref(null);
const addProductOpen = ref(false);
const addQuantity = ref("1");
const fieldErrors = reactive({});
const form = reactive({
    name: "",
    description: "",
    isActive: true,
});
/** @type {import('vue').Ref<string|null>} */
const sku = ref(null);

const title = computed(() => {
    if (isNew.value) {
        return "Новый набор";
    }
    const name = String(form.name ?? "").trim();
    return name !== "" ? name : "Набор";
});

const productSuggestions = computed(() => {
    const query = addProductQuery.value.trim().toLowerCase();
    if (query.length < 1) {
        return [];
    }
    const taken = new Set(lines.value.map((line) => Number(line.product_id)));
    return products.value
        .filter((row) => !taken.has(Number(row.id)))
        .filter((row) => String(row.name ?? "").toLowerCase().includes(query))
        .slice(0, 12);
});

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
}

function mapPrices(payload) {
    if (Array.isArray(payload?.prices) && payload.prices.length > 0) {
        return payload.prices.map((row) => ({
            type: row.type,
            amount: row.amount != null ? String(row.amount) : "",
        }));
    }
    const rows = [{ type: "regular", amount: payload?.price != null ? String(payload.price) : "" }];
    if (payload?.old_price != null) {
        rows.push({ type: "old", amount: String(payload.old_price) });
    }
    return rows;
}

function mapImages(payload) {
    if (!Array.isArray(payload?.images)) {
        return [];
    }
    return payload.images.map((row, index) => ({
        localKey: `existing-${row.id ?? index}`,
        id: row.id,
        url: row.url,
        focus_x: row.focus_x ?? 50,
        focus_y: row.focus_y ?? 50,
        isNew: false,
    }));
}

function applyDetail(payload) {
    form.name = payload?.name ?? "";
    form.description = payload?.description ?? "";
    sku.value = payload?.sku ?? null;
    form.isActive = (payload?.status ?? "active") === "active";
    prices.value = mapPrices(payload);
    draftImages.value = mapImages(payload);
    deletedImageIds.value = [];
    lines.value = Array.isArray(payload?.lines)
        ? payload.lines.map((line) => ({
            product_id: line.product_id,
            name: line.name,
            quantity: line.quantity,
        }))
        : [];
}

async function load() {
    clearErrors();
    try {
        const catalog = await getCatalog();
        products.value = catalog?.products ?? [];
    } catch {
        products.value = [];
    }

    if (isNew.value) {
        applyDetail(null);
        return;
    }
    try {
        const payload = await loadApi.run(() => getSet(setId.value));
        applyDetail(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить набор");
        router.push({ name: "owner-catalog" });
    }
}

function validate() {
    clearErrors();
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    const regular = prices.value.find((row) => row.type === "regular");
    if (!regular || regular.amount === "" || Number.isNaN(Number(regular.amount))) {
        fieldErrors.prices = "Нужна актуальная цена (regular)";
    }
    if (prices.value.some((row) => row.amount === "" || Number.isNaN(Number(row.amount)))) {
        fieldErrors.prices = fieldErrors.prices || "У каждой цены укажи сумму";
    }
    return Object.keys(fieldErrors).length === 0;
}

function buildPayload() {
    return {
        name: form.name.trim(),
        sku: sku.value,
        description: form.description.trim() || null,
        prices: prices.value.map((row) => ({
            type: row.type,
            amount: Number(row.amount),
        })),
        status: form.isActive ? "active" : "archived",
        lines: lines.value.map((line) => ({
            product_id: Number(line.product_id),
            quantity: Number(line.quantity),
        })),
    };
}

function buildImageBundle() {
    const existing = [];
    const newImages = [];
    draftImages.value.forEach((row, index) => {
        if (row.isNew && row.file) {
            newImages.push({
                file: row.file,
                focus_x: Number(row.focus_x ?? 50),
                focus_y: Number(row.focus_y ?? 50),
                sort_order: index,
            });
            return;
        }
        if (row.id != null) {
            existing.push({
                id: Number(row.id),
                focus_x: Number(row.focus_x ?? 50),
                focus_y: Number(row.focus_y ?? 50),
                sort_order: index,
            });
        }
    });
    return {
        newImages,
        existingImages: existing,
        deletedImageIds: [...deletedImageIds.value],
    };
}

async function save() {
    if (!validate()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    try {
        const payload = buildPayload();
        const images = buildImageBundle();
        const result = await saveApi.run(async () => (
            setId.value
                ? updateSet(setId.value, payload, images)
                : storeSet(payload, images)
        ));
        toast.success(setId.value ? "Сохранено" : "Создано");
        if (isNew.value && result?.id) {
            await router.replace({ name: "owner-catalog-set", params: { id: result.id } });
            return;
        }
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

function onAddProductInput(value) {
    addProductQuery.value = value;
    addProductSelected.value = null;
    addProductOpen.value = true;
}

function pickProduct(product) {
    addProductSelected.value = { id: Number(product.id), name: String(product.name ?? "") };
    addProductQuery.value = String(product.name ?? "");
    addProductOpen.value = false;
}

function addLine() {
    const product = addProductSelected.value;
    const quantity = Number(addQuantity.value);
    if (!product?.id || quantity < 1) {
        toast.error("Выбери товар и количество");
        return;
    }
    if (lines.value.some((line) => line.product_id === product.id)) {
        toast.error("Этот товар уже в составе");
        return;
    }
    lines.value.push({
        product_id: product.id,
        name: product.name,
        quantity,
    });
    addProductQuery.value = "";
    addProductSelected.value = null;
    addProductOpen.value = false;
    addQuantity.value = "1";
}

function removeLine(productId) {
    lines.value = lines.value.filter((line) => line.product_id !== productId);
}

function onProductSearchBlur() {
    window.setTimeout(() => {
        addProductOpen.value = false;
    }, 150);
}

watch(() => route.params.id, load);
onMounted(load);
</script>

<template>
    <div class="set-edit mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
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
                    label-on="Активен"
                    label-off="В архиве"
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
                    <UiFormField label="Описание">
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="mt-1 w-full border border-bo-border bg-bo-field px-3 py-2 text-sm"
                        />
                    </UiFormField>
                </div>
            </UiCard>

            <UiCard title="Цены">
                <CatalogPricesEditor v-model="prices" :error="fieldErrors.prices" />
            </UiCard>

            <UiCard title="Состав">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-end gap-3">
                        <div class="relative min-w-[220px] flex-1">
                            <UiFormField label="Товар">
                                <UiInput
                                    :model-value="addProductQuery"
                                    placeholder="Начни вводить название"
                                    @update:model-value="onAddProductInput"
                                    @focus="addProductOpen = true"
                                    @blur="onProductSearchBlur"
                                />
                            </UiFormField>
                            <ul
                                v-if="addProductOpen && addProductQuery.trim() && productSuggestions.length > 0"
                                class="absolute z-20 mt-1 max-h-56 w-full overflow-auto border border-bo-border bg-bo-field shadow-lg"
                            >
                                <li
                                    v-for="product in productSuggestions"
                                    :key="product.id"
                                >
                                    <button
                                        type="button"
                                        class="w-full px-3 py-2 text-left text-sm text-bo-fg hover:bg-white/[0.06]"
                                        @mousedown.prevent="pickProduct(product)"
                                    >
                                        {{ product.name }}
                                    </button>
                                </li>
                            </ul>
                            <p
                                v-else-if="addProductOpen && addProductQuery.trim() && productSuggestions.length === 0"
                                class="absolute z-20 mt-1 w-full border border-bo-border bg-bo-field px-3 py-2 text-sm text-bo-muted"
                            >
                                Ничего не найдено
                            </p>
                        </div>
                        <label class="w-28 text-sm text-bo-muted">
                            Кол-во
                            <UiInput v-model="addQuantity" type="number" />
                        </label>
                        <UiButton @click="addLine">
                            Добавить
                        </UiButton>
                    </div>

                    <draggable
                        v-model="lines"
                        item-key="product_id"
                        handle=".drag-handle"
                        class="space-y-2"
                    >
                        <template #item="{ element }">
                            <div class="flex flex-wrap items-center gap-3 border border-bo-border bg-bo-field/30 px-3 py-2">
                                <span class="drag-handle cursor-grab text-bo-muted">⋮⋮</span>
                                <span class="flex-1 text-sm">
                                    {{ element.name }}
                                </span>
                                <input
                                    v-model.number="element.quantity"
                                    type="number"
                                    min="1"
                                    class="w-20 border border-bo-border bg-bo-field px-2 py-1.5 text-sm"
                                >
                                <UiButton @click="removeLine(element.product_id)">
                                    Убрать
                                </UiButton>
                            </div>
                        </template>
                    </draggable>
                    <p v-if="lines.length === 0" class="text-sm text-bo-muted">
                        Пока пусто
                    </p>
                </div>
            </UiCard>

            <UiCard title="Фото">
                <CatalogProductImagesDraft
                    v-model="draftImages"
                    v-model:deleted-ids="deletedImageIds"
                />
            </UiCard>
        </div>
    </div>
</template>

<style scoped>
.set-edit :deep(section > header) {
    padding: 1rem 1.25rem 0.5rem;
    gap: 0.5rem;
}

.set-edit :deep(section > header h2) {
    font-size: 0.95rem;
}

.set-edit :deep(section > div.p-6) {
    padding: 1rem 1.25rem 1.25rem;
}

.set-edit :deep(.mb-2) {
    margin-bottom: 0.5rem;
}

.set-edit :deep(input.mt-2),
.set-edit :deep(.mt-2) {
    margin-top: 0.375rem;
}
</style>
