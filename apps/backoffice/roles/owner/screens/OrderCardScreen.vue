<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { cancelOrder, getOrder } from "../../../api/orders.js";
import OrderFacts from "../../../shell/components/order/OrderFacts.vue";
import UiBadge from "../../../shell/components/ui/UiBadge.vue";
import UiButton from "../../../shell/components/ui/UiButton.vue";
import UiConfirmDialog from "../../../shell/components/ui/UiConfirmDialog.vue";
import { useApi } from "../../../shell/composables/useApi.js";
import { useBranchChoice } from "../../../shell/composables/useBranchChoice.js";
import { useToast } from "../../../shell/composables/useToast.js";
import {
    orderNumberOf,
    orderStatusLabel,
    orderStatusVariant,
    ownerCommands,
} from "../../../shell/domain/orderStage.js";

const route = useRoute();
const router = useRouter();
const toast = useToast();
const detailApi = useApi();
const actionApi = useApi();
const { branches, load: loadBranches, labelOf } = useBranchChoice();

const order = ref(null);
const cancelOpen = ref(false);
const orderId = computed(() => route.params.id);
const branchTitle = computed(() => labelOf(order.value?.branch_id));
const branchCity = computed(() => {
    const branch = branches.value.find(
        (item) => String(item.id) === String(order.value?.branch_id ?? ""),
    );
    return String(branch?.city ?? "").trim();
});
const commands = computed(() => ownerCommands(order.value));
const clientId = computed(() => {
    const id = Number(order.value?.client?.id ?? order.value?.client_id ?? 0);
    return id > 0 ? id : null;
});
const clientPhone = computed(() => String(order.value?.client?.phone ?? "").trim());
const canCancel = computed(() => commands.value.some((item) => item.id === "cancel"));

async function load() {
    if (!orderId.value) {
        return;
    }
    try {
        order.value = await detailApi.run(() => getOrder(orderId.value));
    } catch (err) {
        order.value = null;
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить заказ");
    }
}

async function copyPhone() {
    if (!clientPhone.value) {
        toast.error("Телефон не указан");
        return;
    }
    try {
        await navigator.clipboard.writeText(clientPhone.value);
        toast.success("Телефон скопирован");
    } catch {
        toast.error("Не удалось скопировать");
    }
}

function openClient() {
    if (!clientId.value) {
        toast.error("Клиент не привязан к аккаунту");
        return;
    }
    router.push({ name: "owner-client", params: { id: String(clientId.value) } });
}

async function submitCancel() {
    cancelOpen.value = false;
    if (!order.value?.id) {
        return;
    }
    try {
        order.value = await actionApi.run(() => cancelOrder(order.value.id));
        toast.success("Заказ отменён");
    } catch (err) {
        toast.error(err instanceof Error ? err.message : "Не удалось отменить");
    }
}

watch(orderId, load);
onMounted(async () => {
    await loadBranches();
    await load();
});
</script>

<template>
    <div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    @click="router.push({ name: 'owner-orders' })"
                >
                    К списку заказов
                </UiButton>
                <h1 class="text-lg font-semibold tracking-tight text-bo-fg">
                    {{ order ? orderNumberOf(order) : `Заказ #${orderId}` }}
                </h1>
                <UiBadge
                    v-if="order"
                    :variant="orderStatusVariant(order.status)"
                    :text="orderStatusLabel(order.status)"
                />
            </div>
            <div class="flex flex-wrap gap-2">
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    :disabled="!clientPhone || detailApi.loading"
                    @click="copyPhone"
                >
                    Копировать телефон
                </UiButton>
                <UiButton
                    class="!bg-white/10 !text-bo-fg"
                    :disabled="!clientId || detailApi.loading"
                    @click="openClient"
                >
                    К клиенту
                </UiButton>
                <UiButton
                    v-if="canCancel"
                    :disabled="actionApi.loading || detailApi.loading"
                    @click="cancelOpen = true"
                >
                    Отменить
                </UiButton>
            </div>
        </div>

        <p v-if="detailApi.loading" class="text-sm text-bo-muted">Загрузка…</p>
        <p v-else-if="!order" class="text-sm text-bo-muted">Заказ не найден.</p>
        <OrderFacts v-else :order="order" :branch-title="branchTitle" :branch-city="branchCity" />

        <UiConfirmDialog
            :open="cancelOpen"
            title="Отменить заказ"
            message="Отменить заказ? Тикет, остановка и платёж закроются вместе с отменой."
            @confirm="submitCancel"
            @cancel="cancelOpen = false"
        />
    </div>
</template>
