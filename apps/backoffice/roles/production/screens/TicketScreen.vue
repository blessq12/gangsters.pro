<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { completeTicket, getTicket, startTicket } from "../../../api/production.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import {
    orderStatusLabel,
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
const comment = computed(() => ticket.value?.order?.comment ?? null);

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

async function onStart() {
    try {
        ticket.value = await actionApi.run(() => startTicket(orderId.value));
        toast.success("Тикет в работе. Заказ готовится.");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось начать тикет");
    }
}

async function onComplete() {
    try {
        await actionApi.run(() => completeTicket(orderId.value));
        toast.success("Тикет завершён. Заказ готов.");
        goQueue();
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось завершить тикет");
    }
}

watch(orderId, load);
onMounted(load);
</script>

<template>
    <div class="space-y-6">
        <UiButton
            class="!h-14 !bg-white/10 !px-6 !text-xl !text-white"
            @click="goQueue"
        >
            К тикетам
        </UiButton>

        <div v-if="detailApi.loading" class="text-3xl text-bo-muted">
            Загрузка…
        </div>

        <UiCard
            v-else-if="ticket"
            class="!border-bo-border !bg-bo-elevated !text-bo-fg !shadow-none"
        >
            <div class="space-y-8">
                <div class="flex flex-wrap items-center gap-4">
                    <p class="text-3xl font-black tracking-tight md:text-4xl">
                        {{ ticket.order_number || ticket.order?.number || `#${ticket.order_id}` }}
                    </p>
                    <UiBadge
                        class="!px-4 !py-1.5 !text-xl"
                        :variant="ticketStatusVariant(ticket.status)"
                        :text="ticketStatusLabel(ticket.status)"
                    />
                </div>

                <p class="text-2xl text-bo-muted">
                    {{ ticketHint(ticket.status) }}
                </p>

                <div class="space-y-3">
                    <p class="text-2xl font-semibold text-bo-muted">Состав</p>
                    <ul class="space-y-3">
                        <li
                            v-for="(line, idx) in lines"
                            :key="idx"
                            class="border border-bo-border bg-black/40 px-4 py-3 text-3xl font-semibold"
                        >
                            {{ line.name || `Позиция #${line.product_id}` }}
                            <span class="text-amber-300">× {{ line.quantity }}</span>
                        </li>
                    </ul>
                    <p v-if="lines.length === 0" class="text-2xl text-bo-muted">
                        В тикете нет позиций
                    </p>
                </div>

                <div v-if="comment" class="border border-amber-700/40 bg-amber-950/30 px-4 py-3">
                    <p class="mb-1 text-lg text-amber-200">Комментарий к заказу</p>
                    <p class="text-2xl text-amber-50">{{ comment }}</p>
                </div>

                <p v-if="ticket.order?.status" class="text-xl text-bo-muted">
                    Заказ: {{ orderStatusLabel(ticket.order.status) }}
                </p>

                <div class="flex flex-wrap gap-4">
                    <UiButton
                        v-if="ticket.status === 'queued'"
                        class="!h-16 !min-w-[12rem] !bg-amber-400 !px-8 !text-2xl !font-bold !text-slate-900"
                        :disabled="actionApi.loading"
                        @click="onStart"
                    >
                        Старт
                    </UiButton>
                    <UiButton
                        v-if="ticket.status === 'started'"
                        class="!h-16 !min-w-[12rem] !bg-emerald-500 !px-8 !text-2xl !font-bold !text-white"
                        :disabled="actionApi.loading"
                        @click="onComplete"
                    >
                        Завершить
                    </UiButton>
                </div>
            </div>
        </UiCard>
    </div>
</template>
