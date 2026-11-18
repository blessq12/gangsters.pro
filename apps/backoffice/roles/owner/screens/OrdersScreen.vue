<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { getOrder, listOrders } from "../../../api/orders.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const toast = useToast();
const listApi = useApi();
const detailApi = useApi();

const page = ref(1);
const perPage = 20;
const total = ref(0);
const rows = ref([]);
const detailOpen = ref(false);
const detail = ref(null);

const columns = [
    { key: "id", label: "ID", width: "72px" },
    { key: "status", label: "Статус", width: "120px" },
    { key: "client_name", label: "Клиент" },
    { key: "delivery_method", label: "Доставка", width: "110px" },
    { key: "total", label: "Сумма", width: "100px" },
    { key: "created_at", label: "Создан" },
];

const statusVariant = {
    new: "neutral",
    confirmed: "warning",
    preparing: "warning",
    ready: "success",
    in_transit: "warning",
    delivered: "success",
    completed: "success",
    cancelled: "danger",
};

function formatTotal(order) {
    const rubles = order?.totals?.grand_total_rubles
        ?? order?.total
        ?? Math.floor(Number(order?.totals?.grand_total ?? 0) / 100);
    return `${rubles} ₽`;
}

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

async function load() {
    try {
        const payload = await listApi.run(() => listOrders({
            offset: (page.value - 1) * perPage,
            limit: perPage,
        }));
        total.value = Number(payload?.total ?? 0);
        rows.value = (payload?.items ?? []).map((order) => ({
            id: order.id,
            status: order.status,
            client_name: order.client?.name ?? "—",
            delivery_method: order.delivery?.method ?? "—",
            total: formatTotal(order),
            created_at: formatDate(order.created_at),
            _raw: order,
        }));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить заказы");
    }
}

async function openDetail(row) {
    detailOpen.value = true;
    detail.value = null;
    try {
        detail.value = await detailApi.run(() => getOrder(row.id));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить заказ");
        detailOpen.value = false;
    }
}

const detailLines = computed(() => detail.value?.cart?.lines ?? []);

onMounted(load);
watch(page, load);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Заказы">
            <UiTable
                :columns="columns"
                :rows="rows"
                :loading="listApi.loading"
                empty-text="Заказов пока нет"
                @row-click="openDetail"
            >
                <template #cell-status="{ value }">
                    <UiBadge
                        :variant="statusVariant[value] || 'neutral'"
                        :text="String(value || '—')"
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

        <UiModal
            :open="detailOpen"
            title="Заказ"
            @close="detailOpen = false"
        >
            <div v-if="detailApi.loading" class="text-sm text-bo-muted">
                Загрузка…
            </div>
            <div v-else-if="detail" class="space-y-4 text-sm text-bo-fg">
                <p><span class="text-bo-muted">ID:</span> {{ detail.id }}</p>
                <p>
                    <span class="text-bo-muted">Статус:</span>
                    <UiBadge
                        class="ml-2"
                        :variant="statusVariant[detail.status] || 'neutral'"
                        :text="String(detail.status)"
                    />
                </p>
                <p><span class="text-bo-muted">Клиент:</span> {{ detail.client?.name || '—' }} · {{ detail.client?.phone || '' }}</p>
                <p><span class="text-bo-muted">Доставка:</span> {{ detail.delivery?.method || '—' }}</p>
                <p><span class="text-bo-muted">Сумма:</span> {{ formatTotal(detail) }}</p>
                <p><span class="text-bo-muted">Создан:</span> {{ formatDate(detail.created_at) }}</p>
                <div>
                    <p class="mb-1 font-medium text-bo-fg">Позиции</p>
                    <ul class="list-inside list-disc text-bo-muted">
                        <li v-for="(line, idx) in detailLines" :key="idx">
                            {{ line.product_name || line.product_id }} × {{ line.quantity }}
                            ({{ line.line_total_rubles ?? 0 }} ₽)
                        </li>
                    </ul>
                </div>
            </div>
        </UiModal>
    </div>
</template>
