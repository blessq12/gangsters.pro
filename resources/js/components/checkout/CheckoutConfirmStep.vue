<script setup>
import { computed, unref } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useGiftPromotionPrompt } from "../../features/checkout/useGiftPromotionPrompt";
import {
    CHECKOUT_PAYMENT_METHOD_LABELS,
} from "../../features/checkout/checkoutPaymentMethods";
import CheckoutOrderReview from "./CheckoutOrderReview.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutTotalsSummary from "./CheckoutTotalsSummary.vue";

const chk = useAppDesign().components.checkout;
const s = chk.shared;
const c = chk.cart;
const cf = chk.confirm;

const {
    userStore,
    checkoutState,
    goToPayment,
    handleConfirmOrder,
} = useCheckoutFlowContext();

const {
    checkoutIntent,
    orderStore,
    formatPrice,
    formatPhone,
    isGuestCheckout,
    promoState,
} = checkoutState;

const {
    isGiftEligible,
    hasGiftSelected,
    giftCtaLabel,
    giftCandidates,
    selectedGiftName,
    openGiftModal,
} = useGiftPromotionPrompt(() => promoState?.value ?? promoState);

const paymentLabel = computed(() => {
    const method = checkoutIntent.paymentInfo.method;
    if (!method) {
        return "—";
    }
    return CHECKOUT_PAYMENT_METHOD_LABELS[method] ?? method;
});

const deliveryAddressLine = computed(() => {
    if (checkoutIntent.deliveryInfo.method === "pickup") {
        return "Самовывоз";
    }

    if (unref(isGuestCheckout)) {
        const address = checkoutIntent.deliveryInfo.address;
        if (!address) {
            return "—";
        }
        return [
            address.street,
            address.house && `д. ${address.house}`,
            address.apartment && `кв. ${address.apartment}`,
        ]
            .filter(Boolean)
            .join(", ");
    }

    const selected = userStore.selectedAddress;
    if (!selected) {
        return "—";
    }

    return [
        selected.street,
        selected.house && `д. ${selected.house}`,
        selected.apartment && `кв. ${selected.apartment}`,
    ]
        .filter(Boolean)
        .join(", ");
});

const clientLine = computed(() => {
    if (unref(isGuestCheckout)) {
        const name = checkoutIntent.guestContact.name || "—";
        const phone = checkoutIntent.guestContact.phone
            ? formatPhone(checkoutIntent.guestContact.phone)
            : "—";
        return `${name}, ${phone}`;
    }

    const name = userStore.profile.name || "—";
    const phone = userStore.profile.phone
        ? formatPhone(userStore.profile.phone)
        : "—";
    return `${name}, ${phone}`;
});
</script>

<template>
    <CheckoutStepFrame group="confirm">
        <CheckoutSection
            v-if="isGiftEligible && giftCandidates.length"
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

        <CheckoutSection title="Заказ">
            <div :class="cf.summaryCard">
                <CheckoutOrderReview />
                <CheckoutTotalsSummary />
            </div>
        </CheckoutSection>

        <div :class="cf.metaRow">
            <CheckoutSection
                title="Доставка · оплата"
                variant="muted"
            >
                <p :class="s.textBodyXs">
                    {{ deliveryAddressLine }}
                </p>
                <p :class="s.textBodyXs">
                    {{ paymentLabel }}
                    <span
                        v-if="checkoutIntent.paymentInfo.method === 'cash' && checkoutIntent.paymentInfo.changeFrom"
                        :class="cf.mutedInline"
                    >
                        · сдача с {{ formatPrice(checkoutIntent.paymentInfo.changeFrom) }} ₽
                    </span>
                </p>
                <p
                    v-if="checkoutIntent.customerComment"
                    :class="s.textMutedLine"
                >
                    {{ checkoutIntent.customerComment }}
                </p>
            </CheckoutSection>

            <CheckoutSection
                title="Клиент"
                variant="muted"
            >
                <p :class="s.textBodyXs">
                    {{ clientLine }}
                </p>
                <p
                    v-if="!isGuestCheckout && userStore.profile.email"
                    :class="s.textMutedLine"
                >
                    {{ userStore.profile.email }}
                </p>
            </CheckoutSection>
        </div>

        <div
            v-if="orderStore.error.create"
            :class="s.errorBanner"
        >
            {{ orderStore.error.create }}
        </div>

        <template #nav>
            <button
                type="button"
                :class="s.linkUnderline"
                @click="goToPayment"
            >
                Назад
            </button>
            <button
                type="button"
                :class="s.btnPrimarySmBusy"
                :disabled="orderStore.loading.create"
                @click="handleConfirmOrder"
            >
                <span v-if="orderStore.loading.create">
                    Отправляем…
                </span>
                <span v-else>
                    Подтвердить
                </span>
            </button>
        </template>
    </CheckoutStepFrame>
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
