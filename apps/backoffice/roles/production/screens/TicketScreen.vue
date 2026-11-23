<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { completeTicket, getTicket, startTicket } from "../../../api/production.js";
import { useApi } from "../../../shell/composables/useApi.js";
import { useToast } from "../../../shell/composables/useToast.js";
import { useAuthStore } from "../../../shell/store/authStore.js";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiCard from "../../../shell/components/ui/UiCard.vue";
import {
    ticketStatusLabel,
    ticketStatusVariant,
} from "../../../shell/domain/orderStage.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const auth = useAuthStore();
const { employee } = storeToRefs(auth);
const detailApi = useApi();
const actionApi = useApi();

const ticket = ref(null);

const orderId = computed(() => route.params.orderId);
const lines = computed(() => ticket.value?.lines ?? []);
const myEmployeeId = computed(() => {
    const id = Number(employee.value?.id);
    return Number.isFinite(id) && id > 0 ? id : null;
});
const isMine = computed(() => {
    if (!ticket.value || myEmployeeId.value === null) {
        return false;
    }
    return Number(ticket.value.assignee_employee_id) === myEmployeeId.value;
});
const canStart = computed(() => ticket.value?.status === "queued");
const canComplete = computed(() => ticket.value?.status === "started" && isMine.value);

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
        toast.success("В работе");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось начать тикет");
    }
}

async function onComplete() {
    try {
        await actionApi.run(() => completeTicket(orderId.value));
        toast.success("Готово");
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
            К очереди
        </UiButton>

        <div
            v-if="detailApi.loading"
            class="text-3xl text-bo-muted"
        >
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
                <p
                    v-if="lines.length === 0"
                    class="text-2xl text-bo-muted"
                >
                    Нет позиций
                </p>

                <div class="flex flex-wrap gap-4">
                    <UiButton
                        v-if="canStart"
                        class="!h-16 !min-w-[12rem] !bg-amber-400 !px-8 !text-2xl !font-bold !text-slate-900"
                        :disabled="actionApi.loading"
                        @click="onStart"
                    >
                        Старт
                    </UiButton>
                    <UiButton
                        v-if="canComplete"
                        class="!h-16 !min-w-[12rem] !bg-emerald-500 !px-8 !text-2xl !font-bold !text-white"
                        :disabled="actionApi.loading"
                        @click="onComplete"
                    >
                        Завершить
                    </UiButton>
                    <p
                        v-else-if="ticket.status === 'started' && !isMine"
                        class="text-2xl text-bo-muted"
                    >
                        Тикет у другого повара
                    </p>
                </div>
            </div>
        </UiCard>
    </div>
</template>
