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

const chk = useAppDesign().components.checkout;
const s = chk.shared;

const {
    checkoutState,
    checkoutStepMeta,
    goToCart,
    goToGuestNext,
    setGuestContact,
} = useCheckoutFlowContext();

const { checkoutIntent, guestStepError } = checkoutState;

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
    <div :class="s.flowBody">
        <p :class="s.stepKicker">
            Шаг {{ checkoutStepMeta.guest?.n ?? 1 }} из {{ checkoutStepMeta.guest?.total ?? 4 }} — Контакт
        </p>

        <div :class="s.guestIsland">
            <p :class="s.headingSm">
                Как с тобой связаться
            </p>
            <input
                :value="checkoutIntent.guestContact.name"
                type="text"
                placeholder="Имя"
                :class="s.inputFieldFull"
                @input="setGuestContact({ name: $event.target.value })"
            />
            <input
                v-model="phoneMask.masked"
                v-maska="phoneMask"
                :data-maska="RU_PHONE_MASKA_PATTERN"
                :data-maska-tokens="RU_PHONE_MASKA_TOKENS_ATTR"
                type="tel"
                placeholder="+7 (___) ___-__-__"
                :class="s.inputFieldFull"
            />
        </div>

        <p
            v-if="guestStepError"
            :class="s.errorLine"
        >
            {{ guestStepError }}
        </p>

        <div :class="s.navFooterRow">
            <button
                type="button"
                :class="s.linkUnderline"
                @click="goToCart"
            >
                Назад к корзине
            </button>
            <button
                type="button"
                :class="s.btnPrimarySm"
                @click="goToGuestNext"
            >
                Далее: доставка
            </button>
        </div>
    </div>
</template>