<script setup>
import { computed, onMounted, onUnmounted, watch } from "vue";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { getQueue } from "../../../api/production.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import {
    ticketHint,
    ticketStatusLabel,
    ticketStatusVariant,
} from "../../../shell/domain/orderStage.js";

const router = useRouter();
const toast = useToast();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);
const listApi = useApi();

const {
    page,
    perPage,
    total,
    items: rows,
    offset,
    applyPayload,
    clearItems,
} = usePagedList();

const branchId = computed(() => {
    const raw = employee.value?.branch_id;
    const id = Number(raw);
    return Number.isFinite(id) && id > 0 ? id : null;
});

const columns = [
    { key: "order_number", label: "Заказ", width: "150px" },
    { key: "status", label: "Тикет", width: "140px" },
    { key: "lines_preview", label: "Позиции", truncate: true },
    { key: "next", label: "Дальше", truncate: true },
];

/** @type {ReturnType<typeof setInterval>|null} */
let pollTimer = null;
const POLL_MS = 12000;

/**
 * @param {Array<{ name?: string, product_id?: number|string, quantity?: number }>|undefined} lines
 */
function linesPreview(lines) {
    const list = Array.isArray(lines) ? lines : [];
    if (list.length === 0) {
        return "—";
    }
    return list
        .slice(0, 3)
        .map((line) => `${line.name || `Позиция #${line.product_id}`} × ${line.quantity ?? 1}`)
        .join(", ") + (list.length > 3 ? ` +${list.length - 3}` : "");
}

async function load(silent = false) {
    if (!branchId.value) {
        clearItems();
        return;
    }
    try {
        const payload = await listApi.run(() => getQueue(branchId.value, {
            offset: offset.value,
            limit: perPage,
        }));
        const pool = payload?.pool && typeof payload.pool === "object" ? payload.pool : payload;
        applyPayload(pool, (ticket) => ({
            order_id: ticket.order_id,
            order_number: ticket.order_number || ticket.order?.number || `#${ticket.order_id}`,
            status: ticket.status,
            lines_preview: linesPreview(ticket.lines),
            next: ticketHint(ticket.status),
            _raw: ticket,
        }));
    } catch (err) {
        if (!silent) {
            toast.error(err instanceof Error ? err.message : "Не удалось загрузить тикеты");
        }
    }
}

/**
 * @param {{ order_id: number|string }} row
 */
function goTicket(row) {
    router.push({ name: "production-ticket", params: { orderId: String(row.order_id) } });
}

function restartPoll() {
    if (pollTimer) {
        clearInterval(pollTimer);
    }
    pollTimer = setInterval(() => load(true), POLL_MS);
}

watch(page, () => {
    load();
});

watch(branchId, () => {
    load();
});

onMounted(async () => {
    if (!auth.bootstrapped) {
        await auth.bootstrap();
    }
    await load();
    restartPoll();
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
        <UiCard title="Очередь">
            <p
                v-if="!branchId"
                class="text-sm text-bo-muted"
            >
                У сотрудника не указан филиал смены — очередь недоступна.
            </p>

            <template v-else>
                <UiTable
                    :columns="columns"
                    :rows="rows"
                    :loading="listApi.loading"
                    empty-text="Пул пуст"
                    row-key="order_id"
                    @row-click="goTicket"
                >
                    <template #cell-status="{ value }">
                        <UiBadge
                            :variant="ticketStatusVariant(value)"
                            :text="ticketStatusLabel(value)"
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
            </template>
        </UiCard>
    </div>
</template>
