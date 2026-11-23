<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { createRouteSheet, takeRouteSheet } from "../../../api/delivery.js";
import { listOrders } from "../../../api/orders.js";
import { listEmployees } from "../../../api/staff.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { formatAddress, orderNumberOf } from "../../../shell/domain/orderStage.js";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import RouteOrdersMap from "../../../shell/components/order/RouteOrdersMap.vue";

const router = useRouter();
const toast = useToast();
const loadApi = useApi();
const actionApi = useApi();

const readyOrders = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const couriers = ref(/** @type {Array<Record<string, unknown>>} */ ([]));
const selectedOrderIds = ref(/** @type {number[]} */ ([]));
const courierId = ref("");

const mapOrders = computed(() =>
    readyOrders.value.map((order) => {
        const address = order?.delivery?.address ?? order?.address ?? null;
        return {
            id: Number(order.id),
            title: orderNumberOf(order) || `Заказ #${order.id}`,
            subtitle: String(order?.client?.name ?? "").trim() || "—",
            street: address?.street ? String(address.street) : "",
            house: address?.house ? String(address.house) : "",
            city: address?.city ? String(address.city) : "",
            latitude: address?.lat ?? address?.latitude ?? null,
            longitude: address?.lng ?? address?.longitude ?? null,
        };
    }),
);

const selectedCount = computed(() => selectedOrderIds.value.length);

function asList(payload) {
    return Array.isArray(payload) ? payload : (payload?.items ?? []);
}

function isSelected(id) {
    return selectedOrderIds.value.includes(Number(id));
}

function toggleOrder(id) {
    const num = Number(id);
    if (!Number.isFinite(num) || num <= 0) {
        return;
    }
    const idx = selectedOrderIds.value.indexOf(num);
    if (idx >= 0) {
        selectedOrderIds.value = selectedOrderIds.value.filter((item) => item !== num);
        return;
    }
    selectedOrderIds.value = [...selectedOrderIds.value, num];
}

async function loadCouriers() {
    try {
        couriers.value = asList(await listEmployees({ role: "courier", offset: 0, limit: 100 }));
    } catch {
        couriers.value = [];
    }
}

async function loadReadyOrders() {
    try {
        const payload = await loadApi.run(() => listOrders({ status: "ready", offset: 0, limit: 100 }));
        readyOrders.value = (payload?.items ?? []).filter(
            (order) => order.delivery?.method === "courier",
        );
    } catch (err) {
        readyOrders.value = [];
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить готовые заказы");
    }
}

function goBack() {
    router.push({ name: "administrator-logistics" });
}

async function onSubmit() {
    if (selectedOrderIds.value.length === 0) {
        toast.error("Выбери хотя бы один готовый заказ");
        return;
    }
    const courier = Number(courierId.value);
    if (!courier) {
        toast.error("Выбери курьера");
        return;
    }

    try {
        const created = await actionApi.run(() => createRouteSheet({
            order_ids: [...selectedOrderIds.value],
        }));
        const sheetId = created?.id;
        if (!sheetId) {
            throw new Error("Лист создан без id");
        }
        await actionApi.run(() => takeRouteSheet(sheetId, { courier_id: courier }));
        toast.success("Лист выпущен и назначен курьеру");
        goBack();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось выпустить лист");
    }
}

onMounted(async () => {
    await Promise.all([loadCouriers(), loadReadyOrders()]);
});
</script>

<template>
    <div class="mx-auto flex h-[calc(100vh-4rem)] w-full max-w-[1600px] flex-col gap-4 px-4 py-5 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-bo-fg">
                    Собрать маршрутный лист
                </h1>
                <p class="mt-1 text-sm text-bo-muted">
                    Готовые заказы на курьера. Точки на карте — клик выбирает заказ.
                </p>
            </div>
            <UiButton
                class="!bg-white/10 !text-bo-fg"
                @click="goBack"
            >
                К списку листов
            </UiButton>
        </div>

        <div class="grid min-h-0 flex-1 gap-4 lg:grid-cols-3">
            <aside class="flex min-h-0 flex-col gap-4 border border-bo-border bg-bo-elevated lg:col-span-1">
                <div class="border-b border-bo-border px-4 py-3">
                    <p class="text-sm font-medium text-bo-fg">
                        Заказы
                        <span class="text-bo-muted">· выбрано {{ selectedCount }}</span>
                    </p>
                </div>

                <div class="min-h-0 flex-1 space-y-2 overflow-y-auto px-3 pb-2">
                    <p
                        v-if="loadApi.loading"
                        class="px-1 py-4 text-sm text-bo-muted"
                    >
                        Загрузка…
                    </p>
                    <p
                        v-else-if="readyOrders.length === 0"
                        class="px-1 py-4 text-sm text-bo-muted"
                    >
                        Готовых заказов на курьера нет.
                    </p>
                    <label
                        v-for="order in readyOrders"
                        :key="String(order.id)"
                        class="flex cursor-pointer items-start gap-3 border px-3 py-2.5 text-sm transition-colors"
                        :class="isSelected(order.id)
                            ? 'border-bo-accent bg-bo-accent/10'
                            : 'border-bo-border hover:bg-white/[0.04]'"
                    >
                        <input
                            type="checkbox"
                            class="mt-1 accent-bo-accent"
                            :checked="isSelected(order.id)"
                            @change="toggleOrder(order.id)"
                        >
                        <span class="min-w-0">
                            <span class="font-medium text-bo-fg">
                                {{ order.number || `Заказ #${order.id}` }}
                            </span>
                            <span class="mt-0.5 block text-bo-muted">
                                {{ order.client?.name || "—" }}
                                <template v-if="order.client?.phone"> · {{ order.client.phone }}</template>
                            </span>
                            <span class="mt-0.5 block text-xs text-bo-muted">
                                {{ formatAddress(order.delivery?.address) || "Адрес не указан" }}
                            </span>
                        </span>
                    </label>
                </div>

                <div class="space-y-3 border-t border-bo-border px-4 py-4">
                    <UiFormField
                        label="Курьер"
                        required
                    >
                        <select
                            v-model="courierId"
                            class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-sm text-bo-fg focus:border-bo-accent focus:outline-none"
                        >
                            <option value="">Выбери курьера</option>
                            <option
                                v-for="courier in couriers"
                                :key="String(courier.id)"
                                :value="String(courier.id)"
                            >
                                {{ courier.name }}
                            </option>
                        </select>
                    </UiFormField>
                    <UiButton
                        class="w-full"
                        :disabled="actionApi.loading || selectedCount === 0 || !courierId"
                        @click="onSubmit"
                    >
                        Выпустить и назначить
                    </UiButton>
                </div>
            </aside>

            <div class="min-h-[24rem] lg:col-span-2 lg:min-h-0">
                <RouteOrdersMap
                    :orders="mapOrders"
                    :selected-ids="selectedOrderIds"
                    @toggle="toggleOrder"
                />
            </div>
        </div>
    </div>
</template>
