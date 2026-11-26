<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { getTicket, markTicketLineDone } from "../../../api/production.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { OrderNotesTrigger } from "../../../shell/modules/orderNotes/index.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import {
    ticketHint,
    ticketStatusLabel,
    ticketStatusVariant,
} from "../../../shell/domain/orderStage.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const detailApi = useApi();
const actionApi = useApi();

const ticket = ref(null);

const orderId = computed(() => route.params.orderId);
const lines = computed(() => ticket.value?.lines ?? []);
const canWorkLines = computed(() => {
    const status = ticket.value?.status;
    return status === "queued" || status === "started";
});
const orderNumber = computed(
    () => ticket.value?.order_number || ticket.value?.order?.number || `#${ticket.value?.order_id}`,
);

async function load() {
    if (!orderId.value) {
        return;
    }
    try {
        ticket.value = await detailApi.run(() => getTicket(orderId.value));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось открыть тикет");
        ticket.value = null;
    }
}

function goQueue() {
    router.push({ name: "production-queue" });
}

/**
 * @param {{ id?: number|string, status?: string }} line
 */
function canMarkLineDone(line) {
    return canWorkLines.value && line?.id && line.status === "queued";
}

/**
 * @param {{ id?: number|string, status?: string }} line
 */
async function onLineDone(line) {
    if (!canMarkLineDone(line)) {
        return;
    }
    try {
        ticket.value = await actionApi.run(() => markTicketLineDone(orderId.value, line.id));
        toast.success("Позиция готова");
        if (ticket.value?.status === "completed") {
            goQueue();
        }
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось отметить позицию");
    }
}

watch(orderId, load);
onMounted(load);
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <UiButton @click="goQueue">К очереди</UiButton>
            <OrderNotesTrigger
                v-if="ticket?.order_id"
                :order-id="ticket.order_id"
                :order-status="ticket.order?.status || ''"
                :initial-notes="ticket.order?.notes ?? []"
                :notes-count="Number(ticket.order?.notes_count ?? 0)"
            />
        </div>

        <p v-if="detailApi.loading" class="text-sm text-bo-muted">Загрузка…</p>

        <UiCard v-else-if="ticket" :title="String(orderNumber)">
            <template #actions>
                <UiBadge
                    :variant="ticketStatusVariant(ticket.status)"
                    :text="ticketStatusLabel(ticket.status)"
                />
            </template>

            <p class="mb-4 text-sm text-bo-muted">
                {{ ticketHint(ticket.status) }}
            </p>

            <ul class="space-y-2 text-sm">
                <li
                    v-for="line in lines"
                    :key="line.id ?? `${line.product_id}-${line.name}`"
                    class="flex flex-wrap items-center justify-between gap-3 border border-bo-border bg-black/20 px-3 py-2"
                >
                    <div>
                        <span class="text-bo-fg">
                            {{ line.name || `Позиция #${line.product_id}` }}
                        </span>
                        <span class="ml-2 text-bo-muted">× {{ line.quantity }}</span>
                        <span v-if="line.status === 'done'" class="ml-2 text-emerald-300">
                            готово
                            <template v-if="line.done_by">· #{{ line.done_by }}</template>
                        </span>
                        <span v-else-if="line.status === 'cancelled'" class="ml-2 text-bo-muted">
                            отменена
                        </span>
                    </div>
                    <UiButton
                        v-if="canMarkLineDone(line)"
                        :disabled="actionApi.loading"
                        @click="onLineDone(line)"
                    >
                        Готово
                    </UiButton>
                </li>
            </ul>
            <p v-if="lines.length === 0" class="text-sm text-bo-muted">Нет позиций</p>

            <p v-if="canWorkLines" class="mt-6 text-sm text-bo-muted">
                Отмечай позиции по готовности — любой повар филиала. Когда все готовы, заказ станет
                «Готов».
            </p>
        </UiCard>
    </div>
</template>
