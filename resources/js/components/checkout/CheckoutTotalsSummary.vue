<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useSelectedGiftSummary } from "../../features/checkout/useSelectedGiftSummary";
import { useCheckoutPricingStore } from "../../stores/checkoutPricingStore";

const chk = useAppDesign().components.checkout;
const c = chk.cart;

const { checkoutState } = useCheckoutFlowContext();
const { totalAmount, itemsTotalAmount, deliveryFeeAmount, isDeliveryFree, formatPrice } =
    checkoutState;

const cartStore = useCheckoutPricingStore();
const { hasDeliveryPricing, deliveryPricing } = storeToRefs(cartStore);
const selectedGiftSummary = useSelectedGiftSummary();
const isDeliveryPreview = computed(() => deliveryPricing.value?.isPreview === true);
</script>

<template>
    <div :class="c.totalsCard">
        <div :class="c.totalsRow">
            <span :class="c.totalsLabelMuted">Товары</span>
            <span :class="c.totalsValue">{{ formatPrice(itemsTotalAmount) }} ₽</span>
        </div>
        <div
            v-if="selectedGiftSummary"
            :class="c.totalsRow"
        >
            <span :class="c.totalsLabelMuted">Подарок</span>
            <span :class="c.totalsValue">
                {{ selectedGiftSummary.name }} · Бесплатно
            </span>
        </div>
        <div
            v-if="hasDeliveryPricing"
            :class="c.totalsRow"
        >
            <span :class="c.totalsLabelMuted">
                Доставка
                <span
                    v-if="isDeliveryPreview"
                    class="text-[10px] text-app-muted"
                >
                    (курьер)
                </span>
            </span>
            <span
                v-if="isDeliveryFree"
                :class="c.totalsValue"
            >
                Бесплатно
            </span>
            <span
                v-else
                :class="c.totalsValue"
            >
                {{ formatPrice(deliveryFeeAmount) }} ₽
            </span>
        </div>
        <div :class="c.totalsDivider">
            <span :class="c.totalsLabelStrong">Итого</span>
            <span :class="c.grandTotal">{{ formatPrice(totalAmount) }} ₽</span>
        </div>
    </div>
</template>
