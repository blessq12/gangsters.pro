<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { getBranchIngredient } from "../../../api/warehouse.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useClientList } from "../../../shell/composables/useClientList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);

const routeBranchId = computed(() => Number(route.params.branchId));
const ingredientId = computed(() => Number(route.params.id));
const ownBranchId = computed(() => {
    const raw = employee.value?.branch_id;
    const id = Number(raw);
    return Number.isFinite(id) && id > 0 ? id : null;
});

const name = ref("");
const unit = ref("");
const quantity = ref(0);

const {
    searchQuery: historySearch,
    page: historyPage,
    perPage: historyPerPage,
    total: historyTotal,
    pageItems: historyRows,
    setItems: setHistoryItems,
} = useClientList({
    perPage: 20,
    getHaystack: (row) => `${row.reason ?? ""} ${row.kitchen_ticket_id ?? ""}`,
});

const historyColumns = [
    { key: "quantity", label: "Кол-во", width: "100px" },
    { key: "reason", label: "Причина" },
    { key: "kitchen_ticket_id", label: "Тикет", width: "100px" },
    { key: "occurred_at", label: "Когда", width: "170px" },
];

const title = computed(() => {
    const value = String(name.value ?? "").trim();
    return value !== "" ? value : "Позиция склада";
});

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

function backToWarehouse() {
    router.push({ name: "production-warehouse", query: { section: "stock" } });
}

async function load() {
    if (!ownBranchId.value || !ingredientId.value) {
        backToWarehouse();
        return;
    }
    if (routeBranchId.value !== ownBranchId.value) {
        toast.error("Склад другого филиала недоступен");
        backToWarehouse();
        return;
    }
    try {
        const payload = await loadApi.run(() =>
            getBranchIngredient(ownBranchId.value, ingredientId.value),
        );
        name.value = payload?.name ?? "";
        unit.value = payload?.unit ?? "";
        quantity.value = Number(payload?.quantity ?? 0);
        setHistoryItems(
            (Array.isArray(payload?.write_offs) ? payload.write_offs : []).map((row) => ({
                ...row,
                occurred_at: formatDate(row.occurred_at),
                kitchen_ticket_id: row.kitchen_ticket_id ?? "—",
            })),
        );
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить");
        backToWarehouse();
    }
}

watch(() => [route.params.branchId, route.params.id, ownBranchId.value], load);
onMounted(async () => {
    if (!auth.bootstrapped) {
        await auth.bootstrap();
    }
    await load();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="flex flex-wrap items-center gap-3">
            <UiButton class="!bg-white/10 !text-bo-fg" @click="backToWarehouse">
                К складу филиала
            </UiButton>
            <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                {{ title }}
            </h1>
        </div>

        <p v-if="loadApi.loading" class="text-sm text-bo-muted">Загрузка…</p>

        <div v-else class="space-y-4">
            <UiCard title="Остаток">
                <p class="text-xl font-semibold text-bo-fg">
                    {{ quantity }}
                    <span v-if="unit" class="text-sm font-normal text-bo-muted">
                        {{ unit }}
                    </span>
                </p>
            </UiCard>

            <UiCard title="История списаний">
                <div class="mb-4 max-w-md">
                    <UiInput v-model="historySearch" type="search" placeholder="Поиск по причине" />
                </div>
                <UiTable
                    :columns="historyColumns"
                    :rows="historyRows"
                    empty-text="Списаний пока нет"
                />
                <div class="mt-6">
                    <UiPagination
                        :page="historyPage"
                        :per-page="historyPerPage"
                        :total="historyTotal"
                        @update:page="historyPage = $event"
                    />
                </div>
            </UiCard>
        </div>
    </div>
</template>
