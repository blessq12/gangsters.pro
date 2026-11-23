<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    getRouteSheet,
    settleStop,
} from "../../../api/delivery.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import RouteOrdersMap from "../../../shell/components/order/RouteOrdersMap.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import {
    formatAddress,
    sheetStatusLabel,
    sheetStatusVariant,
    stopIsOpen,
    stopOutcomeLabel,
    stopOutcomeVariant,
} from "../../../shell/domain/orderStage.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const detailApi = useApi();
const actionApi = useApi();

const sheet = ref(/** @type {Record<string, unknown>|null} */ (null));
const writeOffOpen = ref(false);
const writeOffOrderId = ref(/** @type {number|null} */ (null));
const writeOffReason = ref("");
const mapShell = ref(/** @type {HTMLElement|null} */ (null));
const mapRef = ref(/** @type {{ refreshViewport?: () => void }|null} */ (null));
const mapFullscreen = ref(false);
const fullscreenSupported = ref(false);

const sheetId = computed(() => {
    const id = Number(route.params.id);
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
            city: address?.city ? String(address.city) : "",
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

async function onDelivered(orderId) {
    if (!sheetId.value) {
        return;
    }
    try {
        sheet.value = await actionApi.run(() => settleStop(sheetId.value, orderId, {
            outcome: "delivered",
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

watch(sheetId, () => {
    load();
});

onMounted(() => {
    fullscreenSupported.value = Boolean(
        document.documentElement.requestFullscreen
        || document.documentElement.webkitRequestFullscreen,
    );
    document.addEventListener("fullscreenchange", syncFullscreenState);
    document.addEventListener("webkitfullscreenchange", syncFullscreenState);
    load();
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
    <div class="mx-auto w-full max-w-lg space-y-4 px-4 py-4">
        <div class="sticky top-0 z-10 -mx-4 border-b border-bo-border bg-bo-canvas/95 px-4 py-3 backdrop-blur">
            <button
                type="button"
                class="inline-flex h-11 items-center text-base text-bo-muted active:text-bo-fg"
                @click="goBack"
            >
                ← К листам
            </button>
            <div class="mt-1 flex items-center justify-between gap-3">
                <h1 class="text-xl font-bold text-bo-fg">
                    Лист #{{ sheetId || "—" }}
                </h1>
                <UiBadge
                    v-if="sheet"
                    class="!px-3 !py-1 !text-sm"
                    :variant="sheetStatusVariant(sheet.status)"
                    :text="sheetStatusLabel(sheet.status)"
                />
            </div>
        </div>

        <p
            v-if="detailApi.loading && !sheet"
            class="text-bo-muted"
        >
            Загрузка…
        </p>

        <template v-else-if="sheet">
            <div
                ref="mapShell"
                class="relative overflow-hidden border border-bo-border bg-bo-field"
                :class="mapFullscreen ? 'h-full w-full' : 'h-[42vh] min-h-[14rem]'"
            >
                <RouteOrdersMap
                    ref="mapRef"
                    :orders="mapOrders"
                    :selected-ids="[]"
                />
                <div class="absolute bottom-3 right-3 z-20 flex gap-2">
                    <UiButton
                        v-if="fullscreenSupported"
                        class="!h-12 !bg-bo-surface/95 !px-4 !text-bo-fg shadow-bo"
                        @click="toggleMapFullscreen"
                    >
                        {{ mapFullscreen ? "Свернуть" : "На весь экран" }}
                    </UiButton>
                </div>
            </div>

            <div class="space-y-3">
                <h2 class="text-lg font-semibold text-bo-fg">
                    Остановки
                </h2>
                <article
                    v-for="stop in (sheet.stops ?? [])"
                    :key="`${stop.order_id}-${stop.position}`"
                    class="space-y-3 border border-bo-border bg-bo-elevated p-4"
                >
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-lg font-semibold text-bo-fg">
                            {{ stop.position }}. {{ stop.order?.number || `Заказ #${stop.order_id}` }}
                        </p>
                        <UiBadge
                            :variant="stopOutcomeVariant(stop.outcome)"
                            :text="stopOutcomeLabel(stop.outcome)"
                        />
                    </div>

                    <p class="text-base leading-snug text-bo-fg">
                        {{ formatAddress(stop.order?.address) || "Адрес не указан" }}
                    </p>

                    <p class="text-sm text-bo-muted">
                        {{ stop.order?.client?.name || "—" }}
                    </p>

                    <a
                        v-if="stop.order?.client?.phone && clientPhoneHref(stop.order.client.phone)"
                        :href="clientPhoneHref(stop.order.client.phone)"
                        class="inline-flex h-12 w-full items-center justify-center border border-bo-border bg-white/[0.04] text-base font-medium text-bo-fg active:bg-white/[0.08]"
                    >
                        Позвонить · {{ stop.order.client.phone }}
                    </a>

                    <p
                        v-if="stop.reason"
                        class="text-sm text-bo-muted"
                    >
                        Причина: {{ stop.reason }}
                    </p>

                    <div
                        v-if="sheet.status === 'in_delivery' && stopIsOpen(stop.outcome)"
                        class="flex flex-col gap-2"
                    >
                        <UiButton
                            class="!h-14 !w-full !bg-emerald-600 !text-lg !font-bold"
                            :disabled="actionApi.loading"
                            @click="onDelivered(stop.order_id)"
                        >
                            Доставлен
                        </UiButton>
                        <UiButton
                            class="!h-14 !w-full !bg-white/10 !text-lg !font-bold !text-bo-fg"
                            :disabled="actionApi.loading"
                            @click="askWriteOff(stop.order_id)"
                        >
                            Списать
                        </UiButton>
                    </div>
                </article>
            </div>
        </template>

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
                    class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-3 text-base focus:border-bo-accent focus:outline-none"
                    placeholder="Почему заказ не доставлен"
                />
            </UiFormField>
            <template #footer>
                <UiButton
                    class="!h-12 !bg-white/10 !text-bo-fg"
                    @click="writeOffOpen = false"
                >
                    Закрыть
                </UiButton>
                <UiButton
                    class="!h-12"
                    :disabled="actionApi.loading"
                    @click="confirmWriteOff"
                >
                    Списать
                </UiButton>
            </template>
        </UiModal>
    </div>
</template>
