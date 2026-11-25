<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import {
    getBranchStock,
    listBranchWriteOffs,
    listIngredients,
} from "../../../api/warehouse.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useClientList } from "../../../shell/composables/useClientList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const PAGE_LIMIT = 100;

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);

const branchId = computed(() => {
    const raw = employee.value?.branch_id;
    const id = Number(raw);
    return Number.isFinite(id) && id > 0 ? id : null;
});

const sections = [
    { id: "stock", label: "Остатки" },
    { id: "writeoffs", label: "Списания" },
];
const sectionIds = sections.map((item) => item.id);

const section = computed(() =>
    sectionIds.includes(String(route.query.section))
        ? String(route.query.section)
        : "stock",
);

function setSection(id) {
    if (section.value === id) {
        return;
    }
    router.replace({ name: "production-warehouse", query: { section: id } });
}

const allIngredients = ref([]);
/** @type {import('vue').Ref<Map<number, number>>} */
const quantityByIngredientId = ref(new Map());
const allWriteOffs = ref([]);

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

function rebuildLists() {
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

async function load() {
    if (!branchId.value) {
        allIngredients.value = [];
        quantityByIngredientId.value = new Map();
        allWriteOffs.value = [];
        rebuildLists();
        return;
    }
    try {
        const id = branchId.value;
        const [ingredients, stockLevels, writeOffs] = await loadApi.run(() => Promise.all([
            fetchAllPages(listIngredients),
            fetchAllPages((query) => getBranchStock(id, query)),
            fetchAllPages((query) => listBranchWriteOffs(id, query)),
        ]));
        allIngredients.value = ingredients;
        const qty = new Map();
        for (const row of stockLevels) {
            qty.set(Number(row.ingredient_id), Number(row.quantity ?? 0));
        }
        quantityByIngredientId.value = qty;
        allWriteOffs.value = writeOffs;
        rebuildLists();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить склад филиала");
    }
}

/**
 * @param {{ id: number }} row
 */
function openStockItem(row) {
    if (!branchId.value) {
        return;
    }
    router.push({
        name: "production-warehouse-item",
        params: {
            branchId: String(branchId.value),
            id: String(row.id),
        },
    });
}

/**
 * @param {{ ingredient_id?: number }} row
 */
function openWriteOffIngredient(row) {
    if (!branchId.value || !row?.ingredient_id) {
        return;
    }
    router.push({
        name: "production-warehouse-item",
        params: {
            branchId: String(branchId.value),
            id: String(row.ingredient_id),
        },
    });
}

watch(
    () => route.query.section,
    (value) => {
        if (value && !sectionIds.includes(String(value))) {
            router.replace({ name: "production-warehouse", query: { section: "stock" } });
        }
    },
    { immediate: true },
);

watch(branchId, load);
onMounted(async () => {
    if (!auth.bootstrapped) {
        await auth.bootstrap();
    }
    await load();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <div class="space-y-3">
            <h1 class="text-2xl font-semibold tracking-tight text-bo-fg">
                Склад филиала
            </h1>
            <p class="text-sm text-bo-muted">
                Только просмотр. Изменения остатков и списания — у владельца.
            </p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="item in sections"
                    :key="item.id"
                    type="button"
                    class="h-11 px-4 text-sm font-medium transition-colors duration-150"
                    :class="section === item.id
                        ? 'bg-bo-accent text-white'
                        : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'"
                    @click="setSection(item.id)"
                >
                    {{ item.label }}
                </button>
            </div>
        </div>

        <p
            v-if="!branchId"
            class="text-sm text-bo-muted"
        >
            У сотрудника не указан филиал смены — склад недоступен.
        </p>

        <template v-else>
            <UiCard
                v-if="section === 'stock'"
                title="Остатки"
            >
                <div class="mb-4 max-w-md">
                    <UiInput
                        v-model="stockSearch"
                        type="search"
                        placeholder="Поиск"
                    />
                </div>

                <UiTable
                    :columns="stockColumns"
                    :rows="stockRows"
                    :loading="loadApi.loading"
                    empty-text="Ничего не найдено"
                    @row-click="openStockItem"
                />

                <div class="mt-6">
                    <UiPagination
                        :page="stockPage"
                        :per-page="stockPerPage"
                        :total="stockTotal"
                        @update:page="stockPage = $event"
                    />
                </div>
            </UiCard>

            <UiCard
                v-else
                title="Списания"
            >
                <div class="mb-4 max-w-md">
                    <UiInput
                        v-model="writeOffsSearch"
                        type="search"
                        placeholder="Поиск"
                    />
                </div>

                <UiTable
                    :columns="writeOffColumns"
                    :rows="writeOffRows"
                    :loading="loadApi.loading"
                    empty-text="Ничего не найдено"
                    @row-click="openWriteOffIngredient"
                />

                <div class="mt-6">
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
