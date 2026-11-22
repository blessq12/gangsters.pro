<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    getCatalog,
    getProduct,
    storeProduct,
    updateProduct,
} from "../../../api/catalog.js";
import CatalogPricesEditor from "../components/catalog/CatalogPricesEditor.vue";
import CatalogProductImagesDraft from "../components/catalog/CatalogProductImagesDraft.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiChipInput from "../../../shell/components/ui/UiChipInput.vue";
import UiToggle from "../../../shell/components/ui/UiToggle.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const isNew = computed(() => route.params.id === "new");
const productId = computed(() => (isNew.value ? null : Number(route.params.id)));

const tags = ref([]);
const prices = ref([{ type: "regular", amount: "" }]);
const draftImages = ref([]);
const deletedImageIds = ref([]);
const fieldErrors = reactive({});
const form = reactive({
    name: "",
    weight: "",
    isActive: true,
    isRoll: false,
    isComplement: false,
    description: "",
    ingredients: [],
    calories: "",
    proteins: "",
    fats: "",
    carbs: "",
    basis: "per_100g",
    tag_ids: [],
});
/** @type {import('vue').Ref<string|null>} */
const sku = ref(null);

const title = computed(() => {
    if (isNew.value) {
        return "Новый товар";
    }
    const name = String(form.name ?? "").trim();
    return name !== "" ? name : "Товар";
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
    sku.value = payload?.sku ?? null;
    form.weight = payload?.weight != null ? String(payload.weight) : "";
    form.isActive = (payload?.status ?? "active") === "active";
    form.isRoll = Boolean(payload?.is_roll);
    form.isComplement = Boolean(payload?.is_complement);
    form.description = payload?.description ?? "";
    form.ingredients = Array.isArray(payload?.ingredients)
        ? payload.ingredients.map((item) => String(item).trim()).filter(Boolean)
        : [];
    form.calories = payload?.nutrition?.calories != null ? String(payload.nutrition.calories) : "";
    form.proteins = payload?.nutrition?.proteins != null ? String(payload.nutrition.proteins) : "";
    form.fats = payload?.nutrition?.fats != null ? String(payload.nutrition.fats) : "";
    form.carbs = payload?.nutrition?.carbs != null ? String(payload.nutrition.carbs) : "";
    form.basis = payload?.nutrition?.basis ?? "per_100g";
    form.tag_ids = Array.isArray(payload?.tag_ids) ? [...payload.tag_ids] : [];
    prices.value = mapPrices(payload);
    draftImages.value = mapImages(payload);
    deletedImageIds.value = [];
}

async function load() {
    clearErrors();
    try {
        const catalog = await getCatalog();
        tags.value = catalog?.tags ?? [];
    } catch {
        tags.value = [];
    }

    if (isNew.value) {
        applyDetail(null);
        return;
    }
    try {
        const payload = await loadApi.run(() => getProduct(productId.value));
        applyDetail(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить товар");
        router.push({ name: "owner-menu" });
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
    const nutritionEmpty = [form.calories, form.proteins, form.fats, form.carbs]
        .every((value) => value === "");

    return {
        name: form.name.trim(),
        sku: sku.value,
        prices: prices.value.map((row) => ({
            type: row.type,
            amount: Number(row.amount),
        })),
        weight: form.weight === "" ? null : Number(form.weight),
        status: form.isActive ? "active" : "archived",
        is_roll: Boolean(form.isRoll),
        is_complement: Boolean(form.isComplement),
        description: form.description.trim() || null,
        ingredients: form.ingredients.map((item) => String(item).trim()).filter(Boolean),
        nutrition: nutritionEmpty
            ? null
            : {
                calories: Number(form.calories || 0),
                proteins: Number(form.proteins || 0),
                fats: Number(form.fats || 0),
                carbs: Number(form.carbs || 0),
                basis: form.basis || "per_100g",
            },
        tag_ids: form.tag_ids.map(Number),
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
            productId.value
                ? updateProduct(productId.value, payload, images)
                : storeProduct(payload, images)
        ));
        toast.success(productId.value ? "Сохранено" : "Создано");
        if (isNew.value && result?.id) {
            await router.replace({ name: "owner-catalog-product", params: { id: result.id } });
            return;
        }
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

function toggleTag(id) {
    const num = Number(id);
    if (form.tag_ids.includes(num)) {
        form.tag_ids = form.tag_ids.filter((value) => value !== num);
        return;
    }
    form.tag_ids = [...form.tag_ids, num];
}

watch(() => route.params.id, load);
onMounted(load);
</script>

<template>
    <div class="product-edit mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <UiButton class="!bg-white/10 !text-bo-fg" @click="router.push({ name: 'owner-menu' })">
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
                    <div class="flex flex-wrap gap-6">
                        <UiToggle
                            v-model="form.isRoll"
                            label-on="Ролл"
                            label-off="Не ролл"
                            :disabled="loadApi.loading"
                        />
                        <UiToggle
                            v-model="form.isComplement"
                            label-on="Комплектный"
                            label-off="Не комплектный"
                            :disabled="loadApi.loading"
                        />
                    </div>
                </div>
            </UiCard>

            <UiCard title="Справочные цены">
                <p class="mb-3 text-sm text-bo-muted">
                    Справочные цены каталога. Продажная цена задаётся в меню филиала.
                </p>
                <CatalogPricesEditor v-model="prices" :error="fieldErrors.prices" />
            </UiCard>

            <UiCard title="Теги">
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="tag in tags"
                        :key="tag.id"
                        type="button"
                        class="h-8 px-2.5 text-sm transition-colors"
                        :class="form.tag_ids.includes(tag.id)
                            ? 'bg-bo-accent text-white'
                            : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08]'"
                        @click="toggleTag(tag.id)"
                    >
                        {{ tag.label || tag.code }}
                    </button>
                    <p v-if="tags.length === 0" class="text-sm text-bo-muted">
                        Тегов пока нет
                    </p>
                </div>
            </UiCard>

            <UiCard title="Состав">
                <UiFormField label="Ингредиенты">
                    <UiChipInput
                        v-model="form.ingredients"
                        placeholder="Введи и нажми Enter"
                    />
                </UiFormField>
            </UiCard>

            <UiCard title="Пищевая ценность">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <UiFormField label="Вес (г)">
                        <UiInput v-model="form.weight" type="number" />
                    </UiFormField>
                    <UiFormField label="База КБЖУ">
                        <select
                            v-model="form.basis"
                            class="mt-1 h-11 w-full border border-bo-border bg-bo-field px-3 text-sm"
                        >
                            <option value="per_100g">на 100 г</option>
                            <option value="per_portion">на порцию</option>
                        </select>
                    </UiFormField>
                    <UiFormField label="Ккал">
                        <UiInput v-model="form.calories" type="number" />
                    </UiFormField>
                    <UiFormField label="Белки">
                        <UiInput v-model="form.proteins" type="number" />
                    </UiFormField>
                    <UiFormField label="Жиры">
                        <UiInput v-model="form.fats" type="number" />
                    </UiFormField>
                    <UiFormField label="Углеводы">
                        <UiInput v-model="form.carbs" type="number" />
                    </UiFormField>
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
.product-edit :deep(section > header) {
    padding: 1rem 1.25rem 0.5rem;
    gap: 0.5rem;
}

.product-edit :deep(section > header h2) {
    font-size: 0.95rem;
}

.product-edit :deep(section > div.p-6) {
    padding: 1rem 1.25rem 1.25rem;
}

.product-edit :deep(.mb-2) {
    margin-bottom: 0.5rem;
}

.product-edit :deep(input.mt-2),
.product-edit :deep(.mt-2) {
    margin-top: 0.375rem;
}
</style>
