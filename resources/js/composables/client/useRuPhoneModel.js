import { computed } from "vue";

/**
 * v-model для поля телефона: хранит только 10 цифр без ведущей 7/8.
 * @param {import('vue').Ref<Record<string, unknown>>} formRef
 * @param {string} fieldKey
 */
export function useRuPhoneModel(formRef, fieldKey = "phone") {
    return computed({
        get() {
            return formRef.value[fieldKey];
        },
        set(value) {
            let digits = String(value || "").replace(/\D/g, "");
            if (
                digits.length &&
                (digits[0] === "7" || digits[0] === "8")
            ) {
                digits = digits.slice(1);
            }
            formRef.value[fieldKey] = digits;
        },
    });
}
