<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
    cancelTicket,
    completeTicket,
    getTicket,
    startTicket,
} from "../../../api/production.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const detailApi = useApi();
const actionApi = useApi();

const ticket = ref(null);
const cancelOpen = ref(false);

const orderId = computed(() => route.params.orderId);
const lines = computed(() => ticket.value?.order?.lines ?? []);
const comment = computed(() => ticket.value?.order?.comment ?? null);

function statusVariant(status) {
    if (status === "queued") {
        return "warning";
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

function formatDate(value) {
    if (!value) {
        return "—";
    }
    try {
        return new Date(value).toLocaleString("ru-RU");
    } catch {
        return String(value);
    }
}

async function load() {
    if (!orderId.value) {
        return;
    }
    try {
        ticket.value = await detailApi.run(() => getTicket(orderId.value));
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить тикет");
        ticket.value = null;
    }
}

function goQueue() {
    router.push({ name: "production-queue" });
}

async function onStart() {
    try {
        ticket.value = await actionApi.run(() => startTicket(orderId.value));
        toast.success("Взяли в работу");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

async function onComplete() {
    try {
        await actionApi.run(() => completeTicket(orderId.value));
        toast.success("Готово");
        goQueue();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

function askCancel() {
    cancelOpen.value = true;
}

async function confirmCancel() {
    cancelOpen.value = false;
    try {
        await actionApi.run(() => cancelTicket(orderId.value));
        toast.success("Отменён");
        goQueue();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Ошибка");
    }
}

watch(orderId, load);
onMounted(load);
</script>

<template>
    <div class="space-y-6">
        <UiButton
            class="!h-14 !bg-slate-700 !px-6 !text-xl !text-white"
            @click="goQueue"
        >
            ← Назад к очереди
        </UiButton>

        <div v-if="detailApi.loading" class="text-3xl text-slate-400">
            Загрузка…
        </div>

        <UiCard
            v-else-if="ticket"
            class="!border-slate-700 !bg-slate-900 !text-slate-50 !shadow-none"
        >
            <div class="space-y-8">
                <div class="flex flex-wrap items-center gap-4">
                    <p class="text-5xl font-black md:text-6xl">
                        #{{ ticket.order_id }}
                    </p>
                    <UiBadge
                        class="!px-4 !py-1.5 !text-xl"
                        :class="statusClass(ticket.status)"
                        :variant="statusVariant(ticket.status)"
                        :text="String(ticket.status || '—')"
                    />
                </div>

                <div class="space-y-3">
                    <p class="text-2xl font-semibold text-slate-300">Состав</p>
                    <ul class="space-y-3">
                        <li
                            v-for="(line, idx) in lines"
                            :key="idx"
                            class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-3 text-3xl font-semibold"
                        >
                            {{ line.product_name || line.product_id }}
                            <span class="text-amber-300">× {{ line.quantity }}</span>
                        </li>
                    </ul>
                    <p v-if="lines.length === 0" class="text-2xl text-slate-500">
                        Нет позиций
                    </p>
                </div>

                <div v-if="comment" class="rounded-xl border border-amber-700/40 bg-amber-950/30 px-4 py-3">
                    <p class="mb-1 text-lg text-amber-200">Комментарий</p>
                    <p class="text-2xl text-amber-50">{{ comment }}</p>
                </div>

                <div class="space-y-1 text-xl text-slate-400">
                    <p>Заказ: {{ ticket.order?.status ?? "—" }}</p>
                    <p>Старт: {{ formatDate(ticket.started_at) }}</p>
                    <p v-if="ticket.completed_at">
                        Готово: {{ formatDate(ticket.completed_at) }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-4">
                    <UiButton
                        v-if="ticket.status === 'queued'"
                        class="!h-16 !min-w-[12rem] !bg-amber-400 !px-8 !text-2xl !font-bold !text-slate-900"
                        :disabled="actionApi.loading"
                        @click="onStart"
                    >
                        Взять
                    </UiButton>
                    <UiButton
                        v-if="ticket.status === 'started'"
                        class="!h-16 !min-w-[12rem] !bg-emerald-500 !px-8 !text-2xl !font-bold !text-white"
                        :disabled="actionApi.loading"
                        @click="onComplete"
                    >
                        Готово
                    </UiButton>
                    <UiButton
                        v-if="ticket.status === 'queued' || ticket.status === 'started'"
                        class="!h-16 !min-w-[12rem] !bg-rose-600 !px-8 !text-2xl !font-bold !text-white"
                        :disabled="actionApi.loading"
                        @click="askCancel"
                    >
                        Отменить
                    </UiButton>
                </div>
            </div>
        </UiCard>

        <UiConfirmDialog
            :open="cancelOpen"
            title="Отменить тикет?"
            :message="`Заказ #${orderId} будет отменён на кухне.`"
            @confirm="confirmCancel"
            @cancel="cancelOpen = false"
        />
    </div>
</template>
