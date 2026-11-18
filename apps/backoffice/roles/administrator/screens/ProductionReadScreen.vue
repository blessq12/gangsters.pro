<script setup>
import { computed, onMounted, ref } from "vue";
import { getQueue, getTicket } from "../../../api/production.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const toast = useToast();
const listApi = useApi();
const detailApi = useApi();

const rows = ref([]);
const detailOpen = ref(false);
const detail = ref(null);

const columns = [
    { key: "order_id", label: "Order", width: "90px" },
    { key: "status", label: "Тикет", width: "120px" },
    { key: "started_at", label: "Started" },
    { key: "order_status", label: "Заказ", width: "120px" },
];

const statusVariant = {
    queued: "neutral",
    started: "warning",
    completed: "success",
    cancelled: "danger",
};

const tableRows = computed(() =>
    rows.value.map((row) => ({
        order_id: row.order_id,
        status: row.status,
        started_at: formatDate(row.started_at),
        order_status: row.order?.status ?? "—",
        _raw: row,
    })),
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

function asList(payload) {
    return Array.isArray(payload) ? payload : (payload?.items ?? []);
}

async function load() {
    try {
        const payload = await listApi.run(() => getQueue());
        rows.value = asList(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить очередь");
    }
}

async function openDetail(row) {
    detail.value = null;
    detailOpen.value = true;
    try {
        detail.value = await detailApi.run(() => getTicket(row.order_id));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить тикет");
        detailOpen.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="space-y-4 p-4 md:p-6">
        <UiCard title="Production (read)">
            <UiTable
                :columns="columns"
                :rows="tableRows"
                :loading="listApi.loading"
                empty-text="Очередь пуста"
                @row-click="openDetail"
            >
                <template #cell-status="{ value }">
                    <UiBadge
                        :variant="statusVariant[value] || 'neutral'"
                        :text="String(value || '—')"
                    />
                </template>
                <template #cell-order_status="{ value }">
                    <UiBadge variant="neutral" :text="String(value || '—')" />
                </template>
            </UiTable>
        </UiCard>

        <UiModal :open="detailOpen" title="Тикет" @close="detailOpen = false">
            <div v-if="detailApi.loading" class="text-sm text-slate-500">
                Загрузка…
            </div>
            <div v-else-if="detail" class="space-y-2 text-sm">
                <p><span class="text-slate-500">Ticket ID:</span> {{ detail.id }}</p>
                <p><span class="text-slate-500">Order ID:</span> {{ detail.order_id }}</p>
                <p><span class="text-slate-500">Статус тикета:</span> {{ detail.status }}</p>
                <p><span class="text-slate-500">Started:</span> {{ formatDate(detail.started_at) }}</p>
                <p><span class="text-slate-500">Completed:</span> {{ formatDate(detail.completed_at) }}</p>
                <p><span class="text-slate-500">Статус заказа:</span> {{ detail.order?.status ?? "—" }}</p>
                <div v-if="detail.order?.lines?.length">
                    <p class="mb-1 font-medium">Позиции</p>
                    <ul class="list-inside list-disc text-slate-600">
                        <li
                            v-for="(line, idx) in detail.order.lines"
                            :key="idx"
                        >
                            {{ line.product_name || line.product_id }} × {{ line.quantity }}
                        </li>
                    </ul>
                </div>
            </div>
            <template #footer>
                <UiButton @click="detailOpen = false">Закрыть</UiButton>
            </template>
        </UiModal>
    </div>
</template>
