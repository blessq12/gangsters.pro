<script setup>
import { computed, nextTick, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { acceptOrder, completePickup, listOrders } from "../../../api/orders.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import PeriodRangePicker from "../../../shell/components/PeriodRangePicker.vue";
import ShellIcon from "../../../shell/components/ShellIcon.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import {
    ORDER_STATUSES,
    administratorCommands,
    deliveryMethodLabel,
    formatPreorderRemaining,
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
const actionApi = useApi();
const { page, perPage, total, items: rows, offset, resetPage, applyPayload } = usePagedList();

const statusFilter = ref("");
const searchOpen = ref(false);
const searchInput = ref("");
const q = ref("");
/** @type {import('vue').Ref<HTMLElement | null>} */
const searchWrapRef = ref(null);
const period = reactive({ from: "", to: "" });
/** @type {import('vue').Ref<Record<string, number>>} */
const statusCounts = ref({});
/** Тик для пересчёта «через N» у предзаказов. */
const nowTick = ref(Date.now());
/** @type {ReturnType<typeof setInterval> | null} */
let tickTimer = null;
/** @type {ReturnType<typeof setTimeout> | null} */
let searchTimer = null;

/** Чипы статусов: «Все» + полный список этапов. */
const STATUS_CHIPS = [
    { value: "", label: "Все" },
    ...ORDER_STATUSES.map((status) => ({
        value: status,
        label: orderStatusLabel(status),
    })),
];

function statusFromQuery() {
    const raw = route.query.status;
    const value = Array.isArray(raw) ? raw[0] : raw;
    if (typeof value !== "string" || value === "") {
        return "";
    }
    return ORDER_STATUSES.includes(value) ? value : "";
}

/** @type {import('vue').Ref<'confirm'|'pickup'|null>} */
const pendingAction = ref(null);
/** @type {import('vue').Ref<number|string|null>} */
const pendingOrderId = ref(null);

const columns = [
    { key: "number", label: "Заказ", width: "132px" },
    { key: "status", label: "Этап", width: "148px" },
    { key: "next", label: "Дальше", truncate: true },
    { key: "client", label: "Клиент", truncate: true },
    { key: "delivery_method", label: "Тип", width: "52px", align: "center" },
    { key: "payment", label: "Платёж", width: "110px" },
    { key: "total", label: "Сумма", width: "88px", align: "right" },
    { key: "actions", label: "Действия", width: "168px" },
];

const allCount = computed(() => {
    const counts = statusCounts.value;
    return Object.values(counts).reduce((sum, value) => sum + Number(value || 0), 0);
});

/**
 * @param {string} value
 */
function chipCount(value) {
    if (value === "") {
        return allCount.value;
    }
    return Number(statusCounts.value[value] || 0);
}

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

/**
 * Только подтверждение и выдача самовывоза — остальное на полной карточке.
 * @param {{ status?: string, delivery_method?: string }} row
 */
function quickActionsFor(row) {
    return administratorCommands({
        status: row.status,
        delivery: { method: row.delivery_method },
    }).filter((command) => command.id === "confirm" || command.id === "pickup");
}

/**
 * @param {Record<string, unknown>} order
 */
function mapListRow(order) {
    const scheduledAt = order.scheduled_at ?? null;
    const remaining =
        order.status === "preorder" ? formatPreorderRemaining(scheduledAt, nowTick.value) : "";
    return {
        id: order.id,
        number: orderNumberOf(order),
        status: order.status,
        scheduled_at: scheduledAt,
        preorder_remaining: remaining,
        preorder_overdue: remaining.startsWith("просрочен") || remaining === "слот наступил",
        next: shortStage(order, nowTick.value),
        client: clientLine(order),
        delivery_method: order.delivery?.method ?? "",
        payment: order.payment?.status ?? "",
        total: formatRubles(order),
    };
}

function pad(n) {
    return String(n).padStart(2, "0");
}

function toDateInput(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

async function toggleSearch() {
    searchOpen.value = !searchOpen.value;
    if (searchOpen.value) {
        await nextTick();
        const input = searchWrapRef.value?.querySelector("input");
        input?.focus();
    }
}

async function load() {
    try {
        const payload = await listApi.run(() =>
            listOrders({
                offset: offset.value,
                limit: perPage,
                status: statusFilter.value || undefined,
                from: period.from || undefined,
                to: period.to || undefined,
                q: q.value.trim() || undefined,
            }),
        );
        const counts = payload?.status_counts;
        statusCounts.value = counts && typeof counts === "object" ? counts : {};
        applyPayload(payload, mapListRow);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить заказы");
    }
}

function refreshPreorderLabels() {
    nowTick.value = Date.now();
    rows.value = rows.value.map((row) => {
        if (row.status !== "preorder") {
            return row;
        }
        const remaining = formatPreorderRemaining(row.scheduled_at, nowTick.value);
        return {
            ...row,
            preorder_remaining: remaining,
            preorder_overdue: remaining.startsWith("просрочен") || remaining === "слот наступил",
            next: shortStage(
                {
                    status: row.status,
                    scheduled_at: row.scheduled_at,
                    delivery: { method: row.delivery_method },
                },
                nowTick.value,
            ),
        };
    });
}

function openFull(row) {
    router.push({ name: "administrator-order", params: { id: String(row.id) } });
}

function openCreate() {
    router.push({ name: "administrator-order-new" });
}

/**
 * @param {MouseEvent} event
 * @param {{ id: number|string }} row
 * @param {'confirm'|'pickup'} actionId
 */
function askQuickAction(event, row, actionId) {
    event.stopPropagation();
    pendingOrderId.value = row.id;
    pendingAction.value = actionId;
}

function closePending() {
    pendingAction.value = null;
    pendingOrderId.value = null;
}

async function submitPending() {
    const orderId = pendingOrderId.value;
    const action = pendingAction.value;
    closePending();
    if (!orderId || !action) {
        return;
    }
    try {
        if (action === "confirm") {
            await actionApi.run(() => acceptOrder(orderId));
            toast.success("Заказ подтверждён. Тикет и платёж созданы.");
        } else if (action === "pickup") {
            await actionApi.run(() => completePickup(orderId));
            toast.success("Заказ выдан. Платёж оплачен.");
        } else {
            const _exhaustive = action;
            void _exhaustive;
            return;
        }
        await load();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось выполнить действие");
    }
}

watch(statusFilter, () => {
    resetPage();
    load();
});
watch(page, load);
watch(
    () => [period.from, period.to],
    () => {
        resetPage();
        load();
    },
);
watch(searchInput, () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
    searchTimer = setTimeout(() => {
        q.value = searchInput.value.trim();
        resetPage();
        load();
    }, 300);
});
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
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    period.from = toDateInput(today);
    period.to = toDateInput(today);
    const next = statusFromQuery();
    if (statusFilter.value !== next) {
        statusFilter.value = next;
    }
    tickTimer = setInterval(refreshPreorderLabels, 30000);
});
onUnmounted(() => {
    if (tickTimer) {
        clearInterval(tickTimer);
        tickTimer = null;
    }
    if (searchTimer) {
        clearTimeout(searchTimer);
        searchTimer = null;
    }
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Заказы">
            <template #actions>
                <UiButton @click="openCreate">+ Заказ</UiButton>
            </template>
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <PeriodRangePicker v-model:from="period.from" v-model:to="period.to" />
                <div class="ml-auto flex items-center gap-1">
                    <div
                        ref="searchWrapRef"
                        class="overflow-hidden transition-[max-width,opacity] duration-200 ease-out"
                        :class="searchOpen ? 'max-w-[16rem] opacity-100' : 'max-w-0 opacity-0'"
                    >
                        <input
                            v-model="searchInput"
                            type="text"
                            class="h-11 min-w-[14rem] border border-bo-border bg-bo-field px-4 text-sm text-bo-fg transition-colors placeholder:text-bo-muted/60 hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                            placeholder="Номер заказа (ORD-…)"
                        />
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center border border-bo-border bg-bo-field text-bo-muted transition-colors hover:border-bo-border-strong hover:text-bo-fg"
                        :class="searchOpen || q ? 'border-bo-accent text-bo-accent' : ''"
                        :aria-expanded="searchOpen"
                        aria-label="Поиск по номеру заказа"
                        @click="toggleSearch"
                    >
                        <ShellIcon name="search" class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div class="mb-6 flex flex-wrap gap-2">
                <button
                    v-for="item in STATUS_CHIPS"
                    :key="item.value || 'all'"
                    type="button"
                    class="inline-flex h-11 items-center gap-2 px-4 text-sm font-medium transition-colors duration-150"
                    :class="
                        statusFilter === item.value
                            ? 'bg-bo-accent text-white'
                            : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'
                    "
                    @click="statusFilter = item.value"
                >
                    <span>{{ item.label }}</span>
                    <span
                        class="min-w-[1.25rem] rounded-sm px-1.5 text-center text-xs font-semibold tabular-nums"
                        :class="
                            statusFilter === item.value
                                ? 'bg-white/20 text-white'
                                : 'bg-white/10 text-bo-fg'
                        "
                    >
                        {{ chipCount(item.value) }}
                    </span>
                </button>
            </div>

            <div class="overflow-hidden">
                <UiTable
                    :columns="columns"
                    :rows="rows"
                    :loading="listApi.loading"
                    empty-text="Заказов нет"
                    @row-click="openFull"
                >
                    <template #cell-status="{ row, value }">
                        <div class="flex min-w-0 flex-col gap-1">
                            <UiBadge
                                :variant="orderStatusVariant(value)"
                                :text="orderStatusLabel(value)"
                            />
                            <span
                                v-if="row.preorder_remaining"
                                class="text-xs tabular-nums"
                                :class="row.preorder_overdue ? 'text-red-300' : 'text-bo-muted'"
                            >
                                {{ row.preorder_remaining }}
                            </span>
                        </div>
                    </template>
                    <template #cell-delivery_method="{ value }">
                        <span
                            v-if="value"
                            class="inline-flex text-bo-muted"
                            :title="deliveryMethodLabel(value)"
                        >
                            <ShellIcon :name="deliveryIcon(value)" class="h-5 w-5" />
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
                    <template #cell-actions="{ row }">
                        <div v-if="quickActionsFor(row).length" class="flex flex-wrap gap-2">
                            <UiButton
                                v-for="command in quickActionsFor(row)"
                                :key="command.id"
                                class="!h-9 !px-3 !text-xs"
                                :disabled="actionApi.loading"
                                @click="askQuickAction($event, row, command.id)"
                            >
                                {{ command.label }}
                            </UiButton>
                        </div>
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

        <UiConfirmDialog
            :open="pendingAction === 'confirm'"
            title="Подтвердить заказ?"
            message="Филиал уже на заказе. Появятся тикет в очереди и платёж в ожидании."
            @confirm="submitPending"
            @cancel="closePending"
        />

        <UiConfirmDialog
            :open="pendingAction === 'pickup'"
            title="Выдать самовывоз?"
            message="Заказ станет выданным, платёж — оплаченным. В маршрутный лист он не попадёт."
            @confirm="submitPending"
            @cancel="closePending"
        />
    </div>
</template>
