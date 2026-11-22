<script setup>
import { computed, onMounted, ref, watch } from "vue";
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
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import {
    formatAddress,
    orderStatusLabel,
    sheetHint,
    sheetStatusLabel,
    sheetStatusVariant,
    stopIsOpen,
    stopOutcomeLabel,
    stopOutcomeVariant,
} from "../../../shell/domain/orderStage.js";

const toast = useToast();
const listApi = useApi();
const detailApi = useApi();
const actionApi = useApi();

const {
    page,
    perPage,
    total,
    items: sheets,
    offset,
    applyPayload,
} = usePagedList();
const readyOrders = ref([]);
const couriers = ref([]);
const createOpen = ref(false);
const selectedOrderIds = ref(/** @type {number[]} */ ([]));
const sheet = ref(null);
const courierId = ref("");
const writeOffOpen = ref(false);
const writeOffOrderId = ref(null);
const writeOffReason = ref("");

const columns = [
    { key: "id", label: "Лист", width: "80px" },
    { key: "status", label: "Этап", width: "140px" },
    { key: "courier_name", label: "Курьер" },
    { key: "stops_count", label: "Остановки", width: "110px" },
    { key: "next", label: "Дальше" },
];

const sheetRows = computed(() =>
    sheets.value.map((row) => ({
        id: row.id,
        status: row.status,
        courier_name: courierName(row.courier_id),
        stops_count: Array.isArray(row.stops) ? row.stops.length : 0,
        next: sheetHint(row),
    })),
);

function asList(payload) {
    return Array.isArray(payload) ? payload : (payload?.items ?? []);
}

function courierName(id) {
    if (!id) {
        return "Не назначен";
    }
    const courier = couriers.value.find((item) => Number(item.id) === Number(id));
    return courier?.name ? `${courier.name}` : `Курьер #${id}`;
}

async function loadCouriers() {
    try {
        couriers.value = asList(await listEmployees({ role: "courier", offset: 0, limit: 100 }));
    } catch {
        couriers.value = [];
    }
}

async function loadSheets() {
    try {
        const payload = await listApi.run(() => listRouteSheets({
            offset: offset.value,
            limit: perPage,
        }));
        applyPayload(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить маршрутные листы");
    }
}

async function loadReadyOrders() {
    try {
        const payload = await listOrders({ status: "ready", offset: 0, limit: 100 });
        readyOrders.value = (payload?.items ?? []).filter(
            (order) => order.delivery?.method === "courier",
        );
    } catch (err) {
        readyOrders.value = [];
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить готовые заказы");
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
        return;
    }
    selectedOrderIds.value.push(num);
}

async function onCreateSheet() {
    if (selectedOrderIds.value.length === 0) {
        toast.error("Выбери хотя бы один готовый заказ");
        return;
    }
    try {
        const created = await actionApi.run(() => createRouteSheet({
            order_ids: [...selectedOrderIds.value],
        }));
        toast.success("Маршрутный лист выпущен. Заказы ждут, пока его возьмут.");
        createOpen.value = false;
        await loadSheets();
        if (created?.id) {
            await openSheet({ id: created.id });
        }
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось выпустить лист");
    }
}

async function openSheet(row) {
    courierId.value = "";
    try {
        sheet.value = await detailApi.run(() => getRouteSheet(row.id));
    } catch (err) {
        sheet.value = null;
        toast.error(err instanceof Error ? err.message : "Не удалось открыть маршрутный лист");
    }
}

async function onTake() {
    const id = Number(courierId.value);
    if (!id) {
        toast.error("Выбери курьера");
        return;
    }
    try {
        sheet.value = await actionApi.run(() => takeRouteSheet(sheet.value.id, {
            courier_id: id,
        }));
        toast.success("Лист взят. Заказы в пути.");
        await loadSheets();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось взять лист");
    }
}

async function onDelivered(orderId) {
    try {
        sheet.value = await actionApi.run(() => settleStop(sheet.value.id, orderId, {
            outcome: "delivered",
        }));
        toast.success(`Остановка заказа #${orderId} закрыта: доставлен.`);
        await loadSheets();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось закрыть остановку");
    }
}

function askWriteOff(orderId) {
    writeOffOrderId.value = orderId;
    writeOffReason.value = "";
    writeOffOpen.value = true;
}

async function confirmWriteOff() {
    if (!String(writeOffReason.value).trim()) {
        toast.error("Нужна причина списания");
        return;
    }
    const orderId = writeOffOrderId.value;
    writeOffOpen.value = false;
    try {
        sheet.value = await actionApi.run(() => settleStop(sheet.value.id, orderId, {
            outcome: "written_off",
            reason: writeOffReason.value.trim(),
        }));
        toast.success(`Остановка заказа #${orderId} закрыта: списан.`);
        await loadSheets();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось списать заказ");
    }
}

watch(page, loadSheets);
onMounted(async () => {
    await loadCouriers();
    await loadSheets();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Маршрутные листы">
            <template #actions>
                <UiButton @click="openCreate">
                    Собрать лист
                </UiButton>
            </template>

            <UiTable
                :columns="columns"
                :rows="sheetRows"
                :loading="listApi.loading"
                empty-text="Активных маршрутных листов нет"
                @row-click="openSheet"
            >
                <template #cell-id="{ value }">
                    #{{ value }}
                </template>
                <template #cell-status="{ value }">
                    <UiBadge
                        :variant="sheetStatusVariant(value)"
                        :text="sheetStatusLabel(value)"
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

        <UiCard v-if="sheet" :title="`Маршрутный лист #${sheet.id}`">
            <div v-if="detailApi.loading" class="text-sm text-bo-muted">
                Загрузка…
            </div>
            <div v-else class="space-y-5 text-sm">
                <div class="border border-bo-border bg-white/[0.03] px-4 py-3">
                    <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-bo-muted">
                        Где лист
                    </p>
                    <p class="mt-2">
                        {{ sheetHint(sheet) }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <UiBadge
                        :variant="sheetStatusVariant(sheet.status)"
                        :text="sheetStatusLabel(sheet.status)"
                    />
                    <span class="text-bo-muted">{{ courierName(sheet.courier_id) }}</span>
                </div>

                <div
                    v-if="sheet.status === 'released'"
                    class="space-y-3 border border-bo-border bg-white/[0.03] p-4"
                >
                    <p class="font-medium">Взятие листа</p>
                    <UiFormField label="Курьер" required>
                        <select
                            v-model="courierId"
                            class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm focus:border-bo-accent focus:outline-none"
                        >
                            <option value="">Выбери курьера</option>
                            <option
                                v-for="courier in couriers"
                                :key="courier.id"
                                :value="String(courier.id)"
                            >
                                {{ courier.name }}
                            </option>
                        </select>
                    </UiFormField>
                    <UiButton :disabled="actionApi.loading" @click="onTake">
                        Взять лист
                    </UiButton>
                </div>

                <div class="space-y-2">
                    <p class="font-medium">Остановки</p>
                    <article
                        v-for="stop in (sheet.stops ?? [])"
                        :key="`${stop.order_id}-${stop.position}`"
                        class="border border-bo-border px-4 py-3"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="space-y-1">
                                <p class="font-semibold">
                                    Остановка {{ stop.position }} · {{ stop.order?.number || `заказ #${stop.order_id}` }}
                                </p>
                                <p class="text-bo-muted">
                                    {{ stop.order?.client?.name || "—" }}
                                    <span v-if="stop.order?.client?.phone"> · {{ stop.order.client.phone }}</span>
                                </p>
                                <p>{{ formatAddress(stop.order?.address) || "Адрес не указан" }}</p>
                                <p v-if="stop.order?.status" class="text-bo-muted">
                                    Заказ: {{ orderStatusLabel(stop.order.status) }}
                                </p>
                                <p v-if="stop.reason" class="text-bo-muted">
                                    Причина: {{ stop.reason }}
                                </p>
                            </div>
                            <UiBadge
                                :variant="stopOutcomeVariant(stop.outcome)"
                                :text="stopOutcomeLabel(stop.outcome)"
                            />
                        </div>
                        <div
                            v-if="sheet.status === 'in_delivery' && stopIsOpen(stop.outcome)"
                            class="mt-3 flex flex-wrap gap-2"
                        >
                            <UiButton :disabled="actionApi.loading" @click="onDelivered(stop.order_id)">
                                Доставлен
                            </UiButton>
                            <UiButton :disabled="actionApi.loading" @click="askWriteOff(stop.order_id)">
                                Списать
                            </UiButton>
                        </div>
                    </article>
                </div>
            </div>
        </UiCard>

        <UiModal :open="createOpen" title="Собрать маршрутный лист" @close="createOpen = false">
            <p class="mb-3 text-sm text-bo-muted">
                Только готовые заказы на курьера. Самовывоз сюда не попадает.
            </p>
            <div class="space-y-2 text-sm">
                <label
                    v-for="order in readyOrders"
                    :key="order.id"
                    class="flex items-start gap-3 border border-bo-border px-3 py-2"
                >
                    <input
                        type="checkbox"
                        class="mt-1"
                        :checked="selectedOrderIds.includes(order.id)"
                        @change="toggleOrder(order.id)"
                    >
                    <span>
                        <span class="font-medium">{{ order.number || `Заказ #${order.id}` }}</span>
                        · {{ order.client?.name || "—" }}
                        <span class="block text-bo-muted">{{ formatAddress(order.delivery?.address) || "Адрес не указан" }}</span>
                    </span>
                </label>
                <p v-if="readyOrders.length === 0" class="text-bo-muted">
                    Готовых заказов на курьера нет.
                </p>
            </div>
            <template #footer>
                <UiButton class="!bg-white/10 !text-bo-fg" @click="createOpen = false">
                    Закрыть
                </UiButton>
                <UiButton :disabled="actionApi.loading" @click="onCreateSheet">
                    Выпустить лист
                </UiButton>
            </template>
        </UiModal>

        <UiModal :open="writeOffOpen" title="Списать заказ" @close="writeOffOpen = false">
            <UiFormField label="Причина" required>
                <textarea
                    v-model="writeOffReason"
                    rows="4"
                    class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm focus:border-bo-accent focus:outline-none"
                    placeholder="Почему заказ не доставлен"
                />
            </UiFormField>
            <template #footer>
                <UiButton class="!bg-white/10 !text-bo-fg" @click="writeOffOpen = false">
                    Закрыть
                </UiButton>
                <UiButton :disabled="actionApi.loading" @click="confirmWriteOff">
                    Списать
                </UiButton>
            </template>
        </UiModal>
    </div>
</template>
