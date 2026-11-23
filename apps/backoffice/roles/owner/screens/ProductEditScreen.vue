<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    getProduct,
    storeProduct,
    updateProduct,
} from "../../../api/catalog.js";
import { listIngredients } from "../../../api/warehouse.js";
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

const draftImages = ref([]);
const deletedImageIds = ref([]);
/** @type {import('vue').Ref<Array<{ id: number, name: string, unit: string }>>} */
const warehouseIngredients = ref([]);
/** @type {import('vue').Ref<Array<{ ingredient_ref: string, quantity: string, unit: string }>>} */
const techCardLines = ref([]);
const addIngredientId = ref("");
const fieldErrors = reactive({});
const form = reactive({
    name: "",
    weight: "",
    isActive: true,
    isRoll: false,
    description: "",
    ingredients: [],
    calories: "",
    proteins: "",
    fats: "",
    carbs: "",
    basis: "per_100g",
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

const ingredientById = computed(
    () => new Map(warehouseIngredients.value.map((row) => [String(row.id), row])),
);

const availableIngredients = computed(() => {
    const used = new Set(techCardLines.value.map((row) => row.ingredient_ref));
    return warehouseIngredients.value.filter((row) => !used.has(String(row.id)));
});

function clearErrors() {
    Object.keys(fieldErrors).forEach((key) => {
        delete fieldErrors[key];
    });
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

function ingredientLabel(ref) {
    const row = ingredientById.value.get(String(ref));
    if (!row) {
        return `ref:${ref}`;
    }
    return row.name;
}

function applyDetail(payload) {
    form.name = payload?.name ?? "";
    sku.value = payload?.sku ?? null;
    form.weight = payload?.weight != null ? String(payload.weight) : "";
    form.isActive = (payload?.status ?? "active") === "active";
    form.isRoll = Boolean(payload?.is_roll);
    form.description = payload?.description ?? "";
    form.ingredients = Array.isArray(payload?.ingredients)
        ? payload.ingredients.map((item) => String(item).trim()).filter(Boolean)
        : [];
    form.calories = payload?.nutrition?.calories != null ? String(payload.nutrition.calories) : "";
    form.proteins = payload?.nutrition?.proteins != null ? String(payload.nutrition.proteins) : "";
    form.fats = payload?.nutrition?.fats != null ? String(payload.nutrition.fats) : "";
    form.carbs = payload?.nutrition?.carbs != null ? String(payload.nutrition.carbs) : "";
    form.basis = payload?.nutrition?.basis ?? "per_100g";
    draftImages.value = mapImages(payload);
    deletedImageIds.value = [];
    techCardLines.value = Array.isArray(payload?.tech_card?.lines)
        ? payload.tech_card.lines.map((row) => ({
            ingredient_ref: String(row.ingredient_ref ?? ""),
            quantity: row.quantity != null ? String(row.quantity) : "",
            unit: String(row.unit ?? ""),
        })).filter((row) => row.ingredient_ref !== "")
        : [];
    addIngredientId.value = "";
}

async function loadWarehouseIngredients() {
    const payload = await listIngredients({ offset: 0, limit: 500 });
    warehouseIngredients.value = Array.isArray(payload?.items) ? payload.items : [];
}

async function load() {
    clearErrors();
    if (isNew.value) {
        applyDetail(null);
        try {
            await loadWarehouseIngredients();
        } catch (err) {
            toast.error(err instanceof Error ? err.message : "Не удалось загрузить ингредиенты");
        }
        return;
    }
    try {
        const [payload] = await loadApi.run(() => Promise.all([
            getProduct(productId.value),
            loadWarehouseIngredients(),
        ]));
        applyDetail(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить товар");
        router.push({ name: "owner-menu" });
    }
}

function addTechLine() {
    const id = String(addIngredientId.value || "");
    const ingredient = ingredientById.value.get(id);
    if (!ingredient) {
        return;
    }
    techCardLines.value.push({
        ingredient_ref: id,
        quantity: "1",
        unit: String(ingredient.unit || "шт"),
    });
    addIngredientId.value = "";
}

function removeTechLine(index) {
    techCardLines.value.splice(index, 1);
}

function validate() {
    clearErrors();
    if (!String(form.name).trim()) {
        fieldErrors.name = "Обязательное поле";
    }
    for (const [index, row] of techCardLines.value.entries()) {
        const qty = Number(String(row.quantity).replace(",", "."));
        if (!row.ingredient_ref || !row.unit.trim() || Number.isNaN(qty) || qty <= 0) {
            fieldErrors.tech_card = `Строка техкарты #${index + 1}: нужен ингредиент, кол-во > 0 и единица`;
            break;
        }
    }
    return Object.keys(fieldErrors).length === 0;
}

function buildPayload() {
    const nutritionEmpty = [form.calories, form.proteins, form.fats, form.carbs]
        .every((value) => value === "");

    return {
        name: form.name.trim(),
        sku: sku.value,
        weight: form.weight === "" ? null : Number(form.weight),
        status: form.isActive ? "active" : "archived",
        is_roll: Boolean(form.isRoll),
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
        tech_card: {
            lines: techCardLines.value.map((row) => ({
                ingredient_ref: String(row.ingredient_ref),
                quantity: Number(String(row.quantity).replace(",", ".")),
                unit: String(row.unit).trim(),
            })),
        },
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
                    </div>
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

            <UiCard title="Техкарта">
                <UiFormField :error="fieldErrors.tech_card">
                    <ul
                        v-if="techCardLines.length > 0"
                        class="mb-3 divide-y divide-bo-border border border-bo-border"
                    >
                        <li
                            v-for="(row, index) in techCardLines"
                            :key="`${row.ingredient_ref}-${index}`"
                            class="flex flex-wrap items-end gap-2 px-3 py-2.5"
                        >
                            <div class="min-w-[10rem] flex-1 text-sm text-bo-fg">
                                {{ ingredientLabel(row.ingredient_ref) }}
                            </div>
                            <div class="w-28">
                                <UiInput v-model="row.quantity" type="number" placeholder="Кол-во" />
                            </div>
                            <div class="w-24">
                                <UiInput v-model="row.unit" placeholder="Ед." />
                            </div>
                            <UiButton class="!h-9 !px-2.5" @click="removeTechLine(index)">
                                ×
                            </UiButton>
                        </li>
                    </ul>
                    <div class="flex flex-wrap gap-2">
                        <select
                            v-model="addIngredientId"
                            class="min-w-0 flex-1 border border-bo-border bg-bo-field px-4 py-2.5 text-sm focus:border-bo-accent focus:outline-none"
                        >
                            <option value="">Добавить ингредиент…</option>
                            <option
                                v-for="item in availableIngredients"
                                :key="item.id"
                                :value="String(item.id)"
                            >
                                {{ item.name }}{{ item.unit ? ` · ${item.unit}` : "" }}
                            </option>
                        </select>
                        <UiButton :disabled="!addIngredientId" @click="addTechLine">
                            Добавить
                        </UiButton>
                    </div>
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
