<script setup>
import { computed, ref, watch } from "vue";
import { getOrder } from "../../../api/orders.js";
import { useApi } from "../../composables/useApi.js";
import { useBranchChoice } from "../../composables/useBranchChoice.js";
import { useToast } from "../../composables/useToast.js";
import UiBadge from "../ui/UiBadge.vue";
import UiButton from "../ui/UiButton.vue";
import UiModal from "../ui/UiModal.vue";
import {
    deliveryMethodLabel,
    formatAddress,
    formatRubles,
    orderNumberOf,
    orderStatusLabel,
    orderStatusVariant,
    paymentHint,
    paymentLooksStuck,
    paymentStatusLabel,
    paymentStatusVariant,
    stageHint,
} from "../../domain/orderStage.js";

const props = defineProps({
    open: { type: Boolean, default: false },
    orderId: { type: [Number, String, null], default: null },
});

const emit = defineEmits(["close", "open-full"]);

const toast = useToast();
const detailApi = useApi();
const { load: loadBranches, labelOf } = useBranchChoice();

const order = ref(null);

const title = computed(() => {
    if (!order.value) {
        return "Заказ";
    }
    return orderNumberOf(order.value);
});

const branchText = computed(() => {
    if (!order.value) {
        return "—";
    }
    return labelOf(order.value.branch_id)
        || (order.value.branch_id ? `Филиал #${order.value.branch_id}` : "Ещё не назначен");
});

const clientPhone = computed(() => String(order.value?.client?.phone ?? "").trim());
const clientName = computed(() => String(order.value?.client?.name ?? "").trim() || "Без имени");
const clientComment = computed(() => {
    const raw = order.value?.delivery?.address?.comment
        ?? order.value?.delivery?.comment
        ?? "";
    return String(raw).trim();
});

const isCourier = computed(() => String(order.value?.delivery?.method ?? "") === "courier");
const address = computed(() => formatAddress(order.value?.delivery?.address));
const stuck = computed(() => paymentLooksStuck(order.value));
const hint = computed(() => (order.value ? stageHint(order.value) : ""));
const paymentText = computed(() => (order.value ? paymentHint(order.value) : ""));

const cartSummary = computed(() => {
    const lines = Array.isArray(order.value?.cart?.lines) ? order.value.cart.lines : [];
    const counts = { regular: 0, set: 0, gift: 0, complement: 0, other: 0 };
    for (const line of lines) {
        const type = String(line?.line_type ?? "regular");
        if (type === "regular" || type === "set" || type === "gift" || type === "complement") {
            counts[type] += 1;
        } else {
            counts.other += 1;
        }
    }
    const parts = [];
    if (counts.regular > 0) {
        parts.push(`заказ ${counts.regular}`);
    }
    if (counts.set > 0) {
        parts.push(`комплект ${counts.set}`);
    }
    if (counts.gift > 0) {
        parts.push(`подарок ${counts.gift}`);
    }
    if (counts.complement > 0) {
        parts.push(`доп. ${counts.complement}`);
    }
    if (counts.other > 0) {
        parts.push(`прочее ${counts.other}`);
    }
    return parts.length > 0 ? parts.join(" · ") : "Пусто";
});

async function load() {
    if (!props.orderId) {
        order.value = null;
        return;
    }
    try {
        order.value = await detailApi.run(() => getOrder(props.orderId));
    } catch (err) {
        order.value = null;
        toast.error(err instanceof Error ? err.message : "Не удалось загрузить заказ");
    }
}

function onOpenFull() {
    if (!props.orderId) {
        return;
    }
    emit("open-full", props.orderId);
}

watch(
    () => [props.open, props.orderId],
    async ([isOpen]) => {
        if (!isOpen) {
            return;
        }
        await loadBranches();
        await load();
    },
);
</script>

<template>
    <UiModal :open="open" :title="title" @close="emit('close')">
        <div v-if="detailApi.loading" class="text-sm text-bo-muted">
            Загрузка…
        </div>
        <div v-else-if="!order" class="text-sm text-bo-muted">
            Заказ не найден.
        </div>
        <div v-else class="space-y-4 text-sm">
            <section class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <UiBadge
                        :variant="orderStatusVariant(order.status)"
                        :text="orderStatusLabel(order.status)"
                    />
                </div>
                <p
                    class="font-medium text-bo-fg"
                    :class="stuck ? 'text-red-300' : ''"
                >
                    {{ hint }}
                </p>
            </section>

            <section class="space-y-1 border-t border-bo-border pt-3">
                <p class="text-xs font-medium uppercase tracking-wide text-bo-muted">Клиент</p>
                <p class="text-base font-semibold text-bo-fg">
                    {{ clientPhone || "Телефон не указан" }}
                </p>
                <p class="text-bo-muted">{{ clientName }}</p>
                <p v-if="clientComment" class="whitespace-pre-wrap text-bo-fg">
                    {{ clientComment }}
                </p>
            </section>

            <section class="space-y-1 border-t border-bo-border pt-3">
                <p class="text-xs font-medium uppercase tracking-wide text-bo-muted">
                    {{ isCourier ? "Доставка" : "Самовывоз" }}
                </p>
                <p class="text-bo-fg">
                    {{ deliveryMethodLabel(order.delivery?.method) }}
                    · {{ branchText }}
                </p>
                <p v-if="isCourier" class="text-bo-fg">
                    {{ address || "Адрес не указан" }}
                </p>
            </section>

            <section class="space-y-1 border-t border-bo-border pt-3">
                <p class="text-xs font-medium uppercase tracking-wide text-bo-muted">Финансы</p>
                <p class="text-lg font-semibold text-bo-fg">
                    {{ formatRubles(order) }}
                </p>
                <p class="flex flex-wrap items-center gap-2 text-bo-fg">
                    <UiBadge
                        v-if="order.payment"
                        :variant="paymentStatusVariant(order.payment.status)"
                        :text="paymentStatusLabel(order.payment.status)"
                    />
                    <span v-else class="text-bo-muted">Нет платежа</span>
                </p>
                <p
                    v-if="order.payment"
                    :class="stuck ? 'text-red-300' : 'text-bo-muted'"
                >
                    {{ paymentText }}
                </p>
            </section>

            <section class="space-y-1 border-t border-bo-border pt-3">
                <p class="text-xs font-medium uppercase tracking-wide text-bo-muted">Корзина</p>
                <p class="text-bo-fg">{{ cartSummary }}</p>
            </section>
        </div>

        <template #footer>
            <UiButton class="!bg-white/10 !text-bo-fg" @click="emit('close')">
                Закрыть
            </UiButton>
            <UiButton :disabled="!orderId" @click="onOpenFull">
                Открыть карточку
            </UiButton>
        </template>
    </UiModal>
</template>
