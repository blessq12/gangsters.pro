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
    id: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue"]);

const fc = useAppDesign().components.uiPrimitives.formControls;

const rootClass = computed(() =>
    props.size === "sm" ? fc.rootSm : fc.rootMd,
);

const boxClass = computed(() =>
    props.size === "sm" ? fc.checkboxSm : fc.checkbox,
);

const iconClass = computed(() =>
    props.size === "sm" ? fc.checkIconSm : fc.checkIcon,
);

function onChange(event) {
    emit("update:modelValue", event.target.checked);
}
</script>

<template>
    <span :class="rootClass">
        <input
            :id="id || undefined"
            type="checkbox"
            :class="fc.inputOverlay"
            :checked="modelValue"
            :disabled="disabled"
            @change="onChange"
        />
        <span
            :class="[fc.controlDecorLayer, boxClass, fc.checkboxCheckedPeer]"
            aria-hidden="true"
        >
            <i
                :class="iconClass"
                aria-hidden="true"
            />
        </span>
    </span>
</template>
