<script setup>
import { computed } from "vue";
import UiBadge from "../ui/UiBadge.vue";
import UiCard from "../ui/UiCard.vue";
import OrderDeliveryMap from "../maps/OrderDeliveryMap.vue";
import {
    deliveryMethodLabel,
    formatAddress,
    formatKopecks,
    formatLineRubles,
    formatRubles,
    paymentHint,
    paymentLooksStuck,
    paymentMethodLabel,
    paymentStatusLabel,
    paymentStatusVariant,
} from "../../domain/orderStage.js";

const props = defineProps({
    order: { type: Object, required: true },
    branchTitle: { type: String, default: "" },
    branchCity: { type: String, default: "" },
});

const paymentText = computed(() => paymentHint(props.order));
const stuck = computed(() => paymentLooksStuck(props.order));
const totals = computed(() => props.order?.totals ?? props.order?.cart?.totals ?? {});
const isCourier = computed(() => String(props.order?.delivery?.method ?? "") === "courier");
const address = computed(() => formatAddress(props.order?.delivery?.address));
const addressRaw = computed(() => props.order?.delivery?.address ?? null);
const branchText = computed(
    () =>
        props.branchTitle ||
        (props.order?.branch_id ? `Филиал #${props.order.branch_id}` : "Ещё не назначен"),
);

const clientName = computed(() => String(props.order?.client?.name ?? "").trim() || "Без имени");
const clientPhone = computed(() => String(props.order?.client?.phone ?? "").trim());
const clientComment = computed(() => {
    const raw =
        props.order?.delivery?.address?.comment ??
        props.order?.delivery?.comment ??
        props.order?.client_comment ??
        "";
    return String(raw).trim();
});

const paymentLine = computed(() => {
    const payment = props.order?.payment;
    if (!payment) {
        return "Нет платежа";
    }
    return `${paymentMethodLabel(payment.method)} · ${formatKopecks(payment.amount_kopecks)}`;
});

const allLines = computed(() =>
    Array.isArray(props.order?.cart?.lines) ? props.order.cart.lines : [],
);
/** Наборы (set) показываем как обычные товары — без отдельного блока. */
const productLines = computed(() =>
    allLines.value.filter((line) => {
        const type = lineTypeOf(line);
        return type === "regular" || type === "set";
    }),
);
const giftLines = computed(() => allLines.value.filter((line) => lineTypeOf(line) === "gift"));
const complementLines = computed(() =>
    allLines.value.filter((line) => lineTypeOf(line) === "complement"),
);
const otherLines = computed(() =>
    allLines.value.filter((line) => {
        const type = lineTypeOf(line);
        return type !== "regular" && type !== "set" && type !== "gift" && type !== "complement";
    }),
);

const cartSummary = computed(() => {
    const parts = [];
    if (productLines.value.length > 0) {
        parts.push(`заказ ${productLines.value.length}`);
    }
    if (giftLines.value.length > 0) {
        parts.push(`подарок ${giftLines.value.length}`);
    }
    if (complementLines.value.length > 0) {
        parts.push(`доп. ${complementLines.value.length}`);
    }
    if (otherLines.value.length > 0) {
        parts.push(`прочее ${otherLines.value.length}`);
    }
    return parts.length > 0 ? parts.join(" · ") : "Пусто";
});

/**
 * @param {Record<string, unknown>|null|undefined} line
 */
function lineTypeOf(line) {
    return String(line?.line_type ?? "regular");
}

function rublesOf(key) {
    const value = totals.value?.[key];
    if (value === undefined || value === null || Number.isNaN(Number(value))) {
        return null;
    }
    return `${Number(value)} ₽`;
}
</script>

<template>
    <div class="grid gap-4 lg:grid-cols-3">
        <!-- 1/3: клиент + получение -->
        <div class="space-y-4 lg:col-span-1">
            <UiCard title="Клиент">
                <div class="space-y-2">
                    <p class="text-lg font-semibold tracking-tight text-bo-fg">
                        {{ clientPhone || "Телефон не указан" }}
                    </p>
                    <p class="text-sm text-bo-muted">
                        {{ clientName }}
                    </p>
                    <p v-if="clientComment" class="whitespace-pre-wrap text-sm text-bo-fg">
                        {{ clientComment }}
                    </p>
                    <p v-else class="text-sm text-bo-muted">Без комментария</p>
                </div>
            </UiCard>

            <UiCard :title="isCourier ? 'Доставка' : 'Самовывоз'">
                <div class="space-y-3">
                    <template v-if="isCourier">
                        <p class="text-base font-semibold leading-snug text-bo-fg">
                            {{ address || "Адрес не указан" }}
                        </p>
                        <p class="text-sm text-bo-muted">
                            {{ deliveryMethodLabel(order.delivery?.method) }}
                            · {{ branchText }} · {{ formatKopecks(order.delivery?.fee_kopecks) }}
                            <template v-if="order.delivery?.zone_id">
                                · зона {{ order.delivery.zone_id }}
                            </template>
                        </p>
                        <OrderDeliveryMap
                            v-if="addressRaw?.street"
                            :city="branchCity"
                            :street="String(addressRaw.street || '')"
                            :house="String(addressRaw.house || '')"
                        />
                        <p
                            v-else
                            class="border border-dashed border-bo-border px-3 py-6 text-center text-sm text-bo-muted"
                        >
                            Нет адреса для карты
                        </p>
                    </template>
                    <template v-else>
                        <p class="text-base font-semibold text-bo-fg">
                            {{ branchText }}
                        </p>
                        <p class="text-sm text-bo-muted">
                            {{ deliveryMethodLabel(order.delivery?.method) }}
                            · {{ formatKopecks(order.delivery?.fee_kopecks) }}
                            · адрес доставки не нужен
                        </p>
                    </template>
                </div>
            </UiCard>
        </div>

        <!-- 2/3: корзина + финансы -->
        <div class="space-y-4 lg:col-span-2">
            <UiCard title="Корзина">
                <template #actions>
                    <span class="text-sm text-bo-muted">{{ cartSummary }}</span>
                </template>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-bo-muted">
                            Линии заказа
                        </p>
                        <ul v-if="productLines.length > 0" class="space-y-1.5">
                            <li
                                v-for="(line, idx) in productLines"
                                :key="`product-${idx}`"
                                class="flex items-baseline justify-between gap-3"
                            >
                                <span class="min-w-0 font-medium text-bo-fg">
                                    {{ line.name || `Позиция #${line.product_id}` }}
                                    <span class="font-normal text-bo-muted">
                                        × {{ line.quantity }}
                                    </span>
                                </span>
                                <span class="shrink-0 text-bo-muted">
                                    {{ formatLineRubles(line) }}
                                </span>
                            </li>
                        </ul>
                        <p v-else class="text-bo-muted">Нет позиций</p>
                    </div>

                    <div v-if="giftLines.length > 0">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-bo-muted">
                            Линии подарка
                        </p>
                        <ul class="space-y-1.5">
                            <li
                                v-for="(line, idx) in giftLines"
                                :key="`gift-${idx}`"
                                class="flex items-baseline justify-between gap-3"
                            >
                                <span class="min-w-0 font-medium text-bo-fg">
                                    {{ line.name || `Подарок #${line.product_id}` }}
                                    <span class="font-normal text-bo-muted">
                                        × {{ line.quantity }}
                                    </span>
                                </span>
                                <span class="shrink-0 text-bo-muted">
                                    {{ formatLineRubles(line) }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div v-if="complementLines.length > 0">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-bo-muted">
                            Дополнения
                        </p>
                        <ul class="space-y-1.5">
                            <li
                                v-for="(line, idx) in complementLines"
                                :key="`complement-${idx}`"
                                class="flex items-baseline justify-between gap-3"
                            >
                                <span class="min-w-0 font-medium text-bo-fg">
                                    {{ line.name || `Дополнение #${line.product_id}` }}
                                    <span class="font-normal text-bo-muted">
                                        × {{ line.quantity }}
                                    </span>
                                </span>
                                <span class="shrink-0 text-bo-muted">
                                    {{ formatLineRubles(line) }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div v-if="otherLines.length > 0">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-bo-muted">
                            Прочее
                        </p>
                        <ul class="space-y-1.5">
                            <li
                                v-for="(line, idx) in otherLines"
                                :key="`other-${idx}`"
                                class="flex items-baseline justify-between gap-3"
                            >
                                <span class="min-w-0 font-medium text-bo-fg">
                                    {{ line.name || `Позиция #${line.product_id}` }}
                                    <span class="font-normal text-bo-muted">
                                        · {{ lineTypeOf(line) }} × {{ line.quantity }}
                                    </span>
                                </span>
                                <span class="shrink-0 text-bo-muted">
                                    {{ formatLineRubles(line) }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </UiCard>

            <UiCard title="Финансы">
                <template #actions>
                    <UiBadge
                        v-if="order.payment"
                        :variant="paymentStatusVariant(order.payment.status)"
                        :text="paymentStatusLabel(order.payment.status)"
                    />
                    <UiBadge v-else variant="neutral" text="Нет платежа" />
                </template>
                <div class="space-y-3">
                    <p class="text-2xl font-semibold tracking-tight text-bo-accent">
                        {{ formatRubles(order) }}
                    </p>
                    <p class="text-sm text-bo-muted">
                        Позиции {{ rublesOf("items_total_rubles") || "—" }} · Доставка
                        {{ rublesOf("delivery_fee_rubles") || "—" }}
                        <template v-if="order.payment">· {{ paymentLine }}</template>
                    </p>
                    <p
                        v-if="order.payment"
                        class="text-sm"
                        :class="stuck ? 'text-red-300' : 'text-bo-muted'"
                    >
                        {{ paymentText }}
                    </p>
                </div>
            </UiCard>
        </div>
    </div>
</template>
