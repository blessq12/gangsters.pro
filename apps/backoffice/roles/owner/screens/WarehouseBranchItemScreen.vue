<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    adjustBranchStock,
    getBranchIngredient,
    storeBranchWriteOff,
} from "../../../api/warehouse.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useClientList } from "../../../shell/composables/useClientList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const stockApi = useApi();
const writeOffApi = useApi();

const branchId = computed(() => Number(route.params.branchId));
const ingredientId = computed(() => Number(route.params.id));

const name = ref("");
const unit = ref("");
const quantity = ref(0);
const stockDelta = ref("");
const writeOffForm = reactive({
    quantity: "",
    reason: "",
});
const writeOffErrors = reactive({});

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

function clearWriteOffErrors() {
    Object.keys(writeOffErrors).forEach((key) => {
        delete writeOffErrors[key];
    });
}

async function load() {
    if (!branchId.value || !ingredientId.value) {
        router.push({ name: "owner-warehouse", query: { ctx: "branch", section: "stock" } });
        return;
    }
    try {
        const payload = await loadApi.run(() =>
            getBranchIngredient(branchId.value, ingredientId.value),
        );
        name.value = payload?.name ?? "";
        unit.value = payload?.unit ?? "";
        quantity.value = Number(payload?.quantity ?? 0);
        stockDelta.value = "";
        writeOffForm.quantity = "";
        writeOffForm.reason = "";
        clearWriteOffErrors();
        setHistoryItems(
            (Array.isArray(payload?.write_offs) ? payload.write_offs : []).map((row) => ({
                ...row,
                occurred_at: formatDate(row.occurred_at),
                kitchen_ticket_id: row.kitchen_ticket_id ?? "—",
            })),
        );
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить");
        router.push({ name: "owner-warehouse", query: { ctx: "branch", section: "stock" } });
    }
}

async function applyStockDelta() {
    if (stockDelta.value === "" || Number.isNaN(Number(stockDelta.value))) {
        toast.error("Укажи изменение остатка");
        return;
    }
    try {
        const result = await stockApi.run(() => adjustBranchStock(branchId.value, {
            ingredient_id: ingredientId.value,
            delta: Number(stockDelta.value),
        }));
        quantity.value = Number(result?.quantity ?? quantity.value);
        stockDelta.value = "";
        toast.success("Остаток обновлён");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

function validateWriteOff() {
    clearWriteOffErrors();
    if (
        writeOffForm.quantity === ""
        || Number.isNaN(Number(writeOffForm.quantity))
        || Number(writeOffForm.quantity) <= 0
    ) {
        writeOffErrors.quantity = "Больше 0";
    }
    if (!String(writeOffForm.reason).trim()) {
        writeOffErrors.reason = "Обязательное поле";
    }
    return Object.keys(writeOffErrors).length === 0;
}

async function submitWriteOff() {
    if (!validateWriteOff()) {
        toast.error("Проверьте поля списания");
        return;
    }
    try {
        await writeOffApi.run(() => storeBranchWriteOff(branchId.value, {
            ingredient_id: ingredientId.value,
            quantity: Number(writeOffForm.quantity),
            reason: writeOffForm.reason.trim(),
        }));
        toast.success("Списано");
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

function backToBranch() {
    router.push({ name: "owner-warehouse", query: { ctx: "branch", section: "stock" } });
}

watch(() => [route.params.branchId, route.params.id], load);
onMounted(load);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="flex flex-wrap items-center gap-3">
            <UiButton class="!bg-white/10 !text-bo-fg" @click="backToBranch">
                К складу филиала
            </UiButton>
            <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                {{ title }}
            </h1>
        </div>

        <p v-if="loadApi.loading" class="text-sm text-bo-muted">
            Загрузка…
        </p>

        <div v-else class="space-y-4">
            <UiCard title="Остаток">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[8rem]">
                        <p class="text-sm text-bo-muted">Сейчас</p>
                        <p class="mt-1 text-xl font-semibold text-bo-fg">
                            {{ quantity }}
                            <span v-if="unit" class="text-sm font-normal text-bo-muted">
                                {{ unit }}
                            </span>
                        </p>
                    </div>
                    <div class="w-40">
                        <UiFormField label="Изменение (+/−)">
                            <UiInput v-model="stockDelta" type="number" />
                        </UiFormField>
                    </div>
                    <UiButton :disabled="stockApi.loading" @click="applyStockDelta">
                        Применить
                    </UiButton>
                </div>
            </UiCard>

            <UiCard title="Списать">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="w-36">
                        <UiFormField label="Количество" :error="writeOffErrors.quantity">
                            <UiInput v-model="writeOffForm.quantity" type="number" />
                        </UiFormField>
                    </div>
                    <div class="min-w-[14rem] flex-1">
                        <UiFormField label="Причина" :error="writeOffErrors.reason">
                            <UiInput v-model="writeOffForm.reason" />
                        </UiFormField>
                    </div>
                    <UiButton :disabled="writeOffApi.loading" @click="submitWriteOff">
                        Списать
                    </UiButton>
                </div>
            </UiCard>

            <UiCard title="История списаний">
                <div class="mb-4 max-w-md">
                    <UiInput
                        v-model="historySearch"
                        type="search"
                        placeholder="Поиск по причине"
                    />
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
