<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import {
    getDelivery,
    getRouteSheet,
    listRouteSheets,
    listZones,
    storeDelivery,
    storeZone,
    updateDelivery,
    updateZone,
} from "../../../api/delivery.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import UiTable from "../../../shell/components/ui/UiTable.vue";
import OwnerBranchSelector from "../components/branch/OwnerBranchSelector.vue";
import { useOwnerBranchContext } from "../composables/useOwnerBranchContext.js";

const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();
const detailApi = useApi();

const tab = ref("settings");
const deliveryExists = ref(false);
const { selectedBranchId, ensureLoaded } = useOwnerBranchContext();
const {
    page: zonesPage,
    perPage: zonesPerPage,
    total: zonesTotal,
    items: zones,
    offset: zonesOffset,
    resetPage: resetZonesPage,
    applyPayload: applyZones,
    clearItems: clearZones,
} = usePagedList();
const {
    page: routesPage,
    perPage: routesPerPage,
    total: routesTotal,
    items: routeSheets,
    offset: routesOffset,
    applyPayload: applyRouteSheets,
} = usePagedList();
const sheetDetail = ref(null);
const sheetModalOpen = ref(false);

const settingsForm = reactive({
    min_order_rubles: "",
    average_delivery_time_minutes: "",
});
const settingsErrors = reactive({});

const zoneModalOpen = ref(false);
const editingZoneId = ref(null);
const zoneErrors = reactive({});
const zoneForm = reactive({
    name: "",
    fee: "",
    is_remote: false,
    geometryJson: "",
});

const tabs = [
    { id: "settings", label: "Настройки доставки" },
    { id: "zones", label: "Зоны" },
    { id: "routes", label: "Маршрутные листы" },
];

const zoneColumns = [
    { key: "id", label: "ID", width: "120px" },
    { key: "name", label: "Название" },
    { key: "fee", label: "Тариф", width: "90px" },
    { key: "is_remote", label: "Удалённая", width: "110px" },
    { key: "actions", label: "", width: "120px" },
];

const routeColumns = [
    { key: "id", label: "ID", width: "64px" },
    { key: "status", label: "Статус", width: "120px" },
    { key: "courier", label: "Курьер", width: "100px" },
    { key: "stops_count", label: "Остановки", width: "100px" },
];

const routeRows = computed(() =>
    routeSheets.value.map((row) => ({
        ...row,
        courier: row.courier_id ?? "—",
        stops_count: Array.isArray(row.stops) ? row.stops.length : 0,
    })),
);

const zoneModalTitle = computed(() =>
    editingZoneId.value ? "Редактировать зону" : "Новая зона",
);

function clearErrors(target) {
    Object.keys(target).forEach((key) => {
        delete target[key];
    });
}

function applyDelivery(payload) {
    deliveryExists.value = Boolean(payload?.settings);
    settingsForm.min_order_rubles =
        payload?.settings?.min_order_rubles != null
            ? String(payload.settings.min_order_rubles)
            : "";
    settingsForm.average_delivery_time_minutes =
        payload?.settings?.average_delivery_time_minutes != null
            ? String(payload.settings.average_delivery_time_minutes)
            : "";
}

async function loadDelivery() {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        applyDelivery(null);
        return;
    }
    try {
        const payload = await loadApi.run(() => getDelivery(branchId));
        if (branchId === selectedBranchId.value) {
            applyDelivery(payload);
        }
    } catch (err) {
        if (err?.status === 404) {
            // у филиала ещё нет настроек доставки — форма для создания
            applyDelivery(null);
            return;
        }
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить доставку");
    }
}

async function loadZones() {
    const branchId = selectedBranchId.value;
    if (!branchId) {
        clearZones();
        return;
    }
    try {
        const payload = await loadApi.run(() => listZones(branchId, {
            offset: zonesOffset.value,
            limit: zonesPerPage,
        }));
        if (branchId === selectedBranchId.value) {
            applyZones(payload);
        }
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить зоны");
    }
}

async function loadRouteSheets() {
    if (!selectedBranchId.value) {
        applyRouteSheets({ items: [], total: 0 });
        return;
    }
    try {
        const payload = await loadApi.run(() => listRouteSheets({
            branch_id: Number(selectedBranchId.value),
            offset: routesOffset.value,
            limit: routesPerPage,
        }));
        applyRouteSheets(payload);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить маршрутные листы");
    }
}

function validateSettings() {
    clearErrors(settingsErrors);
    if (settingsForm.min_order_rubles !== "" && Number.isNaN(Number(settingsForm.min_order_rubles))) {
        settingsErrors.min_order_rubles = "Число";
    }
    if (
        settingsForm.average_delivery_time_minutes !== ""
        && Number.isNaN(Number(settingsForm.average_delivery_time_minutes))
    ) {
        settingsErrors.average_delivery_time_minutes = "Число";
    }
    return Object.keys(settingsErrors).length === 0;
}

async function saveSettings() {
    if (!selectedBranchId.value) {
        toast.error("Выбери филиал");
        return;
    }
    if (!validateSettings()) {
        toast.error("Проверьте поля");
        return;
    }

    const body = {
        min_order_rubles: settingsForm.min_order_rubles === ""
            ? null
            : Number(settingsForm.min_order_rubles),
        average_delivery_time_minutes: settingsForm.average_delivery_time_minutes === ""
            ? null
            : Number(settingsForm.average_delivery_time_minutes),
        branch_id: Number(selectedBranchId.value),
    };

    try {
        await saveApi.run(() =>
            deliveryExists.value ? updateDelivery(body) : storeDelivery(body),
        );
        toast.success("Настройки сохранены");
        await loadDelivery();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

function resetZoneForm() {
    zoneForm.name = "";
    zoneForm.fee = "";
    zoneForm.is_remote = false;
    zoneForm.geometryJson = "";
    clearErrors(zoneErrors);
}

function openCreateZone() {
    editingZoneId.value = null;
    resetZoneForm();
    zoneModalOpen.value = true;
}

function openEditZone(row) {
    editingZoneId.value = row.id;
    resetZoneForm();
    zoneForm.name = row.name ?? "";
    zoneForm.fee = row.fee != null ? String(row.fee) : "";
    zoneForm.is_remote = Boolean(row.is_remote);
    zoneForm.geometryJson = row.geometry ? JSON.stringify(row.geometry, null, 2) : "";
    zoneModalOpen.value = true;
}

function validateZone() {
    clearErrors(zoneErrors);
    if (!String(zoneForm.name).trim()) {
        zoneErrors.name = "Обязательное поле";
    }
    if (zoneForm.fee === "" || Number.isNaN(Number(zoneForm.fee))) {
        zoneErrors.fee = "Укажите тариф";
    }
    if (!editingZoneId.value && !zoneForm.geometryJson.trim()) {
        zoneErrors.geometry = "GeoJSON обязателен при создании";
    }
    if (zoneForm.geometryJson.trim()) {
        try {
            const parsed = JSON.parse(zoneForm.geometryJson);
            if (!parsed || typeof parsed !== "object" || Array.isArray(parsed)) {
                zoneErrors.geometry = "Ожидается объект GeoJSON";
            }
        } catch {
            zoneErrors.geometry = "Невалидный JSON";
        }
    }
    return Object.keys(zoneErrors).length === 0;
}

async function saveZone() {
    if (!selectedBranchId.value) {
        toast.error("Выбери филиал");
        return;
    }
    if (!validateZone()) {
        toast.error("Проверьте обязательные поля");
        return;
    }

    const body = {
        branch_id: Number(selectedBranchId.value),
        name: zoneForm.name.trim(),
        fee: Number(zoneForm.fee),
        is_remote: Boolean(zoneForm.is_remote),
    };
    if (zoneForm.geometryJson.trim()) {
        body.geometry = JSON.parse(zoneForm.geometryJson);
    }

    try {
        await saveApi.run(() =>
            editingZoneId.value
                ? updateZone(editingZoneId.value, body)
                : storeZone(body),
        );
        toast.success(editingZoneId.value ? "Зона сохранена" : "Зона создана");
        zoneModalOpen.value = false;
        await loadZones();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

async function openRouteSheet(row) {
    sheetDetail.value = null;
    sheetModalOpen.value = true;
    try {
        sheetDetail.value = await detailApi.run(() => getRouteSheet(row.id));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить лист");
        sheetModalOpen.value = false;
    }
}

async function onTab(id) {
    tab.value = id;
    if (id === "routes") {
        await loadRouteSheets();
    }
}

watch(zonesPage, loadZones);
watch(routesPage, loadRouteSheets);
watch(selectedBranchId, async () => {
    if (tab.value === "routes") {
        await loadRouteSheets();
    }
    if (zonesPage.value === 1) {
        await Promise.all([loadDelivery(), loadZones()]);
        return;
    }
    resetZonesPage();
    await loadDelivery();
});
onMounted(async () => {
    await ensureLoaded();
    await Promise.all([loadDelivery(), loadZones()]);
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-8 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <UiCard title="Доставка">
            <template #actions>
                <OwnerBranchSelector />
            </template>
            <div class="mb-8 flex flex-wrap gap-2">
                <button
                    v-for="item in tabs"
                    :key="item.id"
                    type="button"
                    class="h-10 px-4 text-sm font-medium transition-colors duration-150"
                    :class="tab === item.id
                        ? 'bg-bo-accent text-white'
                        : 'bg-white/[0.04] text-bo-muted hover:bg-white/[0.08] hover:text-bo-fg'"
                    @click="onTab(item.id)"
                >
                    {{ item.label }}
                </button>
            </div>

            <p
                v-if="!selectedBranchId && tab !== 'routes'"
                class="text-sm text-bo-muted"
            >
                Выбери филиал, чтобы настроить доставку.
            </p>

            <div v-else-if="tab === 'settings'" class="space-y-5">
                <UiFormField
                    label="Мин. заказ (₽)"
                    :error="settingsErrors.min_order_rubles"
                >
                    <UiInput v-model="settingsForm.min_order_rubles" type="number" />
                </UiFormField>
                <UiFormField
                    label="Среднее время (мин)"
                    :error="settingsErrors.average_delivery_time_minutes"
                >
                    <UiInput
                        v-model="settingsForm.average_delivery_time_minutes"
                        type="number"
                    />
                </UiFormField>
                <UiButton :disabled="saveApi.loading" @click="saveSettings">
                    Сохранить
                </UiButton>
            </div>

            <div v-else-if="tab === 'zones'" class="space-y-5">
                <div class="flex justify-end">
                    <UiButton @click="openCreateZone">+ Зона</UiButton>
                </div>
                <UiTable
                    :columns="zoneColumns"
                    :rows="zones"
                    :loading="loadApi.loading"
                    empty-text="Нет зон"
                >
                    <template #cell-is_remote="{ value }">
                        <UiBadge
                            :variant="value ? 'warning' : 'neutral'"
                            :text="value ? 'да' : 'нет'"
                        />
                    </template>
                    <template #cell-actions="{ row }">
                        <UiButton @click.stop="openEditZone(row)">Редактировать</UiButton>
                    </template>
                </UiTable>
                <UiPagination
                    :page="zonesPage"
                    :per-page="zonesPerPage"
                    :total="zonesTotal"
                    @update:page="zonesPage = $event"
                />
            </div>

            <div v-else class="space-y-5">
                <UiTable
                    :columns="routeColumns"
                    :rows="routeRows"
                    :loading="loadApi.loading"
                    empty-text="Нет маршрутных листов"
                    @row-click="openRouteSheet"
                >
                    <template #cell-status="{ value }">
                        <UiBadge variant="neutral" :text="String(value ?? '—')" />
                    </template>
                </UiTable>
                <UiPagination
                    :page="routesPage"
                    :per-page="routesPerPage"
                    :total="routesTotal"
                    @update:page="routesPage = $event"
                />
            </div>
        </UiCard>

        <UiModal
            :open="zoneModalOpen"
            :title="zoneModalTitle"
            @close="zoneModalOpen = false"
        >
            <div class="space-y-5">
                <UiFormField label="Название" required :error="zoneErrors.name">
                    <UiInput v-model="zoneForm.name" />
                </UiFormField>
                <UiFormField label="Тариф (₽)" required :error="zoneErrors.fee">
                    <UiInput v-model="zoneForm.fee" type="number" />
                </UiFormField>
                <UiFormField label="Удалённая зона">
                    <input v-model="zoneForm.is_remote" type="checkbox" class="mt-2">
                </UiFormField>
                <UiFormField
                    label="Geometry (GeoJSON)"
                    :required="!editingZoneId"
                    :error="zoneErrors.geometry"
                >
                    <textarea
                        v-model="zoneForm.geometryJson"
                        rows="8"
                        class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none font-mono text-xs"
                    />
                </UiFormField>
            </div>
            <template #footer>
                <UiButton @click="zoneModalOpen = false">Отмена</UiButton>
                <UiButton :disabled="saveApi.loading" @click="saveZone">Сохранить</UiButton>
            </template>
        </UiModal>

        <UiModal
            :open="sheetModalOpen"
            title="Маршрутный лист"
            @close="sheetModalOpen = false"
        >
            <div v-if="detailApi.loading" class="text-sm text-bo-muted">
                Загрузка…
            </div>
            <div v-else-if="sheetDetail" class="space-y-2 text-sm">
                <p><span class="text-bo-muted">ID:</span> {{ sheetDetail.id }}</p>
                <p><span class="text-bo-muted">Статус:</span> {{ sheetDetail.status }}</p>
                <p><span class="text-bo-muted">Курьер:</span> {{ sheetDetail.courier_id ?? "—" }}</p>
                <div>
                    <p class="mb-1 font-medium">Остановки</p>
                    <ul class="space-y-1">
                        <li
                            v-for="stop in (sheetDetail.stops ?? [])"
                            :key="`${stop.order_id}-${stop.position}`"
                            class="border border-bo-border bg-white/[0.03] px-3 py-2"
                        >
                            #{{ stop.order_id }}
                            · pos {{ stop.position }}
                            · {{ stop.outcome ?? "—" }}
                        </li>
                    </ul>
                </div>
            </div>
            <template #footer>
                <UiButton @click="sheetModalOpen = false">Закрыть</UiButton>
            </template>
        </UiModal>
    </div>
</template>
