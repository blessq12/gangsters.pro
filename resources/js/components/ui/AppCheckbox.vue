<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: "md",
        validator: (v) => ["md", "sm"].includes(v),
    },
});

const emit = defineEmits(["update:modelValue"]);

const fc = useAppDesign().components.uiPrimitives.formControls;

const boxClass = computed(() =>
    props.size === "sm" ? fc.checkboxSm : fc.checkbox,
);

function onChange(event) {
    emit("update:modelValue", event.target.checked);
}
</script>

<template>
    <span class="inline-flex shrink-0 items-center">
        <input
            type="checkbox"
            :class="fc.inputHidden"
            :checked="modelValue"
            :disabled="disabled"
            @change="onChange"
        />
        <span
            :class="[
                boxClass,
                modelValue ? fc.checkboxChecked : '',
            ]"
            aria-hidden="true"
        >
            <i
                v-if="modelValue"
                :class="fc.checkIcon"
            />
        </span>
    </span>
</template>
