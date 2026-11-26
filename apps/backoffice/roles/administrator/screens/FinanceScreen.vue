<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import {
    getPaymentSummary,
    listPayments,
    markPaymentPaid,
} from "../../../api/payments.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import ShellIcon from "../../../shell/components/ShellIcon.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import {
    formatKopecks,
    paymentMethodLabel,
    paymentStatusLabel,
    paymentStatusVariant,
} from "../../../shell/domain/orderStage.js";

const router = useRouter();
const toast = useToast();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);
const listApi = useApi();
const summaryApi = useApi();
const actionApi = useApi();
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

const branchId = computed(() => {
    const raw = employee.value?.branch_id;
    const id = Number(raw);
    return Number.isFinite(id) && id > 0 ? id : null;
});

/** @type {import('vue').Ref<Record<string, unknown>|null>} */
const summary = ref(null);

const filters = reactive({
    from: "",
    to: "",
    status: "",
    method: "",
});

const confirmOpen = ref(false);
/** @type {import('vue').Ref<{ id: number, title: string }|null>} */
const paidTarget = ref(null);

const columns = [
    { key: "created_at", label: "Создан", width: "150px" },
    { key: "order_number", label: "Заказ", width: "140px" },
    { key: "method", label: "Метод", width: "130px" },
    { key: "amount", label: "Сумма", width: "100px", align: "right" },
    { key: "status", label: "Статус", width: "120px" },
    { key: "actions", label: "", width: "120px" },
];

const presets = [
    { key: "today", label: "Сегодня" },
    { key: "yesterday", label: "Вчера" },
    { key: "7d", label: "7 дней" },
];

const confirmMessage = computed(() => {
    const target = paidTarget.value;
    if (!target) {
        return "";
    }
    return `Отметить оплаченным «${target.title}»?`;
});

function pad(n) {
    return String(n).padStart(2, "0");
}

function toDateInput(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

function applyPreset(key) {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    if (key === "today") {
        filters.from = toDateInput(today);
        filters.to = toDateInput(today);
    } else if (key === "yesterday") {
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        filters.from = toDateInput(yesterday);
        filters.to = toDateInput(yesterday);
    } else {
        const from = new Date(today);
        from.setDate(from.getDate() - 6);
        filters.from = toDateInput(from);
        filters.to = toDateInput(today);
    }
    resetPage();
    loadAll();
}

function queryParams() {
    return {
        from: filters.from || undefined,
        to: filters.to || undefined,
        status: filters.status || undefined,
        method: filters.method || undefined,
        branch_id: branchId.value || undefined,
    };
}

function formatDateTime(value) {
    if (!value) {
        return "—";
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return "—";
    }
    return `${toDateInput(date)} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

async function loadSummary() {
    if (!branchId.value) {
        summary.value = null;
        return;
    }
    try {
        summary.value = await summaryApi.run(() => getPaymentSummary(queryParams()));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить сводку");
    }
}

async function loadList() {
    if (!branchId.value) {
        clearItems();
        return;
    }
    try {
        const payload = await listApi.run(() => listPayments({
            ...queryParams(),
            offset: offset.value,
            limit: perPage,
        }));
        applyPayload(payload, (payment) => ({
            id: payment.id,
            order_id: payment.order_id,
            order_number: payment.order_number || `ORD-${String(payment.order_id).padStart(4, "0")}`,
            created_at: formatDateTime(payment.created_at),
            method: payment.method,
            amount: formatKopecks(payment.amount_kopecks),
            status: payment.status,
            raw_status: payment.status,
        }));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить журнал");
    }
}

async function loadAll() {
    if (!branchId.value) {
        summary.value = null;
        clearItems();
        return;
    }
    await Promise.all([loadSummary(), loadList()]);
}

function onFilterCommit() {
    resetPage();
    loadAll();
}

function openOrder(orderId) {
    if (!orderId) {
        return;
    }
    router.push({ name: "administrator-order", params: { id: String(orderId) } });
}

/**
 * @param {{ id: number|string, order_id?: number|string, order_number?: string }} row
 */
function askMarkPaid(row) {
    paidTarget.value = {
        id: Number(row.id),
        title: row.order_number || `заказ #${row.order_id}`,
    };
    confirmOpen.value = true;
}

async function confirmMarkPaid() {
    const target = paidTarget.value;
    confirmOpen.value = false;
    paidTarget.value = null;
    if (!target?.id) {
        return;
    }
    try {
        await actionApi.run(() => markPaymentPaid(target.id));
        toast.success("Отмечено оплаченным");
        await loadAll();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось отметить оплату");
    }
}

watch(branchId, () => {
    resetPage();
    loadAll();
});
watch(page, loadList);

onMounted(() => {
    applyPreset("today");
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Финансы · касса смены">
            <p
                v-if="!branchId"
                class="text-sm text-bo-muted"
            >
                У сотрудника не назначен филиал — касса недоступна.
            </p>

            <template v-else>
                <p class="mb-4 text-sm text-bo-muted">
                    Платежи филиала #{{ branchId }}. Период — по дате создания поручения. Возвраты делает владелец.
                </p>

                <div class="mb-4 flex flex-wrap gap-2">
                    <UiButton
                        v-for="preset in presets"
                        :key="preset.key"
                        class="!h-9 !px-3"
                        @click="applyPreset(preset.key)"
                    >
                        {{ preset.label }}
                    </UiButton>
                </div>

                <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <UiFormField label="Статус">
                        <select
                            v-model="filters.status"
                            class="mt-2 h-11 w-full border border-bo-border bg-bo-field px-4 text-bo-fg"
                            @change="onFilterCommit"
                        >
                            <option value="">
                                Все
                            </option>
                            <option value="pending">
                                Ожидает
                            </option>
                            <option value="paid">
                                Оплачен
                            </option>
                            <option value="refunded">
                                Возврат
                            </option>
                            <option value="cancelled">
                                Отменён
                            </option>
                        </select>
                    </UiFormField>
                    <UiFormField label="Метод">
                        <select
                            v-model="filters.method"
                            class="mt-2 h-11 w-full border border-bo-border bg-bo-field px-4 text-bo-fg"
                            @change="onFilterCommit"
                        >
                            <option value="">
                                Все
                            </option>
                            <option value="cash">
                                Наличные
                            </option>
                            <option value="card_courier">
                                Карта курьеру
                            </option>
                            <option value="card_online">
                                Онлайн
                            </option>
                        </select>
                    </UiFormField>
                </div>

                <div
                    class="mb-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4"
                    :class="{ 'opacity-50': summaryApi.loading }"
                >
                    <div class="border border-bo-border bg-bo-field px-4 py-3">
                        <div class="text-xs text-bo-muted">
                            Оплачено
                        </div>
                        <div class="mt-1 text-lg text-bo-fg">
                            {{ formatKopecks(summary?.paid_kopecks) }}
                        </div>
                        <div class="mt-1 text-xs text-bo-muted">
                            {{ summary?.paid_count ?? 0 }} шт.
                        </div>
                    </div>
                    <div class="border border-bo-border bg-bo-field px-4 py-3">
                        <div class="text-xs text-bo-muted">
                            Ожидает
                        </div>
                        <div class="mt-1 text-lg text-bo-fg">
                            {{ formatKopecks(summary?.pending_kopecks) }}
                        </div>
                        <div class="mt-1 text-xs text-bo-muted">
                            {{ summary?.pending_count ?? 0 }} шт. · отменённых {{ summary?.cancelled_count ?? 0 }}
                        </div>
                    </div>
                    <div class="border border-bo-border bg-bo-field px-4 py-3">
                        <div class="text-xs text-bo-muted">
                            Возвраты
                        </div>
                        <div class="mt-1 text-lg text-bo-fg">
                            {{ formatKopecks(summary?.refunded_kopecks) }}
                        </div>
                        <div class="mt-1 text-xs text-bo-muted">
                            {{ summary?.refunded_count ?? 0 }} шт. · только просмотр
                        </div>
                    </div>
                    <div class="border border-bo-border bg-bo-field px-4 py-3">
                        <div class="text-xs text-bo-muted">
                            По методам (paid)
                        </div>
                        <div class="mt-2 space-y-0.5 text-xs text-bo-muted">
                            <div
                                v-for="(kopecks, method) in (summary?.paid_by_method ?? {})"
                                :key="method"
                            >
                                {{ paymentMethodLabel(method) }}: {{ formatKopecks(kopecks) }}
                            </div>
                        </div>
                    </div>
                </div>

                <UiTable
                    :columns="columns"
                    :rows="rows"
                    :loading="listApi.loading"
                    empty-text="Платежей нет"
                >
                    <template #cell-order_number="{ row, value }">
                        <button
                            type="button"
                            class="text-bo-accent underline-offset-2 hover:underline"
                            @click="openOrder(row.order_id)"
                        >
                            {{ value }}
                        </button>
                    </template>
                    <template #cell-method="{ value }">
                        {{ paymentMethodLabel(value) }}
                    </template>
                    <template #cell-status="{ value }">
                        <UiBadge
                            :variant="paymentStatusVariant(value)"
                            :text="paymentStatusLabel(value)"
                        />
                    </template>
                    <template #cell-actions="{ row }">
                        <div class="flex flex-wrap gap-1">
                            <UiButton
                                class="!h-9 !px-2.5"
                                title="Открыть заказ"
                                aria-label="Открыть заказ"
                                @click.stop="openOrder(row.order_id)"
                            >
                                <ShellIcon
                                    name="external"
                                    class="h-4 w-4"
                                />
                            </UiButton>
                            <UiButton
                                class="!h-9 !px-2.5"
                                title="Отметить оплаченным"
                                aria-label="Отметить оплаченным"
                                :disabled="row.raw_status !== 'pending' || actionApi.loading"
                                @click.stop="askMarkPaid(row)"
                            >
                                <ShellIcon
                                    name="check"
                                    class="h-4 w-4"
                                />
                            </UiButton>
                        </div>
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

        <UiConfirmDialog
            :open="confirmOpen"
            title="Подтверждение"
            :message="confirmMessage"
            @confirm="confirmMarkPaid"
            @cancel="confirmOpen = false; paidTarget = null"
        />
    </div>
</template>
