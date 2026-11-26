<script setup>
import { vMaska } from "maska";
import { computed, reactive, watch } from "vue";
import { RU_PHONE_MASKA_PATTERN } from "../../phone/ruPhoneMask.js";

const props = defineProps({
    modelValue: { type: String, default: "" },
    type: { type: String, default: "text" },
    name: { type: String, default: "" },
    required: { type: Boolean, default: false },
    autocomplete: { type: String, default: "off" },
    placeholder: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue"]);

const isPhone = computed(() => props.type === "tel");

const resolvedPlaceholder = computed(() => {
    if (props.placeholder) {
        return props.placeholder;
    }
    return isPhone.value ? "+7 (___) ___-__-__" : "";
});

/** Состояние maska v2: директива пишет masked/unmasked сюда. */
const phoneMask = reactive({
    masked: "",
    unmasked: "",
    completed: false,
});

let skipPhoneEmit = false;

watch(
    () => props.modelValue,
    (value) => {
        if (!isPhone.value) {
            return;
        }
        const next = value ?? "";
        if (phoneMask.masked === next) {
            return;
        }
        skipPhoneEmit = true;
        phoneMask.masked = next;
    },
    { immediate: true },
);

watch(
    () => phoneMask.masked,
    (masked) => {
        if (!isPhone.value) {
            return;
        }
        if (skipPhoneEmit) {
            skipPhoneEmit = false;
            return;
        }
        if (masked !== (props.modelValue ?? "")) {
            emit("update:modelValue", masked);
        }
    },
);

function onInput(event) {
    emit("update:modelValue", event.target.value);
}
</script>

<template>
    <input
        v-if="isPhone"
        v-model="phoneMask.masked"
        v-maska="phoneMask"
        :data-maska="RU_PHONE_MASKA_PATTERN"
        type="tel"
        inputmode="tel"
        :name="name || undefined"
        :required="required"
        :autocomplete="autocomplete"
        :placeholder="resolvedPlaceholder"
        :disabled="disabled"
        class="mt-2 h-11 w-full border border-bo-border bg-bo-field px-4 text-bo-fg transition-colors placeholder:text-bo-muted/60 hover:border-bo-border-strong focus:border-bo-accent focus:outline-none disabled:cursor-not-allowed disabled:opacity-40"
    />
    <input
        v-else
        :type="type"
        :name="name || undefined"
        :value="modelValue"
        :required="required"
        :autocomplete="autocomplete"
        :placeholder="resolvedPlaceholder"
        :disabled="disabled"
        class="mt-2 h-11 w-full border border-bo-border bg-bo-field px-4 text-bo-fg transition-colors placeholder:text-bo-muted/60 hover:border-bo-border-strong focus:border-bo-accent focus:outline-none disabled:cursor-not-allowed disabled:opacity-40"
        @input="onInput"
    />
</template>
