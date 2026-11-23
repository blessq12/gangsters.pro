<script setup>
import { computed, onMounted, onUnmounted, watch } from "vue";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { completeTicket, getQueue, startTicket } from "../../../api/production.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { usePagedList } from "../../../shell/composables/usePagedList.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiPagination from "../../../shell/components/ui/UiPagination.vue";
import { ticketStatusLabel, ticketStatusVariant } from "../../../shell/domain/orderStage.js";

const router = useRouter();
const toast = useToast();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);
const listApi = useApi();
const actionApi = useApi();

const branchId = computed(() => {
    const raw = employee.value?.branch_id;
    const id = Number(raw);
    return Number.isFinite(id) && id > 0 ? id : null;
});

const {
    page,
    perPage,
    total,
    items: tickets,
    offset,
    applyPayload,
    clearItems,
} = usePagedList();
/** @type {ReturnType<typeof setInterval>|null} */
let pollTimer = null;
const POLL_MS = 12000;

async function load(silent = false) {
    if (!branchId.value) {
        clearItems();
        return;
    }
    try {
        const payload = await listApi.run(() => getQueue(branchId.value, {
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
            toast.error(err instanceof Error ? err.message : "Не удалось загрузить тикеты");
        }
    }
}

function openTicket(orderId) {
    router.push({ name: "production-ticket", params: { orderId: String(orderId) } });
}

async function onStart(orderId, event) {
    event.stopPropagation();
    try {
        await actionApi.run(() => startTicket(orderId));
        toast.success("В работе");
        await load(true);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось начать тикет");
    }
}

async function onComplete(orderId, event) {
    event.stopPropagation();
    try {
        await actionApi.run(() => completeTicket(orderId));
        toast.success("Готово");
        await load(true);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось завершить тикет");
    }
}

function restartPoll() {
    if (pollTimer) {
        clearInterval(pollTimer);
    }
    pollTimer = setInterval(() => load(true), POLL_MS);
}

watch(page, () => {
    load();
});

onMounted(async () => {
    if (!auth.bootstrapped) {
        await auth.bootstrap();
    }
    await load();
    restartPoll();
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
});
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-4xl font-bold tracking-tight md:text-5xl">
            Тикеты
        </h1>

        <p
            v-if="!branchId"
            class="text-2xl text-bo-muted"
        >
            У сотрудника не указан филиал смены — очередь недоступна.
        </p>

        <div
            v-else-if="!listApi.loading && tickets.length === 0"
            class="border border-bo-border bg-bo-elevated px-8 py-16 text-center text-4xl font-semibold text-bo-muted md:text-5xl"
        >
            Тикетов нет
        </div>

        <div
            v-else-if="branchId"
            class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
        >
            <UiCard
                v-for="ticket in tickets"
                :key="ticket.order_id"
                class="!cursor-pointer !border-bo-border !bg-bo-elevated !text-bo-fg !shadow-none transition hover:!border-bo-accent"
                @click="openTicket(ticket.order_id)"
            >
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-3xl font-black leading-none tracking-tight md:text-4xl">
                            {{ ticket.order_number || ticket.order?.number || `#${ticket.order_id}` }}
                        </p>
                        <UiBadge
                            class="!px-3 !py-1 !text-base"
                            :variant="ticketStatusVariant(ticket.status)"
                            :text="ticketStatusLabel(ticket.status)"
                        />
                    </div>
                    <ul class="space-y-1 text-xl">
                        <li
                            v-for="(line, idx) in (ticket.lines ?? []).slice(0, 4)"
                            :key="idx"
                        >
                            {{ line.name || `Позиция #${line.product_id}` }} × {{ line.quantity }}
                        </li>
                    </ul>
                    <div
                        class="flex flex-wrap gap-3"
                        @click.stop
                    >
                        <UiButton
                            v-if="ticket.status === 'queued'"
                            class="!h-16 !min-w-[10rem] !bg-amber-400 !px-8 !text-2xl !font-bold !text-slate-900"
                            :disabled="actionApi.loading"
                            @click="onStart(ticket.order_id, $event)"
                        >
                            Старт
                        </UiButton>
                        <UiButton
                            v-if="ticket.status === 'started'"
                            class="!h-16 !min-w-[10rem] !bg-emerald-500 !px-8 !text-2xl !font-bold !text-white"
                            :disabled="actionApi.loading"
                            @click="onComplete(ticket.order_id, $event)"
                        >
                            Завершить
                        </UiButton>
                    </div>
                </div>
            </UiCard>
        </div>

        <UiPagination
            v-if="branchId"
            :page="page"
            :per-page="perPage"
            :total="total"
            @update:page="page = $event"
        />
    </div>
</template>
