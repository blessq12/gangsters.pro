<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import {
    listCourierRouteSheets,
    settleStop,
    takeRouteSheet,
} from "../../../api/delivery.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";

const auth = useAuthStore();
const toast = useToast();
const listApi = useApi();
const actionApi = useApi();

const sheets = ref([]);
const expandedId = ref(null);

const problemOpen = ref(false);
const problemSheetId = ref(null);
const problemOrderId = ref(null);
const problemReason = ref("");

/** @type {ReturnType<typeof setInterval>|null} */
let pollTimer = null;
const POLL_MS = 18000;

const sheetStatusLabel = {
    released: "Новый",
    in_delivery: "В работе",
    completed: "Завершён",
};

const sheetStatusVariant = {
    released: "warning",
    in_delivery: "warning",
    completed: "success",
};

const outcomeLabel = {
    pending: "Ожидает",
    delivered: "Доставлен",
    written_off: "Списан",
};

const outcomeVariant = {
    pending: "neutral",
    delivered: "success",
    written_off: "danger",
};

function asList(payload) {
    return Array.isArray(payload) ? payload : (payload?.items ?? []);
}

function stopOutcome(stop) {
    return stop?.outcome && stop.outcome !== "" ? stop.outcome : "pending";
}

function isPending(stop) {
    return stopOutcome(stop) === "pending";
}

async function load(silent = false) {
    try {
        const payload = await listApi.run(() => listCourierRouteSheets());
        sheets.value = asList(payload);
    } catch (err) {
        if (!silent) {
            toast.error(err instanceof Error ? err.message : "Не удалось загрузить маршруты");
        }
    }
}

function toggleExpand(id) {
    expandedId.value = expandedId.value === id ? null : id;
}

async function onTake(sheetId, event) {
    event?.stopPropagation();
    const courierId = auth.employee?.id;
    if (!courierId) {
        toast.error("Нет id курьера");
        return;
    }
    try {
        await actionApi.run(() => takeRouteSheet(sheetId, { courier_id: courierId }));
        toast.success("Маршрут взят");
        await load(true);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

async function onDelivered(sheetId, orderId, event) {
    event?.stopPropagation();
    try {
        await actionApi.run(() => settleStop(sheetId, orderId, {
            outcome: "delivered",
        }));
        toast.success(`#${orderId} доставлен`);
        await load(true);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

function askProblem(sheetId, orderId, event) {
    event?.stopPropagation();
    problemSheetId.value = sheetId;
    problemOrderId.value = orderId;
    problemReason.value = "";
    problemOpen.value = true;
}

async function confirmProblem() {
    if (!String(problemReason.value).trim()) {
        toast.error("Укажи причину");
        return;
    }
    const sheetId = problemSheetId.value;
    const orderId = problemOrderId.value;
    problemOpen.value = false;
    try {
        await actionApi.run(() => settleStop(sheetId, orderId, {
            outcome: "written_off",
            reason: problemReason.value.trim(),
        }));
        toast.success(`#${orderId}: списан`);
        await load(true);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

onMounted(() => {
    load();
    pollTimer = setInterval(() => load(true), POLL_MS);
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
});
</script>

<template>
    <div class="space-y-6 px-4 py-6 sm:px-6">
        <h1 class="text-2xl font-bold text-bo-fg">
            Мои доставки
        </h1>

        <div
            v-if="!listApi.loading && sheets.length === 0"
            class="rounded-none border border-bo-border bg-bo-elevated px-6 py-16 text-center text-2xl font-semibold text-bo-muted"
        >
            Нет активных маршрутов
        </div>

        <div v-else class="space-y-4">
            <UiCard
                v-for="sheet in sheets"
                :key="sheet.id"
                class="!cursor-pointer"
                @click="toggleExpand(sheet.id)"
            >
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-3xl font-black text-bo-fg">
                                #{{ sheet.id }}
                            </p>
                            <p class="mt-1 text-sm text-bo-muted">
                                Стопов: {{ (sheet.stops ?? []).length }}
                            </p>
                        </div>
                        <UiBadge
                            class="!px-3 !py-1 !text-sm"
                            :variant="sheetStatusVariant[sheet.status] || 'neutral'"
                            :text="sheetStatusLabel[sheet.status] || String(sheet.status || '—')"
                        />
                    </div>

                    <div class="space-y-2" @click.stop>
                        <div
                            v-for="stop in (sheet.stops ?? [])"
                            :key="`${sheet.id}-${stop.order_id}-${stop.position}`"
                            class="border border-bo-border bg-white/[0.03] p-4"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="space-y-1">
                                    <p class="text-lg font-semibold text-bo-fg">
                                        Заказ #{{ stop.order_id }}
                                        <span class="text-sm font-normal text-bo-muted">
                                            · pos {{ stop.position }}
                                        </span>
                                    </p>
                                    <UiBadge
                                        :variant="outcomeVariant[stopOutcome(stop)] || 'neutral'"
                                        :text="outcomeLabel[stopOutcome(stop)] || stopOutcome(stop)"
                                    />
                                </div>

                                <div
                                    v-if="sheet.status === 'in_delivery' && isPending(stop)"
                                    class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row"
                                >
                                    <UiButton
                                        class="!h-14 !flex-1 !bg-emerald-600 !text-lg !font-bold sm:!min-w-[9rem]"
                                        :disabled="actionApi.loading"
                                        @click="onDelivered(sheet.id, stop.order_id, $event)"
                                    >
                                        Доставлен
                                    </UiButton>
                                    <UiButton
                                        class="!h-14 !flex-1 !bg-bo-accent !text-lg !font-bold sm:!min-w-[9rem]"
                                        :disabled="actionApi.loading"
                                        @click="askProblem(sheet.id, stop.order_id, $event)"
                                    >
                                        Проблема
                                    </UiButton>
                                </div>
                            </div>

                            <div
                                v-if="expandedId === sheet.id && stop.order"
                                class="mt-2 text-sm text-bo-muted"
                            >
                                <p>{{ stop.order.client?.name || "—" }} · {{ stop.order.client?.phone || "" }}</p>
                                <p v-if="stop.order.address">
                                    {{ typeof stop.order.address === 'string'
                                        ? stop.order.address
                                        : (stop.order.address.line || stop.order.address.search_line || '—') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="sheet.status === 'released'" @click.stop>
                        <UiButton
                            class="!h-14 !w-full !bg-bo-accent !text-xl !font-bold"
                            :disabled="actionApi.loading"
                            @click="onTake(sheet.id, $event)"
                        >
                            Взять маршрут
                        </UiButton>
                    </div>
                </div>
            </UiCard>
        </div>

        <UiModal
            :open="problemOpen"
            title="Причина проблемы"
            @close="problemOpen = false"
        >
            <UiFormField label="Причина" required>
                <textarea
                    v-model="problemReason"
                    rows="4"
                    class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 transition-colors hover:border-bo-border-strong focus:border-bo-accent focus:outline-none text-base"
                    placeholder="Что случилось?"
                />
            </UiFormField>
            <template #footer>
                <UiButton @click="problemOpen = false">Отмена</UiButton>
                <UiButton
                    class="!bg-bo-accent"
                    :disabled="actionApi.loading"
                    @click="confirmProblem"
                >
                    Подтвердить
                </UiButton>
            </template>
        </UiModal>
    </div>
</template>
