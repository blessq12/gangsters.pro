<script setup>
import { computed, onMounted, ref } from "vue";
import {
    createRouteSheet,
    getRouteSheet,
    listRouteSheets,
    settleStop,
    takeRouteSheet,
} from "../../../api/delivery.js";
import { listOrders } from "../../../api/orders.js";
import { listEmployees } from "../../../api/staff.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";

const toast = useToast();
const listApi = useApi();
const detailApi = useApi();
const actionApi = useApi();

const sheets = ref([]);
const readyOrders = ref([]);
const couriers = ref([]);

const createOpen = ref(false);
const selectedOrderIds = ref(/** @type {number[]} */ ([]));

const detailOpen = ref(false);
const sheet = ref(null);
const courierId = ref("");

const problemOpen = ref(false);
const problemOrderId = ref(null);
const problemReason = ref("");

const columns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "status", label: "Статус", width: "120px" },
    { key: "courier_id", label: "Курьер", width: "100px" },
    { key: "stops_count", label: "Остановки", width: "100px" },
];

const statusVariant = {
    released: "warning",
    in_delivery: "warning",
    completed: "success",
};

const sheetRows = computed(() =>
    sheets.value.map((row) => ({
        ...row,
        courier_id: row.courier_id ?? "—",
        stops_count: Array.isArray(row.stops) ? row.stops.length : 0,
    })),
);

function asList(payload) {
    return Array.isArray(payload) ? payload : (payload?.items ?? []);
}

async function loadSheets() {
    try {
        const payload = await listApi.run(() => listRouteSheets());
        sheets.value = asList(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить листы");
    }
}

async function loadReadyOrders() {
    try {
        const payload = await actionApi.run(() => listOrders({
            status: "ready",
            offset: 0,
            limit: 100,
        }));
        readyOrders.value = (payload?.items ?? []).filter(
            (order) => order.delivery?.method === "courier"
                || order.delivery?.method === "delivery"
                || !order.delivery?.method,
        );
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить ready-заказы");
    }
}

async function loadCouriers() {
    try {
        const payload = await actionApi.run(() => listEmployees({ role: "courier" }));
        couriers.value = asList(payload);
    } catch {
        couriers.value = [];
    }
}

async function openCreate() {
    selectedOrderIds.value = [];
    createOpen.value = true;
    await loadReadyOrders();
}

function toggleOrder(id) {
    const num = Number(id);
    const idx = selectedOrderIds.value.indexOf(num);
    if (idx >= 0) {
        selectedOrderIds.value.splice(idx, 1);
    } else {
        selectedOrderIds.value.push(num);
    }
}

async function onCreateSheet() {
    if (selectedOrderIds.value.length === 0) {
        toast.error("Выбери хотя бы один заказ");
        return;
    }
    try {
        await actionApi.run(() => createRouteSheet({
            order_ids: [...selectedOrderIds.value],
        }));
        toast.success("Маршрут создан");
        createOpen.value = false;
        await loadSheets();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

async function openSheet(row) {
    sheet.value = null;
    courierId.value = "";
    detailOpen.value = true;
    try {
        sheet.value = await detailApi.run(() => getRouteSheet(row.id));
        if (sheet.value?.status === "released") {
            await loadCouriers();
        }
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить лист");
        detailOpen.value = false;
    }
}

async function onTake() {
    const id = Number(courierId.value);
    if (!id) {
        toast.error("Укажи courier_id");
        return;
    }
    try {
        sheet.value = await actionApi.run(() => takeRouteSheet(sheet.value.id, {
            courier_id: id,
        }));
        toast.success("Курьер назначен");
        await loadSheets();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

async function onDelivered(orderId) {
    try {
        sheet.value = await actionApi.run(() => settleStop(sheet.value.id, orderId, {
            outcome: "delivered",
        }));
        toast.success(`#${orderId} доставлен`);
        await loadSheets();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

function askProblem(orderId) {
    problemOrderId.value = orderId;
    problemReason.value = "";
    problemOpen.value = true;
}

async function confirmProblem() {
    if (!String(problemReason.value).trim()) {
        toast.error("Укажи причину");
        return;
    }
    const orderId = problemOrderId.value;
    problemOpen.value = false;
    try {
        sheet.value = await actionApi.run(() => settleStop(sheet.value.id, orderId, {
            outcome: "written_off",
            reason: problemReason.value.trim(),
        }));
        toast.success(`#${orderId}: списан`);
        await loadSheets();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

onMounted(loadSheets);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Логистика">
            <template #actions>
                <UiButton @click="openCreate">+ Маршрут</UiButton>
            </template>

            <UiTable
                :columns="columns"
                :rows="sheetRows"
                :loading="listApi.loading"
                empty-text="Нет маршрутных листов"
                @row-click="openSheet"
            >
                <template #cell-status="{ value }">
                    <UiBadge
                        :variant="statusVariant[value] || 'neutral'"
                        :text="String(value || '—')"
                    />
                </template>
            </UiTable>
        </UiCard>

        <UiModal :open="createOpen" title="Новый маршрутный лист" @close="createOpen = false">
            <div class="space-y-2 text-sm">
                <p class="text-bo-muted">Заказы в статусе ready:</p>
                <label
                    v-for="order in readyOrders"
                    :key="order.id"
                    class="flex items-center gap-2 border border-bo-border bg-white/[0.03] px-3 py-2"
                >
                    <input
                        type="checkbox"
                        :checked="selectedOrderIds.includes(order.id)"
                        @change="toggleOrder(order.id)"
                    >
                    #{{ order.id }} · {{ order.client?.name || "—" }} · {{ order.delivery?.method || "—" }}
                </label>
                <p v-if="readyOrders.length === 0" class="text-bo-muted">
                    Нет ready-заказов
                </p>
            </div>
            <template #footer>
                <UiButton @click="createOpen = false">Отмена</UiButton>
                <UiButton :disabled="actionApi.loading" @click="onCreateSheet">
                    Создать
                </UiButton>
            </template>
        </UiModal>

        <UiModal :open="detailOpen" title="Маршрутный лист" @close="detailOpen = false">
            <div v-if="detailApi.loading" class="text-sm text-bo-muted">
                Загрузка…
            </div>
            <div v-else-if="sheet" class="space-y-4 text-sm">
                <p><span class="text-bo-muted">ID:</span> {{ sheet.id }}</p>
                <p><span class="text-bo-muted">Статус:</span> {{ sheet.status }}</p>
                <p><span class="text-bo-muted">Курьер:</span> {{ sheet.courier_id ?? "—" }}</p>

                <div v-if="sheet.status === 'released'" class="space-y-2 border border-bo-border bg-white/[0.03] p-4">
                    <p class="font-medium">Назначить курьера</p>
                    <UiFormField v-if="couriers.length" label="Курьер">
                        <select
                            v-model="courierId"
                            class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none"
                        >
                            <option value="">—</option>
                            <option
                                v-for="courier in couriers"
                                :key="courier.id"
                                :value="String(courier.id)"
                            >
                                {{ courier.id }} — {{ courier.name }}
                            </option>
                        </select>
                    </UiFormField>
                    <UiFormField v-else label="courier_id">
                        <UiInput v-model="courierId" type="number" />
                    </UiFormField>
                    <UiButton :disabled="actionApi.loading" @click="onTake">
                        Назначить курьера
                    </UiButton>
                </div>

                <div>
                    <p class="mb-2 font-medium">Стопы</p>
                    <ul class="space-y-2">
                        <li
                            v-for="stop in (sheet.stops ?? [])"
                            :key="`${stop.order_id}-${stop.position}`"
                            class="border border-bo-border bg-white/[0.03] px-3 py-2.5"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span>
                                    #{{ stop.order_id }} · pos {{ stop.position }} ·
                                    {{ stop.outcome || "pending" }}
                                </span>
                                <div
                                    v-if="sheet.status === 'in_delivery' && (!stop.outcome || stop.outcome === 'pending')"
                                    class="flex gap-1"
                                >
                                    <UiButton @click="onDelivered(stop.order_id)">
                                        Доставлен
                                    </UiButton>
                                    <UiButton @click="askProblem(stop.order_id)">
                                        Проблема
                                    </UiButton>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <template #footer>
                <UiButton @click="detailOpen = false">Закрыть</UiButton>
            </template>
        </UiModal>

        <UiModal :open="problemOpen" title="Причина проблемы" @close="problemOpen = false">
            <UiFormField label="Причина" required>
                <UiInput v-model="problemReason" />
            </UiFormField>
            <template #footer>
                <UiButton @click="problemOpen = false">Отмена</UiButton>
                <UiButton :disabled="actionApi.loading" @click="confirmProblem">
                    Подтвердить
                </UiButton>
            </template>
        </UiModal>
    </div>
</template>
