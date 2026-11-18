<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import {
    acceptOrder,
    changeOrderStatus,
    completePickup,
    getOrder,
    listOrders,
} from "../../../api/orders.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const router = useRouter();
const toast = useToast();
const listApi = useApi();
const detailApi = useApi();
const actionApi = useApi();

const page = ref(1);
const perPage = 20;
const total = ref(0);
const statusFilter = ref("");
const rows = ref([]);
const detailOpen = ref(false);
const detail = ref(null);
const cancelConfirmOpen = ref(false);

const statusOptions = [
    "",
    "new",
    "confirmed",
    "preparing",
    "ready",
    "in_transit",
    "delivered",
    "completed",
    "cancelled",
];

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

const detailLines = computed(() => detail.value?.cart?.lines ?? []);
const detailStatus = computed(() => detail.value?.status ?? "");
const deliveryMethod = computed(() => detail.value?.delivery?.method ?? "");

const canAccept = computed(() => detailStatus.value === "new");
const canPreparing = computed(() => detailStatus.value === "confirmed");
const canCompletePickup = computed(
    () => detailStatus.value === "ready" && deliveryMethod.value === "pickup",
);
const canToRoute = computed(
    () => detailStatus.value === "ready"
        && (deliveryMethod.value === "courier" || deliveryMethod.value === "delivery"),
);
const canCancel = computed(
    () => detail.value
        && !["completed", "cancelled"].includes(detailStatus.value),
);

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
            status: statusFilter.value || undefined,
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

async function refreshDetail() {
    if (!detail.value?.id) {
        return;
    }
    detail.value = await detailApi.run(() => getOrder(detail.value.id));
    await load();
}

async function onAccept() {
    try {
        await actionApi.run(() => acceptOrder(detail.value.id));
        toast.success("Заказ принят");
        await refreshDetail();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

async function onPreparing() {
    try {
        await actionApi.run(() => changeOrderStatus(detail.value.id, {
            target_status: "preparing",
        }));
        toast.success("Статус: preparing");
        await refreshDetail();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

async function onCompletePickup() {
    try {
        await actionApi.run(() => completePickup(detail.value.id));
        toast.success("Выдан");
        await refreshDetail();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

function onToRoute() {
    toast.success("Открой Logistics и создай маршрутный лист");
    router.push({ name: "administrator-logistics" });
}

function askCancel() {
    cancelConfirmOpen.value = true;
}

async function confirmCancel() {
    cancelConfirmOpen.value = false;
    try {
        await actionApi.run(() => changeOrderStatus(detail.value.id, {
            target_status: "cancelled",
        }));
        toast.success("Отменён");
        await refreshDetail();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

watch(statusFilter, () => {
    page.value = 1;
    load();
});
watch(page, load);
onMounted(load);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Заказы онлайн">
            <div class="mb-6 max-w-xs">
                <label class="mb-1 block text-sm text-bo-muted">Статус</label>
                <select
                    v-model="statusFilter"
                    class="w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none text-sm"
                >
                    <option value="">Все</option>
                    <option
                        v-for="status in statusOptions.filter(Boolean)"
                        :key="status"
                        :value="status"
                    >
                        {{ status }}
                    </option>
                </select>
            </div>

            <UiTable
                :columns="columns"
                :rows="rows"
                :loading="listApi.loading"
                empty-text="Заказов нет"
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

        <UiModal :open="detailOpen" title="Заказ" @close="detailOpen = false">
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
                <p>
                    <span class="text-bo-muted">Клиент:</span>
                    {{ detail.client?.name || "—" }} · {{ detail.client?.phone || "" }}
                </p>
                <p>
                    <span class="text-bo-muted">Доставка:</span>
                    {{ detail.delivery?.method || "—" }}
                </p>
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

            <template #footer>
                <UiButton @click="detailOpen = false">Закрыть</UiButton>
                <UiButton
                    v-if="canAccept"
                    :disabled="actionApi.loading"
                    @click="onAccept"
                >
                    Принять
                </UiButton>
                <UiButton
                    v-if="canPreparing"
                    :disabled="actionApi.loading"
                    @click="onPreparing"
                >
                    Готовится
                </UiButton>
                <UiButton
                    v-if="canCompletePickup"
                    :disabled="actionApi.loading"
                    @click="onCompletePickup"
                >
                    Выдан
                </UiButton>
                <UiButton v-if="canToRoute" @click="onToRoute">
                    В маршрут
                </UiButton>
                <UiButton
                    v-if="canCancel"
                    :disabled="actionApi.loading"
                    @click="askCancel"
                >
                    Отменить
                </UiButton>
            </template>
        </UiModal>

        <UiConfirmDialog
            :open="cancelConfirmOpen"
            title="Отменить заказ?"
            message="Статус станет cancelled."
            @confirm="confirmCancel"
            @cancel="cancelConfirmOpen = false"
        />
    </div>
</template>
