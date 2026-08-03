import { computed, ref } from "vue";

/**
 * @typedef {Record<string, string>} FieldErrorMap
 */

export function useFormFieldErrors() {
    /** @type {import('vue').Ref<FieldErrorMap>} */
    const fields = ref({});
    const formError = ref("");

    const hasAny = computed(
        () =>
            Object.keys(fields.value).length > 0
            || Boolean(formError.value),
    );

    function get(key) {
        return fields.value[key] || "";
    }

    function has(key) {
        return Boolean(get(key));
    }

    function setFieldError(key, message) {
        if (!message) {
            clearField(key);
            return;
        }
        fields.value = {
            ...fields.value,
            [key]: message,
        };
    }

    /**
     * @param {FieldErrorMap} next
     */
    function setErrors(next) {
        fields.value = { ...next };
    }

    function clearField(key) {
        if (!(key in fields.value)) {
            return;
        }
        const next = { ...fields.value };
        delete next[key];
        fields.value = next;
    }

    function clearAll() {
        fields.value = {};
        formError.value = "";
    }

    function setFormError(message) {
        formError.value = message || "";
    }

    return {
        fields,
        formError,
        hasAny,
        get,
        has,
        setFieldError,
        setErrors,
        clearField,
        clearAll,
        setFormError,
    };
}
