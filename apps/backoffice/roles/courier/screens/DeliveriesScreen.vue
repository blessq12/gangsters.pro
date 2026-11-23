<script setup>
import { computed, onMounted, onUnmounted, watch } from "vue";
import { useRouter } from "vue-router";
import { listCourierRouteSheets } from "../../../api/delivery.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import {
    sheetStatusLabel,
    sheetStatusVariant,
} from "../../../shell/domain/orderStage.js";

const router = useRouter();
const toast = useToast();
const listApi = useApi();

const {
    page,
    perPage,
    total,
    items: sheets,
    offset,
    applyPayload,
} = usePagedList({ perPage: 10 });

/** @type {ReturnType<typeof setInterval>|null} */
let pollTimer = null;
const POLL_MS = 18000;

const empty = computed(() => !listApi.loading && sheets.value.length === 0);

/**
 * @param {Record<string, unknown>} sheet
 */
function stopsCount(sheet) {
    return Array.isArray(sheet.stops) ? sheet.stops.length : 0;
}

/**
 * @param {Record<string, unknown>} sheet
 */
function openStopsCount(sheet) {
    const stops = Array.isArray(sheet.stops) ? sheet.stops : [];
    return stops.filter((stop) => {
        const outcome = String(stop?.outcome ?? "pending");
        return outcome === "pending" || outcome === "";
    }).length;
}

async function load(silent = false) {
    try {
        const payload = await listApi.run(() => listCourierRouteSheets({
            offset: offset.value,
            limit: perPage,
        }));
        applyPayload(payload);
        const lastPage = Math.max(1, Math.ceil(total.value / perPage));
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
 * @param {number|string} sheetId
 */
function openSheet(sheetId) {
    router.push({ name: "courier-route-sheet", params: { id: String(sheetId) } });
}

watch(page, () => load());
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
    <div class="mx-auto w-full max-w-lg space-y-4 px-4 py-4">
        <h1 class="text-xl font-bold tracking-tight text-bo-fg">
            Мои листы
        </h1>

        <div
            v-if="listApi.loading && sheets.length === 0"
            class="border border-bo-border bg-bo-elevated px-4 py-12 text-center text-base text-bo-muted"
        >
            Загрузка…
        </div>

        <div
            v-else-if="empty"
            class="border border-bo-border bg-bo-elevated px-4 py-16 text-center text-lg font-semibold text-bo-muted"
        >
            Листов нет
        </div>

        <div
            v-else
            class="space-y-3"
        >
            <button
                v-for="sheet in sheets"
                :key="sheet.id"
                type="button"
                class="flex w-full flex-col gap-3 border border-bo-border bg-bo-elevated p-4 text-left active:bg-white/[0.06]"
                @click="openSheet(sheet.id)"
            >
                <div class="flex items-start justify-between gap-3">
                    <p class="text-2xl font-black leading-none text-bo-fg">
                        Лист #{{ sheet.id }}
                    </p>
                    <UiBadge
                        class="!px-3 !py-1 !text-sm"
                        :variant="sheetStatusVariant(sheet.status)"
                        :text="sheetStatusLabel(sheet.status)"
                    />
                </div>
                <p class="text-base font-medium text-bo-fg">
                    {{ stopsCount(sheet) }} ост.
                    <span
                        v-if="openStopsCount(sheet) > 0"
                        class="font-normal text-bo-muted"
                    >
                        · открыто {{ openStopsCount(sheet) }}
                    </span>
                </p>
            </button>
        </div>

        <UiPagination
            v-if="total > perPage"
            :page="page"
            :per-page="perPage"
            :total="total"
            @update:page="page = $event"
        />
    </div>
</template>
