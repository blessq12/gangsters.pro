<script setup>
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { listOrders } from "../../../api/orders.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import OrderQuickPreview from "../../../shell/components/order/OrderQuickPreview.vue";
import ShellIcon from "../../../shell/components/ShellIcon.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import {
    ORDER_STATUSES,
    deliveryMethodLabel,
    formatRubles,
    orderNumberOf,
    orderStatusLabel,
    orderStatusVariant,
    paymentStatusLabel,
    paymentStatusVariant,
    shortStage,
} from "../../../shell/domain/orderStage.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const listApi = useApi();
const {
    page,
    perPage,
    total,
    items: rows,
    offset,
    resetPage,
    applyPayload,
} = usePagedList();

const statusFilter = ref("");

function statusFromQuery() {
    const raw = route.query.status;
    const value = Array.isArray(raw) ? raw[0] : raw;
    if (typeof value !== "string" || value === "") {
        return "";
    }
    return ORDER_STATUSES.includes(value) ? value : "";
}
const previewOpen = ref(false);
const previewOrderId = ref(/** @type {number|string|null} */ (null));

/** Быстрые фильтры по ключевым этапам жизни заказа. */
const QUICK_STATUS_FILTERS = [
    { value: "", label: "Все" },
    { value: "new", label: "Новый" },
    { value: "ready", label: "Готов" },
    { value: "in_transit", label: "В пути" },
];

const columns = [
    { key: "number", label: "Заказ", width: "132px" },
    { key: "status", label: "Этап", width: "118px" },
    { key: "next", label: "Дальше", truncate: true },
    { key: "client", label: "Клиент", truncate: true },
    { key: "delivery_method", label: "Тип", width: "52px", align: "center" },
    { key: "payment", label: "Платёж", width: "110px" },
    { key: "total", label: "Сумма", width: "88px", align: "right" },
];

function clientLine(order) {
    const name = String(order?.client?.name ?? "").trim();
    const phone = String(order?.client?.phone ?? "").trim();
    if (name && phone) {
        return `${name} · ${phone}`;
    }
    return name || phone || "—";
}

function deliveryIcon(method) {
    return method === "pickup" ? "pickup" : "delivery";
}

async function load() {
    try {
        const payload = await listApi.run(() => listOrders({
            offset: offset.value,
            limit: perPage,
            status: statusFilter.value || undefined,
        }));
        applyPayload(payload, (order) => ({
            id: order.id,
            number: orderNumberOf(order),
            status: order.status,
            next: shortStage(order),
            client: clientLine(order),
            delivery_method: order.delivery?.method ?? "",
            payment: order.payment?.status ?? "",
            total: formatRubles(order),
        }));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить заказы");
    }
}

function openPreview(row) {
    previewOrderId.value = row.id;
    previewOpen.value = true;
}

function closePreview() {
    previewOpen.value = false;
    previewOrderId.value = null;
}

function openFull(orderId) {
    closePreview();
    router.push({ name: "administrator-order", params: { id: String(orderId) } });
}

function openCreate() {
    router.push({ name: "administrator-manual-order" });
}

watch(statusFilter, () => {
    resetPage();
    load();
});
watch(page, load);
watch(
    () => route.query.status,
    () => {
        const next = statusFromQuery();
        if (statusFilter.value !== next) {
            statusFilter.value = next;
        }
    },
);
onMounted(() => {
    const next = statusFromQuery();
    if (statusFilter.value !== next) {
        statusFilter.value = next;
        return;
    }
    load();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Заказы">
            <template #actions>
                <UiButton @click="openCreate">+ Заказ</UiButton>
            </template>
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end">
                <div class="min-w-0 flex-1">
                    <p class="mb-1 text-sm text-bo-muted">
                        Быстрый фильтр
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="item in QUICK_STATUS_FILTERS"
                            :key="item.value || 'all'"
                            type="button"
                            class="h-11 px-4 text-sm font-medium transition-colors duration-150"
                            :class="statusFilter === item.value
                                ? 'bg-bo-accent text-white'
                                : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'"
                            @click="statusFilter = item.value"
                        >
                            {{ item.label }}
                        </button>
                    </div>
                </div>
                <div class="w-full max-w-xs sm:shrink-0">
                    <label
                        class="mb-1 block text-sm text-bo-muted"
                        for="order-stage-filter"
                    >Этап</label>
                    <select
                        id="order-stage-filter"
                        v-model="statusFilter"
                        class="w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                    >
                        <option value="">Все</option>
                        <option
                            v-for="status in ORDER_STATUSES"
                            :key="status"
                            :value="status"
                        >
                            {{ orderStatusLabel(status) }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="overflow-hidden">
                <UiTable
                    :columns="columns"
                    :rows="rows"
                    :loading="listApi.loading"
                    empty-text="Заказов нет"
                    @row-click="openPreview"
                >
                    <template #cell-status="{ value }">
                        <UiBadge
                            :variant="orderStatusVariant(value)"
                            :text="orderStatusLabel(value)"
                        />
                    </template>
                    <template #cell-delivery_method="{ value }">
                        <span
                            v-if="value"
                            class="inline-flex text-bo-muted"
                            :title="deliveryMethodLabel(value)"
                        >
                            <ShellIcon
                                :name="deliveryIcon(value)"
                                class="h-5 w-5"
                            />
                        </span>
                        <span v-else class="text-bo-muted">—</span>
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
            </div>

            <div class="mt-6">
                <UiPagination
                    :page="page"
                    :per-page="perPage"
                    :total="total"
                    @update:page="page = $event"
                />
            </div>
        </UiCard>

        <OrderQuickPreview
            :open="previewOpen"
            :order-id="previewOrderId"
            @close="closePreview"
            @open-full="openFull"
        />
    </div>
</template>
