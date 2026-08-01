<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useOrderPreview } from "../../features/checkout/useOrderPreview";
import CheckoutSection from "./CheckoutSection.vue";

const props = defineProps({
    /** @type {'items'|'delivery'|'full'} */
    depth: {
        type: String,
        default: "items",
        validator: (value) => ["items", "delivery", "full"].includes(value),
    },
    title: {
        type: String,
        default: "Итого",
    },
    wrapSection: {
        type: Boolean,
        default: true,
    },
    /** default — cart glass; light — confirm panel */
    surface: {
        type: String,
        default: "default",
        validator: (value) => ["default", "light"].includes(value),
    },
});

const chk = useAppDesign().components.checkout;
const c = chk.cart;
const cf = chk.confirm;

const totalsCardClass = computed(() =>
    props.surface === "light" ? cf.totalsCard : c.totalsCard,
);
const { checkoutState } = useCheckoutFlowContext();
const { formatPrice, checkoutIntent } = checkoutState;

const {
    giftSummary,
    totals,
    hasDeliveryPricing,
    showOutsideZoneSurcharge,
    showBaseDeliveryFee,
    baseDeliveryFeeRubles,
    isBaseDeliveryFree,
    displayGrandTotalRubles,
    hasCartItems,
} = useOrderPreview();

const showDeliveryRows = computed(
    () =>
        (props.depth === "delivery" || props.depth === "full")
        && checkoutIntent.deliveryInfo.method === "courier",
);

const grandTotalRubles = computed(() =>
    props.depth === "items"
        ? totals.value.itemsTotalRubles
        : displayGrandTotalRubles.value,
);

const visible = computed(() => {
    if (!hasCartItems.value) {
        return false;
    }
    if (props.depth === "items") {
        return true;
    }
    return props.depth === "delivery" || props.depth === "full";
});
</script>

<template>
    <CheckoutSection
        v-if="visible && wrapSection"
        :title="title"
    >
        <div :class="totalsCardClass">
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
                    {{ giftSummary.name }}
                </span>
            </div>
            <div
                v-if="showDeliveryRows && hasDeliveryPricing && showBaseDeliveryFee"
                :class="c.totalsRow"
            >
                <span :class="c.totalsLabelMuted">Доставка</span>
                <span
                    v-if="isBaseDeliveryFree"
                    :class="c.totalsValue"
                >
                    Бесплатно
                </span>
                <span
                    v-else
                    :class="c.totalsValue"
                >
                    {{ formatPrice(baseDeliveryFeeRubles) }} ₽
                </span>
            </div>
            <div
                v-if="showDeliveryRows && hasDeliveryPricing && showOutsideZoneSurcharge"
                :class="c.totalsRow"
            >
                <span :class="c.totalsLabelMuted">Доплата за отдалённый район</span>
                <span :class="c.totalsValue">
                    {{ formatPrice(totals.outsideZoneSurchargeRubles) }} ₽
                </span>
            </div>
            <div :class="c.totalsDivider">
                <span :class="c.totalsLabelStrong">Итого</span>
                <span :class="c.grandTotal">
                    {{ formatPrice(grandTotalRubles) }} ₽
                </span>
            </div>
        </div>
    </CheckoutSection>

    <div
        v-else-if="visible"
        :class="totalsCardClass"
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
                {{ giftSummary.name }}
            </span>
        </div>
        <div
            v-if="showDeliveryRows && hasDeliveryPricing && showBaseDeliveryFee"
            :class="c.totalsRow"
        >
            <span :class="c.totalsLabelMuted">Доставка</span>
            <span
                v-if="isBaseDeliveryFree"
                :class="c.totalsValue"
            >
                Бесплатно
            </span>
            <span
                v-else
                :class="c.totalsValue"
            >
                {{ formatPrice(baseDeliveryFeeRubles) }} ₽
            </span>
        </div>
        <div
            v-if="showDeliveryRows && hasDeliveryPricing && showOutsideZoneSurcharge"
            :class="c.totalsRow"
        >
            <span :class="c.totalsLabelMuted">Доплата за отдалённый район</span>
            <span :class="c.totalsValue">
                {{ formatPrice(totals.outsideZoneSurchargeRubles) }} ₽
            </span>
        </div>
        <div :class="c.totalsDivider">
            <span :class="c.totalsLabelStrong">Итого</span>
            <span :class="c.grandTotal">
                {{ formatPrice(grandTotalRubles) }} ₽
            </span>
        </div>
    </div>
</template>
