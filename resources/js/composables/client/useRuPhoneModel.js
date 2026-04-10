import { Mask } from "maska";
import { reactive, watch } from "vue";
import {
    normalizeRuPhoneDigits,
    RU_PHONE_MASKA_PATTERN,
    RU_PHONE_MASKA_TOKENS,
} from "../../validation/ruPhone";

const ruMask = new Mask({
    mask: RU_PHONE_MASKA_PATTERN,
    tokens: RU_PHONE_MASKA_TOKENS,
});

function digitsToMaskedDisplay(digitsNormalized) {
    if (!digitsNormalized) {
        return "";
    }
    return ruMask.masked(digitsNormalized);
}

/**
 * Поле телефона с Maska v2: v-model на `phoneMask.masked`, директива `v-maska="phoneMask"`.
 * В форме (`formRef`) в поле `fieldKey` хранятся только цифры (до 10), без ведущей 7/8.
 * @param {import('vue').Ref<Record<string, unknown>>} formRef
 * @param {string} fieldKey
 */
export function useRuPhoneModel(formRef, fieldKey = "phone") {
    const phoneMask = reactive({
        masked: "",
        unmasked: "",
        completed: false,
    });

    let skipFormToMask = false;

    watch(
        () => phoneMask.unmasked,
        (u) => {
            const n = normalizeRuPhoneDigits(u);
            const cur = normalizeRuPhoneDigits(
                String(formRef.value[fieldKey] ?? ""),
            );
            if (cur === n) {
                return;
            }
            skipFormToMask = true;
            formRef.value[fieldKey] = n;
        },
    );

    watch(
        () => formRef.value[fieldKey],
        () => {
            if (skipFormToMask) {
                skipFormToMask = false;
                return;
            }
            const raw = String(formRef.value[fieldKey] ?? "");
            const n = normalizeRuPhoneDigits(raw);
            const maskUn = normalizeRuPhoneDigits(phoneMask.unmasked);
            if (n === maskUn) {
                if (raw !== n) {
                    skipFormToMask = true;
                    formRef.value[fieldKey] = n;
                }
                return;
            }
            const m = digitsToMaskedDisplay(n);
            if (phoneMask.masked !== m) {
                phoneMask.masked = m;
            }
            if (raw !== n) {
                skipFormToMask = true;
                formRef.value[fieldKey] = n;
            }
        },
        { immediate: true },
    );

    return { phoneMask };
}
