<script setup>
import { computed, useId } from "vue";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    label: {
        type: String,
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
    errorSize: {
        type: String,
        default: "sm",
        validator: (value) => ["sm", "xs"].includes(value),
    },
    htmlFor: {
        type: String,
        default: "",
    },
});

const ff = useAppDesign().components.uiPrimitives.formField;
const generatedId = useId();
const controlId = computed(() => props.htmlFor || `field-${generatedId}`);
const errorId = computed(() => `${controlId.value}-error`);
const hasError = computed(() => Boolean(props.error?.trim()));

const slotBindings = computed(() => ({
    id: controlId.value,
    errorId: errorId.value,
    invalid: hasError.value,
    invalidClass: ff.inputInvalid,
    describedBy: hasError.value ? errorId.value : undefined,
    ariaInvalid: hasError.value ? true : undefined,
}));
</script>

<template>
    <div :class="ff.root">
        <label
            v-if="label"
            :class="ff.label"
            :for="controlId"
        >
            {{ label }}
        </label>
        <slot v-bind="slotBindings" />
        <p
            v-if="hasError"
            :id="errorId"
            role="alert"
            :class="errorSize === 'xs' ? ff.errorXs : ff.errorSm"
        >
            {{ error }}
        </p>
    </div>
</template>
