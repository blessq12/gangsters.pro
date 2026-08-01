<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { CHECKOUT_STEP_HINTS } from "../../features/checkout/checkoutWizardLabels";

const props = defineProps({
    /** @type {'cart'|'guest'|'fulfillment'|'drinks'|'confirm'|'success'} */
    group: {
        type: String,
        required: true,
    },
    hint: {
        type: String,
        default: null,
    },
});

const s = useAppDesign().components.checkout.shared;

const stepHint = computed(() => {
    if (props.hint != null) {
        return props.hint;
    }
    return CHECKOUT_STEP_HINTS[props.group] ?? null;
});
</script>

<template>
    <div :class="[s.flowBody, 'space-y-3']">
        <p
            v-if="stepHint"
            :class="s.stepHint"
        >
            {{ stepHint }}
        </p>

        <slot />

        <div v-if="$slots.nav">
            <slot name="nav" />
        </div>
    </div>
</template>
