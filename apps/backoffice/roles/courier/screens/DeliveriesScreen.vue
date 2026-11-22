<script setup>
import { onMounted, onUnmounted, ref, watch } from "vue";
import {
    listCourierRouteSheets,
    settleStop,
    takeRouteSheet,
} from "../../../api/delivery.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiFormField from "../../../shell/components/ui/UiFormField.vue";
import UiModal from "../../../shell/components/ui/UiModal.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import {
    formatAddress,
    sheetHint,
    sheetStatusLabel,
    sheetStatusVariant,
    stopIsOpen,
    stopOutcomeLabel,
    stopOutcomeVariant,
} from "../../../shell/domain/orderStage.js";

const auth = useAuthStore();
const toast = useToast();
const listApi = useApi();
const actionApi = useApi();

const {
    page,
    perPage,
    total,
    items: sheets,
    offset,
    applyPayload,
} = usePagedList();
const writeOffOpen = ref(false);
const writeOffSheetId = ref(null);
const writeOffOrderId = ref(null);
const writeOffReason = ref("");

/** @type {ReturnType<typeof setInterval>|null} */
let pollTimer = null;
const POLL_MS = 18000;

async function load(silent = false) {
    try {
        const payload = await listApi.run(() => listCourierRouteSheets({
            offset: offset.value,
            limit: perPage,
        }));
        applyPayload(payload);
        const lastPage = Math.max(1, Math.ceil(total.value / perPage));
        if (page.value > lastPage) {
            page.value = lastPage;
        }
    } catch (err) {
        if (!silent) {
            toast.error(err instanceof Error ? err.message : "Не удалось загрузить маршрутные листы");
        }
    }
}

async function onTake(sheetId) {
    const courierId = auth.employee?.id;
    if (!courierId) {
        toast.error("Нет курьера в сессии. Войди заново.");
        return;
    }
    try {
        await actionApi.run(() => takeRouteSheet(sheetId, { courier_id: courierId }));
        toast.success("Лист взят. Заказы в пути.");
        await load(true);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось взять лист");
    }
}

async function onDelivered(sheetId, orderId) {
    try {
        await actionApi.run(() => settleStop(sheetId, orderId, { outcome: "delivered" }));
        toast.success(`Остановка заказа #${orderId}: доставлен.`);
        await load(true);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось закрыть остановку");
    }
}

function askWriteOff(sheetId, orderId) {
    writeOffSheetId.value = sheetId;
    writeOffOrderId.value = orderId;
    writeOffReason.value = "";
    writeOffOpen.value = true;
}

async function confirmWriteOff() {
    if (!String(writeOffReason.value).trim()) {
        toast.error("Нужна причина списания");
        return;
    }
    const sheetId = writeOffSheetId.value;
    const orderId = writeOffOrderId.value;
    writeOffOpen.value = false;
    try {
        await actionApi.run(() => settleStop(sheetId, orderId, {
            outcome: "written_off",
            reason: writeOffReason.value.trim(),
        }));
        toast.success(`Остановка заказа #${orderId}: списан.`);
        await load(true);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось списать заказ");
    }
}

watch(page, () => load());
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
            Маршрутные листы
        </h1>

        <div
            v-if="!listApi.loading && sheets.length === 0"
            class="border border-bo-border bg-bo-elevated px-6 py-16 text-center text-2xl font-semibold text-bo-muted"
        >
            Свободных и своих листов нет
        </div>

        <div v-else class="space-y-4">
            <UiCard
                v-for="sheet in sheets"
                :key="sheet.id"
            >
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-3xl font-black text-bo-fg">
                                Лист #{{ sheet.id }}
                            </p>
                            <p class="mt-2 text-sm text-bo-muted">
                                {{ sheetHint(sheet) }}
                            </p>
                        </div>
                        <UiBadge
                            class="!px-3 !py-1 !text-sm"
                            :variant="sheetStatusVariant(sheet.status)"
                            :text="sheetStatusLabel(sheet.status)"
                        />
                    </div>

                    <article
                        v-for="stop in (sheet.stops ?? [])"
                        :key="`${sheet.id}-${stop.order_id}-${stop.position}`"
                        class="border border-bo-border bg-white/[0.03] p-4"
                    >
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="space-y-1">
                                <p class="text-lg font-semibold text-bo-fg">
                                    Остановка {{ stop.position }} · {{ stop.order?.number || `заказ #${stop.order_id}` }}
                                </p>
                                <p class="text-sm text-bo-muted">
                                    {{ stop.order?.client?.name || "—" }}
                                    <span v-if="stop.order?.client?.phone"> · {{ stop.order.client.phone }}</span>
                                </p>
                                <p class="text-base text-bo-fg">
                                    {{ formatAddress(stop.order?.address) || "Адрес не указан" }}
                                </p>
                            </div>
                            <UiBadge
                                :variant="stopOutcomeVariant(stop.outcome)"
                                :text="stopOutcomeLabel(stop.outcome)"
                            />
                        </div>

                        <div
                            v-if="sheet.status === 'in_delivery' && stopIsOpen(stop.outcome)"
                            class="mt-3 flex w-full flex-col gap-2 sm:flex-row"
                        >
                            <UiButton
                                class="!h-14 !flex-1 !bg-emerald-600 !text-lg !font-bold sm:!min-w-[9rem]"
                                :disabled="actionApi.loading"
                                @click="onDelivered(sheet.id, stop.order_id)"
                            >
                                Доставлен
                            </UiButton>
                            <UiButton
                                class="!h-14 !flex-1 !text-lg !font-bold sm:!min-w-[9rem]"
                                :disabled="actionApi.loading"
                                @click="askWriteOff(sheet.id, stop.order_id)"
                            >
                                Списать
                            </UiButton>
                        </div>
                    </article>

                    <UiButton
                        v-if="sheet.status === 'released' && !sheet.courier_id"
                        class="!h-14 !w-full !text-xl !font-bold"
                        :disabled="actionApi.loading"
                        @click="onTake(sheet.id)"
                    >
                        Взять лист
                    </UiButton>
                </div>
            </UiCard>
        </div>

        <UiPagination
            :page="page"
            :per-page="perPage"
            :total="total"
            @update:page="page = $event"
        />

        <UiModal :open="writeOffOpen" title="Списать заказ" @close="writeOffOpen = false">
            <UiFormField label="Причина" required>
                <textarea
                    v-model="writeOffReason"
                    rows="4"
                    class="mt-1 w-full border border-bo-border bg-bo-field px-4 py-2.5 text-base focus:border-bo-accent focus:outline-none"
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
