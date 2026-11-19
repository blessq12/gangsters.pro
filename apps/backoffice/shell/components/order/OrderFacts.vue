<script setup>
import { computed } from "vue";
import UiBadge from "../ui/UiBadge.vue";
import {
    deliveryMethodLabel,
    formatAddress,
    formatKopecks,
    formatLineRubles,
    formatRubles,
    orderStatusLabel,
    orderStatusVariant,
    paymentHint,
    paymentLooksStuck,
    paymentMethodLabel,
    paymentStatusLabel,
    paymentStatusVariant,
    stageHint,
} from "../../domain/orderStage.js";

const props = defineProps({
    order: { type: Object, required: true },
    kitchenTitle: { type: String, default: "" },
});

const lines = computed(() => props.order?.cart?.lines ?? []);
const address = computed(() => formatAddress(props.order?.delivery?.address));
const hint = computed(() => stageHint(props.order));
const paymentText = computed(() => paymentHint(props.order));
const stuck = computed(() => paymentLooksStuck(props.order));

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
</script>

<template>
    <div class="space-y-5 text-sm text-bo-fg">
        <div class="border border-bo-border bg-white/[0.03] px-4 py-3">
            <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-bo-muted">
                Где заказ
            </p>
            <p class="mt-2 text-bo-fg">
                {{ hint }}
            </p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <p>
                <span class="text-bo-muted">Заказ</span>
                <span class="mt-1 block text-lg font-semibold">#{{ order.id }}</span>
            </p>
            <p>
                <span class="text-bo-muted">Этап</span>
                <span class="mt-1 block">
                    <UiBadge
                        :variant="orderStatusVariant(order.status)"
                        :text="orderStatusLabel(order.status)"
                    />
                </span>
            </p>
            <p>
                <span class="text-bo-muted">Клиент</span>
                <span class="mt-1 block">
                    {{ order.client?.name || "—" }}
                    <span v-if="order.client?.phone" class="text-bo-muted">· {{ order.client.phone }}</span>
                </span>
            </p>
            <p>
                <span class="text-bo-muted">Получение</span>
                <span class="mt-1 block">{{ deliveryMethodLabel(order.delivery?.method) }}</span>
            </p>
            <p class="sm:col-span-2">
                <span class="text-bo-muted">Адрес</span>
                <span class="mt-1 block">{{ address || "Самовывоз, адреса доставки нет" }}</span>
                <span v-if="order.delivery?.address?.comment" class="mt-1 block text-bo-muted">
                    {{ order.delivery.address.comment }}
                </span>
            </p>
            <p>
                <span class="text-bo-muted">Кухня</span>
                <span class="mt-1 block">{{ kitchenTitle || (order.kitchen_id ? `Кухня #${order.kitchen_id}` : "Ещё не назначена") }}</span>
            </p>
            <p>
                <span class="text-bo-muted">Сумма</span>
                <span class="mt-1 block">{{ formatRubles(order) }}</span>
            </p>
            <p>
                <span class="text-bo-muted">Создан</span>
                <span class="mt-1 block">{{ formatDate(order.created_at) }}</span>
            </p>
        </div>

        <div
            class="border px-4 py-3"
            :class="stuck ? 'border-red-400/50 bg-red-500/10' : 'border-bo-border bg-white/[0.03]'"
        >
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-[11px] font-medium uppercase tracking-[0.12em] text-bo-muted">
                    Платёж
                </p>
                <UiBadge
                    v-if="order.payment"
                    :variant="paymentStatusVariant(order.payment.status)"
                    :text="paymentStatusLabel(order.payment.status)"
                />
                <UiBadge v-else variant="neutral" text="Нет платежа" />
            </div>
            <p class="mt-2">
                {{ paymentText }}
            </p>
            <p v-if="order.payment" class="mt-2 text-bo-muted">
                {{ paymentMethodLabel(order.payment.method) }}
                · {{ formatKopecks(order.payment.amount_kopecks) }}
            </p>
        </div>

        <div>
            <p class="mb-2 font-medium">Состав</p>
            <ul class="space-y-2">
                <li
                    v-for="(line, idx) in lines"
                    :key="idx"
                    class="flex items-baseline justify-between gap-3 border border-bo-border px-3 py-2"
                >
                    <span>{{ line.name || `Позиция #${line.product_id}` }} × {{ line.quantity }}</span>
                    <span class="text-bo-muted">{{ formatLineRubles(line) }}</span>
                </li>
            </ul>
            <p v-if="lines.length === 0" class="text-bo-muted">
                В заказе нет позиций.
            </p>
        </div>
    </div>
</template>
