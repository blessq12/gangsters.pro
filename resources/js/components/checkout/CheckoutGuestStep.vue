<script setup>
import { ref, watch } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useRuPhoneModel } from "../../composables/client/useRuPhoneModel";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import {
    normalizeRuPhoneDigits,
    RU_PHONE_MASKA_PATTERN,
    RU_PHONE_MASKA_TOKENS_ATTR,
} from "../../validation/ruPhone";
import FormField from "../ui/FormField.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";

const s = useAppDesign().components.checkout.shared;

const {
    checkoutState,
    goToCart,
    goToGuestNext,
    setGuestContact,
    openProfileDock,
} = useCheckoutFlowContext();

const c = useAppDesign().components.checkout.cart;

const { checkoutIntent, guestFieldErrors } = checkoutState;

const guestPhoneForm = ref({
    phone: normalizeRuPhoneDigits(checkoutIntent.guestContact.phone),
});

const { phoneMask } = useRuPhoneModel(guestPhoneForm, "phone");

watch(
    () => guestPhoneForm.value.phone,
    (digits) => {
        const n = normalizeRuPhoneDigits(digits);
        const cur = normalizeRuPhoneDigits(checkoutIntent.guestContact.phone);
        if (n !== cur) {
            setGuestContact({ phone: n });
        }
    },
);

watch(
    () => checkoutIntent.guestContact.phone,
    (p) => {
        const n = normalizeRuPhoneDigits(p);
        if (n !== guestPhoneForm.value.phone) {
            guestPhoneForm.value.phone = n;
        }
    },
);
</script>

<template>
    <CheckoutStepFrame group="guest">
        <CheckoutSection
            title="Контакт"
            variant="form"
        >
            <FormField :error="guestFieldErrors.get('name')">
                <template #default="{ id, invalid, invalidClass, describedBy, 'aria-invalid': ariaInvalid }">
                    <input
                        :id="id"
                        :value="checkoutIntent.guestContact.name"
                        type="text"
                        placeholder="Имя"
                        :class="[s.inputFieldFull, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                        @input="setGuestContact({ name: $event.target.value })"
                    />
                </template>
            </FormField>

            <FormField :error="guestFieldErrors.get('phone')">
                <template #default="{ id, invalid, invalidClass, describedBy, 'aria-invalid': ariaInvalid }">
                    <input
                        :id="id"
                        v-model="phoneMask.masked"
                        v-maska="phoneMask"
                        :data-maska="RU_PHONE_MASKA_PATTERN"
                        :data-maska-tokens="RU_PHONE_MASKA_TOKENS_ATTR"
                        type="tel"
                        placeholder="+7 (___) ___-__-__"
                        :class="[s.inputFieldFull, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                    />
                </template>
            </FormField>
        </CheckoutSection>

        <button
            type="button"
            :class="c.authCtaBtn"
            @click="openProfileDock"
        >
            Регистрация / вход
        </button>

        <template #nav>
            <button
                type="button"
                :class="s.linkUnderline"
                @click="goToCart"
            >
                Назад
            </button>
            <button
                type="button"
                :class="s.btnPrimarySm"
                @click="goToGuestNext"
            >
                Далее
            </button>
        </template>
    </CheckoutStepFrame>
</template>

<style scoped>
@keyframes checkout-auth-shimmer {
    0% {
        transform: translateX(-140%) skewX(-18deg);
    }

    40%,
    100% {
        transform: translateX(140%) skewX(-18deg);
    }
}

.checkout-auth-cta::after {
    content: "";
    position: absolute;
    inset: -20% 0;
    z-index: 0;
    pointer-events: none;
    background: linear-gradient(
        105deg,
        transparent 38%,
        rgba(255, 255, 255, 0.08) 44%,
        rgba(255, 220, 180, 0.55) 50%,
        rgba(255, 255, 255, 0.12) 56%,
        transparent 62%
    );
    animation: checkout-auth-shimmer 2.4s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
    .checkout-auth-cta::after {
        display: none;
    }
}
</style>
