<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { getQueue, getTicket, markTicketLinePacked } from "../../../api/production.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useBranchChoice } from "../../../shell/composables/useBranchChoice.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import {
    orderStatusLabel,
    orderStatusVariant,
    ticketHint,
    ticketStatusLabel,
    ticketStatusVariant,
} from "../../../shell/domain/orderStage.js";

const toast = useToast();
const listApi = useApi();
const detailApi = useApi();
const actionApi = useApi();
const {
    branches,
    branchId,
    error: branchesError,
    load: loadBranches,
    select: selectBranch,
    branchLabel,
} = useBranchChoice();

const {
    page,
    perPage,
    total,
    items: rows,
    offset,
    resetPage,
    applyPayload,
    clearItems,
} = usePagedList();
const detailOpen = ref(false);
const detail = ref(null);

const columns = [
    { key: "order_number", label: "Заказ", width: "150px" },
    { key: "status", label: "Тикет", width: "140px" },
    { key: "order_status", label: "Этап заказа", width: "160px" },
    { key: "next", label: "Дальше" },
];

const tableRows = computed(() =>
    rows.value.map((row) => ({
        order_id: row.order_id,
        order_number: row.order_number || row.order?.number || `#${row.order_id}`,
        status: row.status,
        order_status: row.order?.status ?? "",
        next: ticketHint(row.status),
        _raw: row,
    })),
);

async function load() {
    if (!branchId.value) {
        clearItems();
        return;
    }
    try {
        const payload = await listApi.run(() => getQueue(branchId.value, {
            offset: offset.value,
            limit: perPage,
            scope: "active",
        }));
        const pool = payload?.pool && typeof payload.pool === "object" ? payload.pool : payload;
        applyPayload(pool);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить тикеты");
    }
}

async function openDetail(row) {
    detail.value = null;
    detailOpen.value = true;
    try {
        detail.value = await detailApi.run(() => getTicket(row.order_id));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось открыть тикет");
        detailOpen.value = false;
    }
}

function canPackLine(line) {
    return detail.value?.order?.status === "ready"
        && line?.id
        && line.status === "done"
        && !line.packed_by;
}

async function onPackLine(line) {
    if (!canPackLine(line) || !detail.value?.order_id) {
        return;
    }
    try {
        detail.value = await actionApi.run(() => markTicketLinePacked(detail.value.order_id, line.id));
        toast.success("Позиция собрана");
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось отметить сборку");
    }
}

watch(branchId, () => {
    if (page.value !== 1) {
        resetPage();
        return;
    }
    load();
});
watch(page, load);
onMounted(async () => {
    await loadBranches();
    await load();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Тикеты">
            <p class="mb-4 text-sm text-bo-muted">
                Тикеты филиала в работе (очередь + готовка) со статусами. Готовность позиций — у кухни; сборку ready-заказа можно отметить здесь.
            </p>
            <label class="mb-6 block max-w-md text-sm text-bo-muted" for="admin-branch">
                Филиал
                <select
                    id="admin-branch"
                    class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm text-bo-fg focus:border-bo-accent focus:outline-none"
                    :value="branchId"
                    @change="selectBranch($event.target.value)"
                >
                    <option value="">Выбери филиал</option>
                    <option
                        v-for="branch in branches"
                        :key="branch.id"
                        :value="String(branch.id)"
                    >
                        {{ branchLabel(branch) }}
                    </option>
                </select>
            </label>
            <p v-if="branchesError" class="mb-4 text-sm text-red-300">
                {{ branchesError }}
            </p>

            <p v-if="!branchId" class="text-sm text-bo-muted">
                Сначала выбери филиал — очередь своя у каждого.
            </p>
            <UiTable
                v-else
                :columns="columns"
                :rows="tableRows"
                :loading="listApi.loading"
                empty-text="В этом филиале нет тикетов в работе"
                row-key="order_id"
                @row-click="openDetail"
            >
                <template #cell-status="{ value }">
                    <UiBadge
                        :variant="ticketStatusVariant(value)"
                        :text="ticketStatusLabel(value)"
                    />
                </template>
                <template #cell-order_status="{ value }">
                    <UiBadge
                        v-if="value"
                        :variant="orderStatusVariant(value)"
                        :text="orderStatusLabel(value)"
                    />
                    <span v-else class="text-bo-muted">—</span>
                </template>
            </UiTable>
            <div v-if="branchId" class="mt-6">
                <UiPagination
                    :page="page"
                    :per-page="perPage"
                    :total="total"
                    @update:page="page = $event"
                />
            </div>
        </UiCard>

        <UiModal :open="detailOpen" title="Тикет" @close="detailOpen = false">
            <div v-if="detailApi.loading" class="text-sm text-bo-muted">
                Загрузка…
            </div>
            <div v-else-if="detail" class="space-y-3 text-sm">
                <p class="border border-bo-border bg-white/[0.03] px-4 py-3">
                    {{ ticketHint(detail.status) }}
                </p>
                <p>{{ detail.order_number || detail.order?.number || `Заказ #${detail.order_id}` }}</p>
                <p>
                    Тикет:
                    <UiBadge
                        class="ml-2"
                        :variant="ticketStatusVariant(detail.status)"
                        :text="ticketStatusLabel(detail.status)"
                    />
                </p>
                <p v-if="detail.order?.status">
                    Заказ: {{ orderStatusLabel(detail.order.status) }}
                </p>
                <ul class="space-y-2">
                    <li
                        v-for="line in (detail.lines ?? [])"
                        :key="line.id ?? `${line.product_id}-${line.name}`"
                        class="flex flex-wrap items-center justify-between gap-2 border border-bo-border px-3 py-2"
                    >
                        <span>
                            {{ line.name || `Позиция #${line.product_id}` }} × {{ line.quantity }}
                            <span class="ml-2 text-bo-muted">{{ line.status || "queued" }}</span>
                            <span
                                v-if="line.done_by"
                                class="ml-2 text-emerald-300"
                            >кухня #{{ line.done_by }}</span>
                            <span
                                v-if="line.packed_by"
                                class="ml-2 text-amber-300"
                            >сборка #{{ line.packed_by }}</span>
                        </span>
                        <UiButton
                            v-if="canPackLine(line)"
                            :disabled="actionApi.loading"
                            @click="onPackLine(line)"
                        >
                            На месте
                        </UiButton>
                    </li>
                </ul>
                <p v-if="detail.order?.comment" class="text-bo-muted">
                    Комментарий: {{ detail.order.comment }}
                </p>
            </div>
            <template #footer>
                <UiButton @click="detailOpen = false">
                    Закрыть
                </UiButton>
            </template>
        </UiModal>
    </div>
</template>
