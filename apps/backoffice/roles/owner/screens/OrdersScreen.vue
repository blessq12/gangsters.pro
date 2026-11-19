<script setup>
import { onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { listOrders } from "../../../api/orders.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import {
    deliveryMethodLabel,
    formatRubles,
    orderStatusLabel,
    orderStatusVariant,
    paymentStatusLabel,
    paymentStatusVariant,
    shortStage,
} from "../../../shell/domain/orderStage.js";

const router = useRouter();
const toast = useToast();
const listApi = useApi();
const { page, perPage, total, items: rows, offset, applyPayload } = usePagedList();

const columns = [
    { key: "id", label: "Заказ", width: "80px" },
    { key: "status", label: "Этап", width: "160px" },
    { key: "next", label: "Дальше" },
    { key: "client_name", label: "Клиент" },
    { key: "delivery_method", label: "Получение", width: "120px" },
    { key: "payment", label: "Платёж", width: "120px" },
    { key: "total", label: "Сумма", width: "90px" },
];

async function load() {
    try {
        const payload = await listApi.run(() => listOrders({
            offset: offset.value,
            limit: perPage,
        }));
        applyPayload(payload, (order) => ({
            id: order.id,
            status: order.status,
            next: shortStage(order),
            client_name: order.client?.name ?? "—",
            delivery_method: deliveryMethodLabel(order.delivery?.method),
            payment: order.payment?.status ?? "",
            total: formatRubles(order),
        }));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить заказы");
    }
}

function openOrder(row) {
    router.push({ name: "owner-order", params: { id: String(row.id) } });
}

onMounted(load);
watch(page, load);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Заказы">
            <p class="mb-4 text-sm text-bo-muted">
                Срез для владельца. Подтверждение, кухня и маршрутные листы — у своих ролей.
            </p>
            <UiTable
                :columns="columns"
                :rows="rows"
                :loading="listApi.loading"
                empty-text="Заказов пока нет"
                @row-click="openOrder"
            >
                <template #cell-id="{ value }">
                    #{{ value }}
                </template>
                <template #cell-status="{ value }">
                    <UiBadge
                        :variant="orderStatusVariant(value)"
                        :text="orderStatusLabel(value)"
                    />
                </template>
                <template #cell-payment="{ value }">
                    <UiBadge
                        v-if="value"
                        :variant="paymentStatusVariant(value)"
                        :text="paymentStatusLabel(value)"
                    />
                    <span v-else class="text-bo-muted">—</span>
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
