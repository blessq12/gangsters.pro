<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { getBranch } from "../../../api/company.js";
import {
    getRouteSheet,
    settleStop,
} from "../../../api/delivery.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import { OrderNotesTrigger } from "../../../shell/modules/orderNotes/index.js";
import RouteOrdersMap from "../../../shell/components/order/RouteOrdersMap.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import {
    formatAddress,
    paymentMethodLabel,
    sheetStatusLabel,
    sheetStatusVariant,
    stopIsOpen,
    stopOutcomeLabel,
    stopOutcomeVariant,
} from "../../../shell/domain/orderStage.js";

const PAYMENT_OPTIONS = [
    { value: "cash", label: "Наличные" },
    { value: "card_courier", label: "Карта курьеру" },
    { value: "card_online", label: "Карта онлайн" },
];

const route = useRoute();
const router = useRouter();
const toast = useToast();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);
const detailApi = useApi();
const actionApi = useApi();

const sheet = ref(/** @type {Record<string, unknown>|null} */ (null));
const writeOffOpen = ref(false);
const writeOffOrderId = ref(/** @type {number|null} */ (null));
const writeOffReason = ref("");
const deliverOpen = ref(false);
const deliverOrderId = ref(/** @type {number|null} */ (null));
const deliverPaymentMethod = ref("cash");
const mapShell = ref(/** @type {HTMLElement|null} */ (null));
const mapRef = ref(/** @type {{ refreshViewport?: () => void }|null} */ (null));
const mapFullscreen = ref(false);
const fullscreenSupported = ref(false);
const branchCity = ref("");

const sheetId = computed(() => {
    const id = Number(route.params.id);
    return Number.isFinite(id) && id > 0 ? id : null;
});

const branchId = computed(() => {
    const raw = employee.value?.branch_id;
    const id = Number(raw);
    return Number.isFinite(id) && id > 0 ? id : null;
});

const mapOrders = computed(() => {
    const stops = Array.isArray(sheet.value?.stops) ? sheet.value.stops : [];
    return stops.map((stop) => {
        const order = stop?.order ?? null;
        const address = order?.address ?? order?.delivery?.address ?? null;
        return {
            id: Number(stop.order_id),
            title: order?.number || `Заказ #${stop.order_id}`,
            subtitle: String(order?.client?.name ?? "").trim() || `Остановка ${stop.position}`,
            street: address?.street ? String(address.street) : "",
            house: address?.house ? String(address.house) : "",
            city: address?.city ? String(address.city) : branchCity.value,
            latitude: address?.lat ?? address?.latitude ?? null,
            longitude: address?.lng ?? address?.longitude ?? null,
        };
    });
});

function clientPhoneHref(phone) {
    let digits = String(phone ?? "").replace(/\D/g, "");
    if (digits.length === 11 && (digits[0] === "8" || digits[0] === "7")) {
        digits = `7${digits.slice(1)}`;
    } else if (digits.length === 10) {
        digits = `7${digits}`;
    }
    return digits.length >= 11 ? `tel:+${digits}` : "";
}

async function load() {
    if (!sheetId.value) {
        sheet.value = null;
        toast.error("Некорректный лист");
        return;
    }
    try {
        sheet.value = await detailApi.run(() => getRouteSheet(sheetId.value));
    } catch (err) {
        sheet.value = null;
        toast.error(err instanceof Error ? err.message : "Не удалось открыть лист");
    }
}

function goBack() {
    router.push({ name: "courier-deliveries" });
}

/**
 * @param {number|string} orderId
 */
function askDelivered(orderId) {
    const stop = (Array.isArray(sheet.value?.stops) ? sheet.value.stops : [])
        .find((row) => Number(row?.order_id) === Number(orderId));
    const current = String(
        stop?.order?.payment_method
        ?? stop?.order?.payment?.method
        ?? "cash",
    );
    deliverOrderId.value = Number(orderId);
    deliverPaymentMethod.value = PAYMENT_OPTIONS.some((row) => row.value === current)
        ? current
        : "cash";
    deliverOpen.value = true;
}

async function confirmDelivered() {
    if (!sheetId.value || !deliverOrderId.value) {
        return;
    }
    if (!deliverPaymentMethod.value) {
        toast.error("Выбери способ оплаты");
        return;
    }
    const orderId = deliverOrderId.value;
    deliverOpen.value = false;
    try {
        sheet.value = await actionApi.run(() => settleStop(sheetId.value, orderId, {
            outcome: "delivered",
            payment_method: deliverPaymentMethod.value,
        }));
        toast.success("Доставлен");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось закрыть остановку");
    }
}

function askWriteOff(orderId) {
    writeOffOrderId.value = Number(orderId);
    writeOffReason.value = "";
    writeOffOpen.value = true;
}

async function confirmWriteOff() {
    if (!String(writeOffReason.value).trim()) {
        toast.error("Нужна причина списания");
        return;
    }
    if (!sheetId.value || !writeOffOrderId.value) {
        return;
    }
    const orderId = writeOffOrderId.value;
    writeOffOpen.value = false;
    try {
        sheet.value = await actionApi.run(() => settleStop(sheetId.value, orderId, {
            outcome: "written_off",
            reason: writeOffReason.value.trim(),
        }));
        toast.success("Списан");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось списать заказ");
    }
}

function syncFullscreenState() {
    const active = document.fullscreenElement === mapShell.value
        || document.webkitFullscreenElement === mapShell.value;
    mapFullscreen.value = Boolean(active);
    nextTick(() => {
        mapRef.value?.refreshViewport?.();
    });
}

async function toggleMapFullscreen() {
    const el = mapShell.value;
    if (!el) {
        return;
    }

    try {
        if (mapFullscreen.value) {
            if (document.exitFullscreen) {
                await document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
            return;
        }

        if (el.requestFullscreen) {
            await el.requestFullscreen();
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        } else {
            toast.error("Полноэкранный режим недоступен на этом устройстве");
        }
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось открыть карту на весь экран");
    }
}

async function loadBranchCity() {
    if (!branchId.value) {
        branchCity.value = "";
        return;
    }
    try {
        const branch = await getBranch(branchId.value);
        branchCity.value = String(branch?.city ?? "").trim();
    } catch {
        branchCity.value = "";
    }
}

watch(sheetId, () => {
    load();
});

onMounted(async () => {
    fullscreenSupported.value = Boolean(
        document.documentElement.requestFullscreen
        || document.documentElement.webkitRequestFullscreen,
    );
    document.addEventListener("fullscreenchange", syncFullscreenState);
    document.addEventListener("webkitfullscreenchange", syncFullscreenState);
    if (!auth.bootstrapped) {
        await auth.bootstrap();
    }
    await Promise.all([loadBranchCity(), load()]);
});

onBeforeUnmount(() => {
    document.removeEventListener("fullscreenchange", syncFullscreenState);
    document.removeEventListener("webkitfullscreenchange", syncFullscreenState);
    if (document.fullscreenElement || document.webkitFullscreenElement) {
        try {
            document.exitFullscreen?.();
            document.webkitExitFullscreen?.();
        } catch {
            /* noop */
        }
    }
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    @click="goBack"
                >
                    К листам
                </UiButton>
                <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                    Лист #{{ sheetId || "—" }}
                </h1>
                <UiBadge
                    v-if="sheet"
                    :variant="sheetStatusVariant(sheet.status)"
                    :text="sheetStatusLabel(sheet.status)"
                />
            </div>
        </div>

        <p
            v-if="detailApi.loading && !sheet"
            class="text-sm text-bo-muted"
        >
            Загрузка…
        </p>

        <template v-else-if="sheet">
            <div class="grid gap-4 lg:grid-cols-5">
                <div class="lg:col-span-2">
                    <div
                        ref="mapShell"
                        class="relative overflow-hidden border border-bo-border bg-bo-field"
                        :class="mapFullscreen ? 'h-full w-full' : 'h-[22rem]'"
                    >
                        <RouteOrdersMap
                            ref="mapRef"
                            :orders="mapOrders"
                            :selected-ids="[]"
                            :default-city="branchCity"
                        />
                        <div class="absolute bottom-3 right-3 z-20 flex gap-2">
                            <UiButton
                                v-if="fullscreenSupported"
                                class="!bg-bo-surface/95 !text-bo-fg shadow-bo"
                                @click="toggleMapFullscreen"
                            >
                                {{ mapFullscreen ? "Свернуть" : "На весь экран" }}
                            </UiButton>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 lg:col-span-3">
                    <h2 class="text-sm font-medium text-bo-muted">
                        Остановки
                    </h2>
                    <UiCard
                        v-for="stop in (sheet.stops ?? [])"
                        :key="`${stop.order_id}-${stop.position}`"
                        :title="`${stop.position}. ${stop.order?.number || `Заказ #${stop.order_id}`}`"
                    >
                        <template #actions>
                            <div class="flex flex-wrap items-center gap-2">
                                <OrderNotesTrigger
                                    :order-id="stop.order_id"
                                    :order-status="stop.order?.status || ''"
                                    :initial-notes="stop.order?.notes ?? []"
                                    :notes-count="Number(stop.order?.notes_count ?? 0)"
                                />
                                <UiBadge
                                    :variant="stopOutcomeVariant(stop.outcome)"
                                    :text="stopOutcomeLabel(stop.outcome)"
                                />
                            </div>
                        </template>

                        <div class="space-y-2 text-sm">
                            <p class="text-bo-fg">
                                {{ formatAddress(stop.order?.address) || "Адрес не указан" }}
                            </p>
                            <p class="text-bo-muted">
                                {{ stop.order?.client?.name || "—" }}
                                <template v-if="stop.order?.client?.phone">
                                    · {{ stop.order.client.phone }}
                                </template>
                            </p>
                            <p class="text-bo-muted">
                                Оплата: {{ paymentMethodLabel(stop.order?.payment_method ?? stop.order?.payment?.method) }}
                            </p>
                            <p
                                v-if="stop.reason"
                                class="text-bo-muted"
                            >
                                Причина: {{ stop.reason }}
                            </p>

                            <div class="flex flex-wrap gap-2 pt-2">
                                <a
                                    v-if="stop.order?.client?.phone && clientPhoneHref(stop.order.client.phone)"
                                    :href="clientPhoneHref(stop.order.client.phone)"
                                    class="inline-flex h-11 items-center border border-bo-border bg-white/10 px-4 text-sm font-medium text-bo-fg hover:bg-white/[0.08]"
                                >
                                    Позвонить
                                </a>
                                <template v-if="sheet.status === 'in_delivery' && stopIsOpen(stop.outcome)">
                                    <UiButton
                                        class="!bg-emerald-600"
                                        :disabled="actionApi.loading"
                                        @click="askDelivered(stop.order_id)"
                                    >
                                        Доставлен
                                    </UiButton>
                                    <UiButton
                                        class="!bg-white/10 !text-bo-fg"
                                        :disabled="actionApi.loading"
                                        @click="askWriteOff(stop.order_id)"
                                    >
                                        Списать
                                    </UiButton>
                                </template>
                            </div>
                        </div>
                    </UiCard>
                </div>
            </div>
        </template>

        <UiModal
            :open="deliverOpen"
            title="Способ оплаты"
            @close="deliverOpen = false"
        >
            <UiFormField
                label="Факт оплаты"
                required
            >
                <select
                    v-model="deliverPaymentMethod"
                    class="mt-1 h-11 w-full border border-bo-border bg-bo-field px-4 text-sm focus:border-bo-accent focus:outline-none"
                >
                    <option
                        v-for="option in PAYMENT_OPTIONS"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </UiFormField>
            <template #footer>
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    @click="deliverOpen = false"
                >
                    Закрыть
                </UiButton>
                <UiButton
                    class="!bg-emerald-600"
                    :disabled="actionApi.loading"
                    @click="confirmDelivered"
                >
                    Доставлен
                </UiButton>
            </template>
        </UiModal>

        <UiModal
            :open="writeOffOpen"
            title="Списать заказ"
            @close="writeOffOpen = false"
        >
            <UiFormField
                label="Причина"
                required
            >
                <textarea
                    v-model="writeOffReason"
                    rows="4"
                    class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-3 text-sm focus:border-bo-accent focus:outline-none"
                    placeholder="Почему заказ не доставлен"
                />
            </UiFormField>
            <template #footer>
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    @click="writeOffOpen = false"
                >
                    Закрыть
                </UiButton>
                <UiButton
                    :disabled="actionApi.loading"
                    @click="confirmWriteOff"
                >
                    Списать
                </UiButton>
            </template>
        </UiModal>
    </div>
</template>
