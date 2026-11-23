<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    getBranchStock,
    listBranchWriteOffs,
    listIngredients,
} from "../../../api/warehouse.js";
import OwnerBranchSelector from "../components/branch/OwnerBranchSelector.vue";
import { useOwnerBranchContext } from "../composables/useOwnerBranchContext.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useClientList } from "../../../shell/composables/useClientList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const PAGE_LIMIT = 100;

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const { selectedBranchId, ensureLoaded } = useOwnerBranchContext();

ensureLoaded();

const contexts = [
    { id: "catalog", label: "Справочник" },
    { id: "branch", label: "Склад филиала" },
];
const contextIds = contexts.map((item) => item.id);

const branchSections = [
    { id: "stock", label: "Остатки" },
    { id: "writeoffs", label: "Списания" },
];
const branchSectionIds = branchSections.map((item) => item.id);

const context = computed(() =>
    contextIds.includes(String(route.query.ctx)) ? String(route.query.ctx) : "catalog",
);

const branchSection = computed(() =>
    branchSectionIds.includes(String(route.query.section))
        ? String(route.query.section)
        : "stock",
);

function setContext(id) {
    if (context.value === id) {
        return;
    }
    if (id === "catalog") {
        router.replace({ name: "owner-warehouse", query: {} });
        return;
    }
    router.replace({
        name: "owner-warehouse",
        query: { ctx: "branch", section: "stock" },
    });
}

function setBranchSection(id) {
    if (branchSection.value === id) {
        return;
    }
    router.replace({
        name: "owner-warehouse",
        query: { ctx: "branch", section: id },
    });
}

const allIngredients = ref([]);
/** @type {import('vue').Ref<Map<number, number>>} */
const quantityByIngredientId = ref(new Map());
const allWriteOffs = ref([]);

const {
    searchQuery: catalogSearch,
    page: catalogPage,
    perPage: catalogPerPage,
    total: catalogTotal,
    pageItems: catalogRows,
    setItems: setCatalogRows,
} = useClientList({
    perPage: 20,
    getHaystack: (row) => `${row.name ?? ""} ${row.unit ?? ""}`,
});

const {
    searchQuery: stockSearch,
    page: stockPage,
    perPage: stockPerPage,
    total: stockTotal,
    pageItems: stockRows,
    setItems: setStockRows,
} = useClientList({
    perPage: 20,
    getHaystack: (row) => `${row.name ?? ""} ${row.unit ?? ""}`,
});

const {
    searchQuery: writeOffsSearch,
    page: writeOffsPage,
    perPage: writeOffsPerPage,
    total: writeOffsTotal,
    pageItems: writeOffRows,
    setItems: setWriteOffItems,
} = useClientList({
    perPage: 20,
    getHaystack: (row) => `${row.ingredient_name ?? ""} ${row.reason ?? ""}`,
});

const catalogColumns = [
    { key: "name", label: "Название" },
    { key: "unit", label: "Ед.", width: "80px" },
];

const stockColumns = [
    { key: "name", label: "Ингредиент" },
    { key: "quantity", label: "Остаток", width: "110px" },
    { key: "unit", label: "Ед.", width: "80px" },
];

const writeOffColumns = [
    { key: "ingredient_name", label: "Ингредиент" },
    { key: "quantity", label: "Кол-во", width: "90px" },
    { key: "reason", label: "Причина" },
    { key: "occurred_at", label: "Когда", width: "170px" },
];

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

/**
 * @param {(query: { offset: number, limit: number }) => Promise<{ items?: unknown[], total?: number }>} fetchPage
 */
async function fetchAllPages(fetchPage) {
    const result = [];
    let total = 0;
    do {
        const payload = await fetchPage({ offset: result.length, limit: PAGE_LIMIT });
        const items = Array.isArray(payload?.items) ? payload.items : [];
        total = Number(payload?.total ?? 0);
        result.push(...items);
        if (items.length === 0) {
            break;
        }
    } while (result.length < total);
    return result;
}

function rebuildCatalog() {
    setCatalogRows(allIngredients.value.map((item) => ({
        id: Number(item.id),
        name: item.name,
        unit: item.unit ?? "",
    })));
}

function rebuildBranchLists() {
    const qtyMap = quantityByIngredientId.value;
    setStockRows(allIngredients.value.map((item) => ({
        id: Number(item.id),
        name: item.name,
        unit: item.unit ?? "",
        quantity: qtyMap.get(Number(item.id)) ?? 0,
    })));

    const nameById = new Map(
        allIngredients.value.map((item) => [Number(item.id), item.name]),
    );
    setWriteOffItems(allWriteOffs.value.map((row) => ({
        ...row,
        ingredient_id: Number(row.ingredient_id),
        ingredient_name: nameById.get(Number(row.ingredient_id)) ?? `#${row.ingredient_id}`,
        occurred_at: formatDate(row.occurred_at),
    })));
}

async function loadCatalog() {
    try {
        const ingredients = await loadApi.run(() => fetchAllPages(listIngredients));
        allIngredients.value = ingredients;
        rebuildCatalog();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить справочник");
    }
}

async function loadBranch() {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        quantityByIngredientId.value = new Map();
        allWriteOffs.value = [];
        rebuildBranchLists();
        return;
    }
    try {
        const [ingredients, stockLevels, writeOffs] = await loadApi.run(() => Promise.all([
            fetchAllPages(listIngredients),
            fetchAllPages((query) => getBranchStock(branchId, query)),
            fetchAllPages((query) => listBranchWriteOffs(branchId, query)),
        ]));
        allIngredients.value = ingredients;
        const qty = new Map();
        for (const row of stockLevels) {
            qty.set(Number(row.ingredient_id), Number(row.quantity ?? 0));
        }
        quantityByIngredientId.value = qty;
        allWriteOffs.value = writeOffs;
        rebuildBranchLists();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить склад филиала");
    }
}

async function loadCurrent() {
    if (context.value === "catalog") {
        await loadCatalog();
        return;
    }
    await loadBranch();
}

function openCreateIngredient() {
    router.push({ name: "owner-warehouse-ingredient", params: { id: "new" } });
}

function openIngredient(row) {
    router.push({ name: "owner-warehouse-ingredient", params: { id: row.id } });
}

function openBranchStockItem(row) {
    if (!selectedBranchId.value) {
        return;
    }
    router.push({
        name: "owner-warehouse-branch-item",
        params: {
            branchId: selectedBranchId.value,
            id: row.id,
        },
    });
}

function openWriteOffIngredient(row) {
    if (!selectedBranchId.value || !row?.ingredient_id) {
        return;
    }
    router.push({
        name: "owner-warehouse-branch-item",
        params: {
            branchId: selectedBranchId.value,
            id: row.ingredient_id,
        },
    });
}

watch(
    () => [route.query.ctx, route.query.section],
    ([ctx, section]) => {
        if (ctx && !contextIds.includes(String(ctx))) {
            router.replace({ name: "owner-warehouse", query: {} });
            return;
        }
        if (String(ctx) === "branch" && section && !branchSectionIds.includes(String(section))) {
            router.replace({ name: "owner-warehouse", query: { ctx: "branch", section: "stock" } });
        }
    },
    { immediate: true },
);

watch([context, selectedBranchId, branchSection], loadCurrent);
onMounted(loadCurrent);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <div class="space-y-3">
            <h1 class="text-2xl font-semibold tracking-tight text-bo-fg">
                Склад
            </h1>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="item in contexts"
                    :key="item.id"
                    type="button"
                    class="h-11 px-4 text-sm font-medium transition-colors duration-150"
                    :class="context === item.id
                        ? 'bg-bo-accent text-white'
                        : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'"
                    @click="setContext(item.id)"
                >
                    {{ item.label }}
                </button>
            </div>
        </div>

        <template v-if="context === 'catalog'">
            <UiCard title="Ингредиенты">
                <template #actions>
                    <UiButton @click="openCreateIngredient">
                        + Ингредиент
                    </UiButton>
                </template>

                <div class="mb-4 max-w-md">
                    <UiInput
                        v-model="catalogSearch"
                        type="search"
                        placeholder="Поиск по названию"
                    />
                </div>

                <UiTable
                    :columns="catalogColumns"
                    :rows="catalogRows"
                    :loading="loadApi.loading"
                    empty-text="Ничего не найдено"
                    @row-click="openIngredient"
                />

                <div class="mt-6">
                    <UiPagination
                        :page="catalogPage"
                        :per-page="catalogPerPage"
                        :total="catalogTotal"
                        @update:page="catalogPage = $event"
                    />
                </div>
            </UiCard>
        </template>

        <template v-else>
            <OwnerBranchSelector />

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="item in branchSections"
                    :key="item.id"
                    type="button"
                    class="h-10 px-3.5 text-sm font-medium transition-colors duration-150"
                    :class="branchSection === item.id
                        ? 'bg-white/15 text-bo-fg'
                        : 'bg-transparent text-bo-muted hover:bg-white/[0.06] hover:text-bo-fg'"
                    @click="setBranchSection(item.id)"
                >
                    {{ item.label }}
                </button>
            </div>

            <UiCard v-if="branchSection === 'stock'" title="Остатки филиала">
                <div class="mb-4 max-w-md">
                    <UiInput
                        v-model="stockSearch"
                        type="search"
                        placeholder="Поиск"
                        :disabled="!selectedBranchId"
                    />
                </div>

                <UiTable
                    v-if="selectedBranchId"
                    :columns="stockColumns"
                    :rows="stockRows"
                    :loading="loadApi.loading"
                    empty-text="Ничего не найдено"
                    @row-click="openBranchStockItem"
                />

                <div v-if="selectedBranchId" class="mt-6">
                    <UiPagination
                        :page="stockPage"
                        :per-page="stockPerPage"
                        :total="stockTotal"
                        @update:page="stockPage = $event"
                    />
                </div>
            </UiCard>

            <UiCard v-else title="Списания филиала">
                <div class="mb-4 max-w-md">
                    <UiInput
                        v-model="writeOffsSearch"
                        type="search"
                        placeholder="Поиск"
                        :disabled="!selectedBranchId"
                    />
                </div>

                <UiTable
                    v-if="selectedBranchId"
                    :columns="writeOffColumns"
                    :rows="writeOffRows"
                    :loading="loadApi.loading"
                    empty-text="Ничего не найдено"
                    @row-click="openWriteOffIngredient"
                />

                <div v-if="selectedBranchId" class="mt-6">
                    <UiPagination
                        :page="writeOffsPage"
                        :per-page="writeOffsPerPage"
                        :total="writeOffsTotal"
                        @update:page="writeOffsPage = $event"
                    />
                </div>
            </UiCard>
        </template>
    </div>
</template>
