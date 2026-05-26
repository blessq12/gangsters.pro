<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: null,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    name: {
        type: String,
        default: "",
    },
    id: {
        type: String,
        default: "",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue"]);

const fc = useAppDesign().components.uiPrimitives.formControls;

const checked = computed(() => props.modelValue === props.value);

function onChange() {
    emit("update:modelValue", props.value);
}
</script>

<template>
    <span :class="fc.rootMd">
        <input
            :id="id"
            type="radio"
            :class="fc.inputOverlay"
            :name="name"
            :value="value"
            :checked="checked"
            :disabled="disabled"
            @change="onChange"
        />
        <span
            :class="[
                fc.controlDecorLayer,
                fc.radioDecor,
                fc.radioCheckedPeer,
            ]"
            aria-hidden="true"
        >
            <span
                :class="fc.radioDot"
                aria-hidden="true"
            />
        </span>
    </span>
</template>
