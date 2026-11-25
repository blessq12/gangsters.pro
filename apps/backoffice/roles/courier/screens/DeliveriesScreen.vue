<script setup>
import { onMounted, onUnmounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { listCourierRouteSheets } from "../../../api/delivery.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import {
    sheetHint,
    sheetStatusLabel,
    sheetStatusVariant,
} from "../../../shell/domain/orderStage.js";

const router = useRouter();
const toast = useToast();
const listApi = useApi();

const STATUS_FILTERS = [
    { value: "", label: "Все" },
    { value: "released", label: "Выпущен" },
    { value: "in_delivery", label: "В доставке" },
    { value: "completed", label: "Завершён" },
];

const statusFilter = ref("");

const {
    page,
    perPage,
    total,
    items: rows,
    offset,
    applyPayload,
} = usePagedList();

const columns = [
    { key: "id", label: "Лист", width: "90px" },
    { key: "status", label: "Этап", width: "140px" },
    { key: "stops", label: "Остановки", width: "140px" },
    { key: "next", label: "Дальше", truncate: true },
];

/** @type {ReturnType<typeof setInterval>|null} */
let pollTimer = null;
const POLL_MS = 18000;

/**
 * @param {Record<string, unknown>} sheet
 */
function stopsLabel(sheet) {
    const stops = Array.isArray(sheet.stops) ? sheet.stops : [];
    const open = stops.filter((stop) => {
        const outcome = String(stop?.outcome ?? "pending");
        return outcome === "pending" || outcome === "";
    }).length;
    if (open > 0) {
        return `${stops.length} · открыто ${open}`;
    }
    return String(stops.length);
}

async function load(silent = false) {
    try {
        const payload = await listApi.run(() => listCourierRouteSheets({
            offset: offset.value,
            limit: perPage,
            status: statusFilter.value || undefined,
        }));
        applyPayload(payload, (sheet) => ({
            id: sheet.id,
            status: sheet.status,
            stops: stopsLabel(sheet),
            next: sheetHint(sheet),
            _raw: sheet,
        }));
        const lastPage = Math.max(1, Math.ceil(total.value / perPage) || 1);
        if (page.value > lastPage) {
            page.value = lastPage;
        }
    } catch (err) {
        if (!silent) {
            toast.error(err instanceof Error ? err.message : "Не удалось загрузить маршрутные листы");
        }
    }
}

/**
 * @param {{ id: number|string }} row
 */
function openSheet(row) {
    router.push({ name: "courier-route-sheet", params: { id: String(row.id) } });
}

/**
 * @param {string} value
 */
function setStatusFilter(value) {
    if (statusFilter.value === value) {
        return;
    }
    statusFilter.value = value;
    if (page.value !== 1) {
        page.value = 1;
        return;
    }
    load();
}

watch(page, () => load());
watch(statusFilter, () => {
    /* page reset handled in setStatusFilter */
});

onMounted(() => {
    load();
    pollTimer = setInterval(() => load(true), POLL_MS);
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Мои листы">
            <div class="mb-6">
                <p class="mb-1 text-sm text-bo-muted">
                    Статус
                </p>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="filter in STATUS_FILTERS"
                        :key="filter.value || 'all'"
                        type="button"
                        class="h-11 px-4 text-sm font-medium transition-colors duration-150"
                        :class="statusFilter === filter.value
                            ? 'bg-bo-accent text-white'
                            : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'"
                        @click="setStatusFilter(filter.value)"
                    >
                        {{ filter.label }}
                    </button>
                </div>
            </div>

            <UiTable
                :columns="columns"
                :rows="rows"
                :loading="listApi.loading"
                empty-text="Листов нет"
                row-key="id"
                @row-click="openSheet"
            >
                <template #cell-status="{ value }">
                    <UiBadge
                        :variant="sheetStatusVariant(value)"
                        :text="sheetStatusLabel(value)"
                    />
                </template>
            </UiTable>

            <div class="mt-6">
                <UiPagination
                    :page="page"
                    :per-page="perPage"
                    :total="total"
                    @update:page="page = $event"
                />
            </div>
        </UiCard>
    </div>
</template>
