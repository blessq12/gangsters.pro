<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import { useRouter } from "vue-router";
import {
    completeTicket,
    getQueue,
    startTicket,
} from "../../../api/production.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";

const router = useRouter();
const toast = useToast();
const listApi = useApi();
const actionApi = useApi();

const tickets = ref([]);
/** @type {ReturnType<typeof setInterval>|null} */
let pollTimer = null;
const POLL_MS = 12000;

function asList(payload) {
    return Array.isArray(payload) ? payload : (payload?.items ?? []);
}

function formatDate(value) {
    if (!value) {
        return "";
    }
    try {
        return new Date(value).toLocaleTimeString("ru-RU", {
            hour: "2-digit",
            minute: "2-digit",
        });
    } catch {
        return String(value);
    }
}

function statusVariant(status) {
    if (status === "queued") {
        return "warning";
    }
    if (status === "started") {
        return "neutral";
    }
    if (status === "completed") {
        return "success";
    }
    if (status === "cancelled") {
        return "danger";
    }
    return "neutral";
}

function statusClass(status) {
    if (status === "started") {
        return "!bg-sky-500 !text-white";
    }
    if (status === "queued") {
        return "!bg-amber-400 !text-slate-900";
    }
    return "";
}

async function load(silent = false) {
    try {
        const payload = await listApi.run(() => getQueue());
        tickets.value = asList(payload);
    } catch (err) {
        if (!silent) {
            toast.error(err instanceof Error ? err.message : "Не удалось загрузить очередь");
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
        toast.success(`#${orderId} взяли`);
        await load(true);
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

async function onComplete(orderId, event) {
    event.stopPropagation();
    try {
        await actionApi.run(() => completeTicket(orderId));
        toast.success(`#${orderId} готово`);
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
    <div class="space-y-6">
        <h1 class="text-4xl font-bold tracking-tight md:text-5xl">
            Очередь кухни
        </h1>

        <div
            v-if="!listApi.loading && tickets.length === 0"
            class="rounded-2xl border border-slate-700 bg-slate-900/60 px-8 py-16 text-center text-4xl font-semibold text-slate-300 md:text-5xl"
        >
            Очередь пуста
        </div>

        <div
            v-else
            class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
        >
            <UiCard
                v-for="ticket in tickets"
                :key="ticket.order_id"
                class="!cursor-pointer !border-slate-700 !bg-slate-900 !text-slate-50 !shadow-none transition hover:!border-slate-500"
                @click="openTicket(ticket.order_id)"
            >
                <div class="space-y-5">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-5xl font-black leading-none md:text-6xl">
                            #{{ ticket.order_id }}
                        </p>
                        <UiBadge
                            class="!px-3 !py-1 !text-base"
                            :class="statusClass(ticket.status)"
                            :variant="statusVariant(ticket.status)"
                            :text="String(ticket.status || '—')"
                        />
                    </div>

                    <p
                        v-if="ticket.status === 'started' && ticket.started_at"
                        class="text-xl text-slate-300"
                    >
                        Старт {{ formatDate(ticket.started_at) }}
                    </p>

                    <div class="flex flex-wrap gap-3" @click.stop>
                        <UiButton
                            v-if="ticket.status === 'queued'"
                            class="!h-16 !min-w-[10rem] !bg-amber-400 !px-8 !text-2xl !font-bold !text-slate-900"
                            :disabled="actionApi.loading"
                            @click="onStart(ticket.order_id, $event)"
                        >
                            Взять
                        </UiButton>
                        <UiButton
                            v-if="ticket.status === 'started'"
                            class="!h-16 !min-w-[10rem] !bg-emerald-500 !px-8 !text-2xl !font-bold !text-white"
                            :disabled="actionApi.loading"
                            @click="onComplete(ticket.order_id, $event)"
                        >
                            Готово
                        </UiButton>
                    </div>
                </div>
            </UiCard>
        </div>
    </div>
</template>
