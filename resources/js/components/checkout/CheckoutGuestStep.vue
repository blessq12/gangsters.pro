<script setup>
import { ref, watch } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useRuPhoneModel } from "../../composables/client/useRuPhoneModel";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { CHECKOUT_NAV_LABELS } from "../../features/checkout/checkoutWizardLabels";
import {
    formatRuPhoneCanonical,
    RU_PHONE_MASKA_PATTERN,
    RU_PHONE_MASKA_TOKENS_ATTR,
} from "../../validation/ruPhone";
import FormField from "../ui/FormField.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutStepNav from "./CheckoutStepNav.vue";

const s = useAppDesign().components.checkout.shared;

const {
    checkoutState,
    goToCart,
    goToGuestNext,
    openProfileDock,
    setGuestContact,
} = useCheckoutFlowContext();

const { checkoutIntent, guestFieldErrors } = checkoutState;

const guestPhoneForm = ref({
    phone: formatRuPhoneCanonical(checkoutIntent.guestContact.phone),
});

const { phoneMask } = useRuPhoneModel(guestPhoneForm, "phone");

watch(
    () => guestPhoneForm.value.phone,
    (digits) => {
        const formatted = formatRuPhoneCanonical(digits);
        const cur = checkoutIntent.guestContact.phone;
        if (formatted && formatted !== cur) {
            setGuestContact({ phone: formatted });
        }
    },
);

watch(
    () => checkoutIntent.guestContact.phone,
    (p) => {
        const formatted = formatRuPhoneCanonical(p);
        if (formatted && formatted !== guestPhoneForm.value.phone) {
            guestPhoneForm.value.phone = formatted;
        }
    },
);
</script>

<template>
    <CheckoutStepFrame group="guest">
        <CheckoutSection
            title="Контакт"
            variant="inset"
        >
            <FormField
                label="Имя"
                :error="guestFieldErrors.get('name')"
            >
                <template #default="{ id, invalid, invalidClass, describedBy, 'aria-invalid': ariaInvalid }">
                    <input
                        :id="id"
                        :value="checkoutIntent.guestContact.name"
                        type="text"
                        placeholder="Как к тебе обращаться"
                        :class="[s.inputFieldFull, invalid && invalidClass]"
                        :aria-invalid="ariaInvalid"
                        :aria-describedby="describedBy"
                        @input="setGuestContact({ name: $event.target.value })"
                    />
                </template>
            </FormField>

            <FormField
                label="Телефон"
                :error="guestFieldErrors.get('phone')"
            >
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
            :class="s.linkUnderline"
            @click="openProfileDock"
        >
            {{ CHECKOUT_NAV_LABELS.authLink }}
        </button>

        <template #nav>
            <CheckoutStepNav
                :primary-label="CHECKOUT_NAV_LABELS.next"
                @back="goToCart"
                @primary="goToGuestNext"
            />
        </template>
    </CheckoutStepFrame>
</template>
