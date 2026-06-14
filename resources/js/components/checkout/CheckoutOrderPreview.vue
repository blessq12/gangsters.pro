<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useOrderPreview } from "../../features/checkout/useOrderPreview";
import { useUiStore } from "../../stores/uiStore";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";
import CheckoutSection from "./CheckoutSection.vue";

const props = defineProps({
    variant: {
        type: String,
        required: true,
        validator: (value) =>
            ["cart", "delivery", "payment", "confirm"].includes(value),
    },
    part: {
        type: String,
        default: null,
        validator: (value) => value == null || ["gift", "totals"].includes(value),
    },
});

const chk = useAppDesign().components.checkout;
const c = chk.cart;
const cf = chk.confirm;
const s = chk.shared;

const { checkoutState } = useCheckoutFlowContext();
const { formatPrice } = checkoutState;
const uiStore = useUiStore();

const {
    complementLines,
    autoLines,
    giftSummary,
    totals,
    hasComplementLines,
    hasAutoLines,
    hasDeliveryPricing,
    hasCartItems,
    delivery,
    gift,
    deliveryProgressPercent,
    giftProgressPercent,
    deliveryLabel,
    giftLabel,
    canShowBenefits,
    showGiftProgress,
    isGiftEligible,
    hasGiftSelected,
    giftCtaLabel,
    giftCandidates,
    selectedGiftName,
} = useOrderPreview();

const showComplementBlock = computed(
    () =>
        props.variant === "cart"
        && props.part == null
        && hasComplementLines.value,
);
const showAutoBlock = computed(
    () => props.variant === "cart" && props.part == null && hasAutoLines.value,
);
const showDeliveryBenefit = computed(
    () =>
        (props.variant === "cart" || props.variant === "delivery")
        && Boolean(deliveryLabel.value)
        && (props.variant === "cart" ? delivery.value.isActive : true),
);
const showBenefitsBlock = computed(() => {
    if (props.part != null) {
        return false;
    }

    if (props.variant === "cart" || props.variant === "delivery") {
        return canShowBenefits.value;
    }

    if (props.variant === "payment") {
        return showGiftBenefit.value;
    }

    return false;
});
const showGiftBenefit = computed(
    () =>
        props.variant === "payment"
        && showGiftProgress.value
        && giftLabel.value,
);
const showGiftCta = computed(
    () =>
        props.variant === "confirm"
        && (props.part == null || props.part === "gift")
        && isGiftEligible.value
        && giftCandidates.value.length > 0,
);
const showTotalsBlock = computed(() => {
    if (props.part === "gift") {
        return false;
    }

    if (props.variant === "cart") {
        return hasCartItems.value;
    }

    return props.variant === "delivery"
        || props.variant === "payment"
        || props.variant === "confirm";
});

function openGiftModal() {
    if (!isGiftEligible.value) {
        return;
    }

    emitDomainEvent(DOMAIN_EVENTS.BENEFIT_BANNER_CTA_CLICK, {
        source: "confirm",
        cta: "choose_gift",
    });
    uiStore.openGiftSelectionModal({ source: "manual" });
}
</script>

<template>
    <CheckoutSection
        v-if="showGiftCta"
        title="Подарок"
    >
        <div
            :class="[
                c.giftCard,
                '!mt-0',
                !hasGiftSelected && cf.giftCardPrompt,
            ]"
        >
            <div :class="c.giftRow">
                <div class="min-w-0">
                    <p :class="c.giftTitle">
                        {{ hasGiftSelected ? selectedGiftName : "Выбери подарок" }}
                    </p>
                </div>
                <button
                    type="button"
                    :class="c.giftCta"
                    @click="openGiftModal"
                >
                    {{ giftCtaLabel }}
                </button>
            </div>
        </div>
    </CheckoutSection>

    <ul
        v-if="showComplementBlock"
        :class="c.systemList"
    >
        <li :class="s.subsectionKickerSm">
            Комплект
        </li>
        <li
            v-for="line in complementLines"
            :key="`complement:${line.productId}`"
            :class="c.systemLine"
        >
            <span :class="c.systemLineName">
                {{ line.name }}
            </span>
            <span :class="c.systemLineMeta">
                {{ line.quantity }} ×
                {{ line.isFree ? "Бесплатно" : `${formatPrice(line.priceRubles)} ₽` }}
            </span>
        </li>
    </ul>

    <CheckoutSection
        v-if="showAutoBlock"
        title="Автодобавления"
    >
        <ul :class="c.systemList">
            <li
                v-for="line in autoLines"
                :key="`auto:${line.productId}`"
                :class="c.systemLine"
            >
                <span :class="c.systemLineName">
                    {{ line.name }}
                </span>
                <span :class="c.systemLineMeta">
                    {{ line.quantity }} × {{ formatPrice(0) }} ₽
                </span>
            </li>
        </ul>
    </CheckoutSection>

    <div
        v-if="showBenefitsBlock"
        :class="[c.totalsCard, 'mt-3 space-y-3']"
    >
        <div
            v-if="showDeliveryBenefit"
            class="space-y-1"
        >
            <div class="flex items-center justify-between gap-2 text-xs text-app-muted">
                <span>Доставка</span>
                <span>{{ deliveryProgressPercent }}%</span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-app-accent-soft-bg">
                <div
                    class="h-full rounded-full bg-app-accent transition-all"
                    :style="{ width: `${deliveryProgressPercent}%` }"
                />
            </div>
            <p
                :class="delivery.isReached ? 'text-sm text-app-accent' : 'text-sm text-app-muted'"
            >
                {{ deliveryLabel }}
            </p>
        </div>

        <div
            v-if="showGiftBenefit"
            class="space-y-1"
        >
            <div class="flex items-center justify-between gap-2 text-xs text-app-muted">
                <span>Подарок</span>
                <span>{{ giftProgressPercent }}%</span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-app-accent-soft-bg">
                <div
                    class="h-full rounded-full bg-app-accent transition-all"
                    :style="{ width: `${giftProgressPercent}%` }"
                />
            </div>
            <p
                :class="gift.isReached ? 'text-sm text-app-accent' : 'text-sm text-app-muted'"
            >
                {{ giftLabel }}
            </p>
        </div>
    </div>

    <div
        v-if="showTotalsBlock"
        :class="c.totalsCard"
    >
        <div :class="c.totalsRow">
            <span :class="c.totalsLabelMuted">Товары</span>
            <span :class="c.totalsValue">
                {{ formatPrice(totals.itemsTotalRubles) }} ₽
            </span>
        </div>
        <div
            v-if="giftSummary"
            :class="c.totalsRow"
        >
            <span :class="c.totalsLabelMuted">Подарок</span>
            <span :class="c.totalsValue">
                {{ giftSummary.name }} · Бесплатно
            </span>
        </div>
        <div
            v-if="hasDeliveryPricing"
            :class="c.totalsRow"
        >
            <span :class="c.totalsLabelMuted">
                Доставка
                <span
                    v-if="totals.isDeliveryPreview"
                    class="text-[10px] text-app-muted"
                >
                    (курьер)
                </span>
            </span>
            <span
                v-if="totals.isDeliveryFree"
                :class="c.totalsValue"
            >
                Бесплатно
            </span>
            <span
                v-else
                :class="c.totalsValue"
            >
                {{ formatPrice(totals.deliveryFeeRubles) }} ₽
            </span>
        </div>
        <div :class="c.totalsDivider">
            <span :class="c.totalsLabelStrong">Итого</span>
            <span :class="c.grandTotal">
                {{ formatPrice(totals.grandTotalRubles) }} ₽
            </span>
        </div>
    </div>
</template>

<style scoped>
@keyframes checkout-gift-shimmer {
    0% {
        transform: translateX(-140%) skewX(-18deg);
    }

    40%,
    100% {
        transform: translateX(140%) skewX(-18deg);
    }
}

.checkout-gift-prompt {
    position: relative;
    overflow: hidden;
    isolation: isolate;
}

.checkout-gift-prompt::after {
    content: "";
    position: absolute;
    inset: -20% 0;
    z-index: 0;
    pointer-events: none;
    background: linear-gradient(
        105deg,
        transparent 38%,
        rgba(255, 255, 255, 0.06) 44%,
        rgba(255, 220, 180, 0.42) 50%,
        rgba(255, 255, 255, 0.1) 56%,
        transparent 62%
    );
    animation: checkout-gift-shimmer 2.6s ease-in-out infinite;
}

.checkout-gift-prompt > * {
    position: relative;
    z-index: 1;
}

@media (prefers-reduced-motion: reduce) {
    .checkout-gift-prompt::after {
        display: none;
    }
}
</style>
