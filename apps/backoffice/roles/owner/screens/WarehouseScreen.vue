<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import {
    adjustStock,
    getStock,
    listIngredients,
    listWriteOffs,
    storeIngredient,
    storeWriteOff,
    updateIngredient,
} from "../../../api/warehouse.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();
const {
    page,
    perPage,
    total,
    items: pageRows,
    offset,
    resetPage,
    applyPayload,
} = usePagedList();

const tab = ref("ingredients");
const ingredientOptions = ref([]);
const ingredients = computed(() => (tab.value === "ingredients" ? pageRows.value : ingredientOptions.value));
const stock = computed(() => (tab.value === "stock" ? pageRows.value : []));
const writeOffs = computed(() => (tab.value === "writeoffs" ? pageRows.value : []));

const ingredientModalOpen = ref(false);
const editingIngredientId = ref(null);
const ingredientErrors = reactive({});
const ingredientForm = reactive({
    name: "",
    sku: "",
    unit: "",
});

const adjustModalOpen = ref(false);
const adjustErrors = reactive({});
const adjustForm = reactive({
    ingredient_id: "",
    delta: "",
});

const writeOffModalOpen = ref(false);
const writeOffErrors = reactive({});
const writeOffForm = reactive({
    ingredient_id: "",
    quantity: "",
    reason: "",
});

const tabs = [
    { id: "ingredients", label: "Ингредиенты" },
    { id: "stock", label: "Остатки" },
    { id: "writeoffs", label: "Списания" },
];

const ingredientColumns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "name", label: "Название" },
    { key: "unit", label: "Ед.", width: "80px" },
    { key: "actions", label: "", width: "120px" },
];

const stockColumns = [
    { key: "ingredient", label: "Ингредиент" },
    { key: "quantity", label: "Кол-во", width: "100px" },
    { key: "unit", label: "Ед.", width: "80px" },
    { key: "actions", label: "", width: "140px" },
];

const writeOffColumns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "ingredient", label: "Ингредиент" },
    { key: "quantity", label: "Кол-во", width: "90px" },
    { key: "reason", label: "Причина" },
    { key: "created_at", label: "Дата", width: "160px" },
];

const ingredientById = computed(() => {
    /** @type {Record<number|string, any>} */
    const map = {};
    ingredientOptions.value.forEach((item) => {
        map[item.id] = item;
    });
    return map;
});

const stockRows = computed(() =>
    stock.value.map((row) => {
        const ingredient = ingredientById.value[row.ingredient_id];
        return {
            ...row,
            ingredient: ingredient?.name ?? `#${row.ingredient_id}`,
            unit: ingredient?.unit ?? "—",
        };
    }),
);

const writeOffRows = computed(() =>
    writeOffs.value.map((row) => {
        const ingredient = ingredientById.value[row.ingredient_id];
        return {
            ...row,
            ingredient: ingredient?.name ?? `#${row.ingredient_id}`,
            created_at: formatDate(row.occurred_at),
        };
    }),
);

function formatDate(value) {
    if (!value) {
        return "—";
    }
    try {
        return new Date(value).toLocaleString("ru-RU");
    } catch {
        return String(value);
    }
}

function clearErrors(target) {
    Object.keys(target).forEach((key) => {
        delete target[key];
    });
}

async function loadIngredientOptions() {
    try {
        const payload = await listIngredients({ offset: 0, limit: 100 });
        ingredientOptions.value = Array.isArray(payload?.items) ? payload.items : [];
    } catch {
        ingredientOptions.value = [];
    }
}

async function loadTab() {
    try {
        const query = { offset: offset.value, limit: perPage };
        let payload;
        if (tab.value === "ingredients") {
            payload = await loadApi.run(() => listIngredients(query));
        } else if (tab.value === "stock") {
            payload = await loadApi.run(() => getStock(query));
        } else {
            payload = await loadApi.run(() => listWriteOffs(query));
        }
        applyPayload(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить склад");
    }
}

async function loadAll() {
    await Promise.all([loadIngredientOptions(), loadTab()]);
}

function openCreateIngredient() {
    editingIngredientId.value = null;
    ingredientForm.name = "";
    ingredientForm.sku = "";
    ingredientForm.unit = "";
    clearErrors(ingredientErrors);
    ingredientModalOpen.value = true;
}

function openEditIngredient(row) {
    editingIngredientId.value = row.id;
    ingredientForm.name = row.name ?? "";
    ingredientForm.sku = row.sku ?? "";
    ingredientForm.unit = row.unit ?? "";
    clearErrors(ingredientErrors);
    ingredientModalOpen.value = true;
}

function validateIngredient() {
    clearErrors(ingredientErrors);
    if (!String(ingredientForm.name).trim()) {
        ingredientErrors.name = "Обязательное поле";
    }
    if (!String(ingredientForm.unit).trim()) {
        ingredientErrors.unit = "Обязательное поле";
    }
    return Object.keys(ingredientErrors).length === 0;
}

async function saveIngredient() {
    if (!validateIngredient()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    const id = editingIngredientId.value;
    const body = {
        name: ingredientForm.name.trim(),
        unit: ingredientForm.unit.trim(),
        sku: ingredientForm.sku || null,
    };
    try {
        await saveApi.run(() =>
            id ? updateIngredient(id, body) : storeIngredient(body),
        );
        toast.success(id ? "Сохранено" : "Создано");
        ingredientModalOpen.value = false;
        await loadAll();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

function openAdjust(row) {
    adjustForm.ingredient_id = String(row.ingredient_id ?? "");
    adjustForm.delta = "";
    clearErrors(adjustErrors);
    adjustModalOpen.value = true;
}

function validateAdjust() {
    clearErrors(adjustErrors);
    if (!String(adjustForm.ingredient_id).trim()) {
        adjustErrors.ingredient_id = "Обязательное поле";
    }
    if (adjustForm.delta === "" || Number.isNaN(Number(adjustForm.delta))) {
        adjustErrors.delta = "Укажите delta";
    }
    return Object.keys(adjustErrors).length === 0;
}

async function saveAdjust() {
    if (!validateAdjust()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    try {
        await saveApi.run(() => adjustStock({
            ingredient_id: Number(adjustForm.ingredient_id),
            delta: Number(adjustForm.delta),
        }));
        toast.success("Остаток скорректирован");
        adjustModalOpen.value = false;
        await loadAll();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

function openWriteOff() {
    writeOffForm.ingredient_id = ingredientOptions.value[0] ? String(ingredientOptions.value[0].id) : "";
    writeOffForm.quantity = "";
    writeOffForm.reason = "";
    clearErrors(writeOffErrors);
    writeOffModalOpen.value = true;
}

function validateWriteOff() {
    clearErrors(writeOffErrors);
    if (!String(writeOffForm.ingredient_id).trim()) {
        writeOffErrors.ingredient_id = "Обязательное поле";
    }
    if (
        writeOffForm.quantity === ""
        || Number.isNaN(Number(writeOffForm.quantity))
        || Number(writeOffForm.quantity) <= 0
    ) {
        writeOffErrors.quantity = " > 0";
    }
    if (!String(writeOffForm.reason).trim()) {
        writeOffErrors.reason = "Обязательное поле";
    }
    return Object.keys(writeOffErrors).length === 0;
}

async function saveWriteOff() {
    if (!validateWriteOff()) {
        toast.error("Проверьте обязательные поля");
        return;
    }
    try {
        await saveApi.run(() => storeWriteOff({
            ingredient_id: Number(writeOffForm.ingredient_id),
            quantity: Number(writeOffForm.quantity),
            reason: writeOffForm.reason.trim(),
        }));
        toast.success("Списание создано");
        writeOffModalOpen.value = false;
        await loadAll();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

watch(tab, () => {
    resetPage();
    loadTab();
});
watch(page, loadTab);
onMounted(loadAll);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Склад">
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

            <div v-if="tab === 'ingredients'" class="space-y-5">
                <div class="flex justify-end">
                    <UiButton @click="openCreateIngredient">+ Ингредиент</UiButton>
                </div>
                <UiTable
                    :columns="ingredientColumns"
                    :rows="pageRows"
                    :loading="loadApi.loading"
                    empty-text="Нет ингредиентов"
                >
                    <template #cell-actions="{ row }">
                        <UiButton @click.stop="openEditIngredient(row)">Редактировать</UiButton>
                    </template>
                </UiTable>
            </div>

            <div v-else-if="tab === 'stock'" class="space-y-5">
                <UiTable
                    :columns="stockColumns"
                    :rows="stockRows"
                    :loading="loadApi.loading"
                    empty-text="Нет остатков"
                >
                    <template #cell-actions="{ row }">
                        <UiButton @click.stop="openAdjust(row)">Корректировать</UiButton>
                    </template>
                </UiTable>
            </div>

            <div v-else class="space-y-5">
                <div class="flex justify-end">
                    <UiButton @click="openWriteOff">+ Списание</UiButton>
                </div>
                <UiTable
                    :columns="writeOffColumns"
                    :rows="writeOffRows"
                    :loading="loadApi.loading"
                    empty-text="Нет списаний"
                />
            </div>

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
            :open="ingredientModalOpen"
            :title="editingIngredientId ? 'Редактировать ингредиент' : 'Новый ингредиент'"
            @close="ingredientModalOpen = false"
        >
            <div class="space-y-5">
                <UiFormField label="Название" required :error="ingredientErrors.name">
                    <UiInput v-model="ingredientForm.name" />
                </UiFormField>
                <UiFormField label="Ед. изм." required :error="ingredientErrors.unit">
                    <UiInput v-model="ingredientForm.unit" />
                </UiFormField>
                <UiFormField label="SKU">
                    <UiInput v-model="ingredientForm.sku" />
                </UiFormField>
            </div>
            <template #footer>
                <UiButton @click="ingredientModalOpen = false">Отмена</UiButton>
                <UiButton :disabled="saveApi.loading" @click="saveIngredient">Сохранить</UiButton>
            </template>
        </UiModal>

        <UiModal
            :open="adjustModalOpen"
            title="Корректировка остатка"
            @close="adjustModalOpen = false"
        >
            <div class="space-y-5">
                <UiFormField label="ingredient_id" required :error="adjustErrors.ingredient_id">
                    <select
                        v-model="adjustForm.ingredient_id"
                        class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                    >
                        <option
                            v-for="item in ingredientOptions"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.id }} — {{ item.name }}
                        </option>
                    </select>
                </UiFormField>
                <UiFormField label="Delta (+/−)" required :error="adjustErrors.delta">
                    <UiInput v-model="adjustForm.delta" type="number" />
                </UiFormField>
            </div>
            <template #footer>
                <UiButton @click="adjustModalOpen = false">Отмена</UiButton>
                <UiButton :disabled="saveApi.loading" @click="saveAdjust">Сохранить</UiButton>
            </template>
        </UiModal>

        <UiModal
            :open="writeOffModalOpen"
            title="Новое списание"
            @close="writeOffModalOpen = false"
        >
            <div class="space-y-5">
                <UiFormField label="Ингредиент" required :error="writeOffErrors.ingredient_id">
                    <select
                        v-model="writeOffForm.ingredient_id"
                        class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                    >
                        <option
                            v-for="item in ingredientOptions"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.id }} — {{ item.name }}
                        </option>
                    </select>
                </UiFormField>
                <UiFormField label="Количество" required :error="writeOffErrors.quantity">
                    <UiInput v-model="writeOffForm.quantity" type="number" />
                </UiFormField>
                <UiFormField label="Причина" required :error="writeOffErrors.reason">
                    <UiInput v-model="writeOffForm.reason" />
                </UiFormField>
            </div>
            <template #footer>
                <UiButton @click="writeOffModalOpen = false">Отмена</UiButton>
                <UiButton :disabled="saveApi.loading" @click="saveWriteOff">Сохранить</UiButton>
            </template>
        </UiModal>
    </div>
</template>
