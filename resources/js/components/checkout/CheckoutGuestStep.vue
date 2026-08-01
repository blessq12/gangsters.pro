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
    goToUpsell,
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
        <div :class="s.registerPitchCard">
            <div class="space-y-1.5">
                <p :class="s.registerPitchEyebrow">
                    {{ CHECKOUT_NAV_LABELS.authRegisterEyebrow }}
                </p>
                <p :class="s.registerPitchTitle">
                    {{ CHECKOUT_NAV_LABELS.authRegisterPitch }}
                </p>
            </div>

            <ul :class="s.registerPitchList">
                <li
                    v-for="benefit in CHECKOUT_NAV_LABELS.authRegisterBenefits"
                    :key="benefit"
                    :class="s.registerPitchListItem"
                >
                    <span
                        :class="s.registerPitchListMark"
                        aria-hidden="true"
                    >•</span>
                    <span>{{ benefit }}</span>
                </li>
            </ul>

            <button
                type="button"
                :class="s.registerPitchBtn"
                @click="openProfileDock"
            >
                {{ CHECKOUT_NAV_LABELS.authRegisterCta }}
            </button>
        </div>

        <CheckoutSection
            title="Или оформить как гость"
            variant="inset"
        >
            <FormField
                label="Имя"
                :error="guestFieldErrors.get('name')"
            >
                <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
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

            <FormField
                label="Телефон"
                :error="guestFieldErrors.get('phone')"
            >
                <template #default="{ id, invalid, invalidClass, describedBy, ariaInvalid }">
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

        <template #nav>
            <CheckoutStepNav
                :primary-label="CHECKOUT_NAV_LABELS.guestPrimary"
                @back="goToUpsell"
                @primary="goToGuestNext"
            />
        </template>
    </CheckoutStepFrame>
</template>
