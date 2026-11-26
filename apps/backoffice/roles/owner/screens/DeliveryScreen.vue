<script setup>
import { onMounted, reactive, ref, watch } from "vue";
import { getDelivery, replaceZones, storeDelivery, updateDelivery } from "../../../api/delivery.js";
import DeliveryZonesMapEditor from "../components/delivery/DeliveryZonesMapEditor.vue";
import OwnerBranchSelector from "../components/branch/OwnerBranchSelector.vue";
import { useOwnerBranchContext } from "../composables/useOwnerBranchContext.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { hasRenderableGeometry } from "../../../shell/maps/yandexGeoJsonCoords.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiInput from "../../../shell/components/ui/UiInput.vue";

const toast = useToast();
const loadApi = useApi();
const saveApi = useApi();

const deliveryExists = ref(false);
const { selectedBranchId, ensureLoaded } = useOwnerBranchContext();
const zonesEditor = ref(null);

const settingsForm = reactive({
    min_order_rubles: "",
    average_delivery_time_minutes: "",
});
const settingsErrors = reactive({});

/** @type {import('vue').Ref<Array<Record<string, unknown>>>} */
const draftZones = ref([]);
/** @type {import('vue').Ref<[number, number]|null>} */
const branchCenter = ref(null);

function clearErrors(target) {
    Object.keys(target).forEach((key) => {
        delete target[key];
    });
}

function applyBranchCenter(payload) {
    const address = payload?.branch_address;
    const lat = address?.latitude != null ? Number(address.latitude) : null;
    const lng = address?.longitude != null ? Number(address.longitude) : null;
    if (Number.isFinite(lat) && Number.isFinite(lng)) {
        branchCenter.value = [lat, lng];
        return;
    }
    branchCenter.value = null;
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
    applyBranchCenter(payload);
    draftZones.value = Array.isArray(payload?.zones)
        ? payload.zones.map((zone) => ({
              id: zone.id,
              name: zone.name,
              fee: zone.fee ?? zone.fee_rubles ?? 0,
              is_remote: Boolean(zone.is_remote),
              geometry: zone.geometry ?? null,
          }))
        : [];
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
            applyDelivery(null);
            return;
        }
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить доставку");
    }
}

function validateSettings() {
    clearErrors(settingsErrors);
    if (
        settingsForm.min_order_rubles !== "" &&
        Number.isNaN(Number(settingsForm.min_order_rubles))
    ) {
        settingsErrors.min_order_rubles = "Число";
    }
    if (
        settingsForm.average_delivery_time_minutes !== "" &&
        Number.isNaN(Number(settingsForm.average_delivery_time_minutes))
    ) {
        settingsErrors.average_delivery_time_minutes = "Число";
    }
    return Object.keys(settingsErrors).length === 0;
}

/**
 * @returns {Array<Record<string, unknown>>|null}
 */
function collectZonesOrNull() {
    const zones = zonesEditor.value?.snapshotZones?.() ?? draftZones.value;
    const incomplete = zones.some((zone) => !hasRenderableGeometry(zone.geometry));
    if (incomplete) {
        toast.error("Есть зоны без контура — нарисуй или удали");
        return null;
    }
    return zones.map((zone) => ({
        id: zone.id,
        name: zone.name,
        fee: Number(zone.fee) || 0,
        is_remote: Boolean(zone.is_remote),
        geometry: zone.geometry,
    }));
}

async function saveAll() {
    if (!selectedBranchId.value) {
        toast.error("Выбери филиал");
        return;
    }
    if (!validateSettings()) {
        toast.error("Проверьте поля настроек");
        return;
    }

    const zones = collectZonesOrNull();
    if (zones === null) {
        return;
    }

    const settingsBody = {
        min_order_rubles:
            settingsForm.min_order_rubles === "" ? null : Number(settingsForm.min_order_rubles),
        average_delivery_time_minutes:
            settingsForm.average_delivery_time_minutes === ""
                ? null
                : Number(settingsForm.average_delivery_time_minutes),
        branch_id: Number(selectedBranchId.value),
    };

    try {
        await saveApi.run(async () => {
            if (deliveryExists.value) {
                await updateDelivery(settingsBody);
            } else {
                await storeDelivery(settingsBody);
            }
            return replaceZones({
                branch_id: Number(selectedBranchId.value),
                zones,
            });
        });
        toast.success("Сохранено");
        await loadDelivery();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка сохранения");
    }
}

watch(selectedBranchId, loadDelivery);

onMounted(async () => {
    await ensureLoaded();
    await loadDelivery();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-semibold tracking-tight text-bo-fg">Доставка</h1>
                <UiButton
                    :disabled="!selectedBranchId || saveApi.loading || loadApi.loading"
                    @click="saveAll"
                >
                    Сохранить
                </UiButton>
            </div>
            <OwnerBranchSelector />
        </div>

        <p v-if="!selectedBranchId" class="text-sm text-bo-muted">
            Выбери филиал, чтобы настроить доставку.
        </p>

        <template v-else>
            <p v-if="loadApi.loading" class="text-sm text-bo-muted">Загрузка…</p>

            <template v-else>
                <UiCard title="Настройки">
                    <div class="grid gap-4 sm:grid-cols-2">
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
                    </div>
                </UiCard>

                <UiCard title="Зоны доставки">
                    <DeliveryZonesMapEditor
                        ref="zonesEditor"
                        :zones="draftZones"
                        :branch-center="branchCenter"
                        :disabled="saveApi.loading"
                    />
                </UiCard>
            </template>
        </template>
    </div>
</template>
