<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import {
    getRouteSheet,
    listRouteSheets,
    takeRouteSheet,
} from "../../../api/delivery.js";
import { listEmployees } from "../../../api/staff.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import {
    formatAddress,
    orderStatusLabel,
    sheetHint,
    sheetStatusLabel,
    sheetStatusVariant,
    stopOutcomeLabel,
    stopOutcomeVariant,
} from "../../../shell/domain/orderStage.js";

const router = useRouter();
const toast = useToast();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);
const listApi = useApi();
const detailApi = useApi();

const branchId = computed(() => {
    const raw = employee.value?.branch_id;
    const id = Number(raw);
    return Number.isFinite(id) && id > 0 ? id : null;
});

const {
    page,
    perPage,
    total,
    items: sheets,
    offset,
    applyPayload,
} = usePagedList();
const couriers = ref([]);
const sheet = ref(null);
const courierId = ref("");
const actionApi = useApi();

const canAssignCourier = computed(
    () => sheet.value?.status === "released" && !sheet.value?.courier_id,
);

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
    if (!branchId.value) {
        applyPayload({ items: [], total: 0 });
        toast.error("У сотрудника не указан филиал смены");
        return;
    }
    try {
        const payload = await listApi.run(() => listRouteSheets({
            branch_id: branchId.value,
            offset: offset.value,
            limit: perPage,
        }));
        applyPayload(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить маршрутные листы");
    }
}

function openAssemble() {
    router.push({ name: "administrator-logistics-assemble" });
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

async function onAssignCourier() {
    const id = Number(courierId.value);
    if (!id || !sheet.value?.id) {
        toast.error("Выбери курьера");
        return;
    }
    try {
        sheet.value = await actionApi.run(() => takeRouteSheet(sheet.value.id, {
            courier_id: id,
        }));
        toast.success("Курьер назначен");
        await loadSheets();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось назначить курьера");
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
                <UiButton @click="openAssemble">
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

        <UiCard
            v-if="sheet"
            :title="`Маршрутный лист #${sheet.id}`"
        >
            <div
                v-if="detailApi.loading"
                class="text-sm text-bo-muted"
            >
                Загрузка…
            </div>
            <div
                v-else
                class="space-y-5 text-sm"
            >
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
                    v-if="canAssignCourier"
                    class="space-y-3 border border-bo-border bg-white/[0.03] p-4"
                >
                    <p class="font-medium">
                        Назначить курьера
                    </p>
                    <p class="text-bo-muted">
                        Лист выпущен без курьера — добей назначение.
                    </p>
                    <UiFormField
                        label="Курьер"
                        required
                    >
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
                    <UiButton
                        :disabled="actionApi.loading"
                        @click="onAssignCourier"
                    >
                        Назначить
                    </UiButton>
                </div>

                <div class="space-y-2">
                    <p class="font-medium">
                        Остановки
                    </p>
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
                                <p
                                    v-if="stop.order?.status"
                                    class="text-bo-muted"
                                >
                                    Заказ: {{ orderStatusLabel(stop.order.status) }}
                                </p>
                                <p
                                    v-if="stop.reason"
                                    class="text-bo-muted"
                                >
                                    Причина: {{ stop.reason }}
                                </p>
                            </div>
                            <UiBadge
                                :variant="stopOutcomeVariant(stop.outcome)"
                                :text="stopOutcomeLabel(stop.outcome)"
                            />
                        </div>
                    </article>
                </div>
            </div>
        </UiCard>
    </div>
</template>
