<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { CHECKOUT_WAITER_LINES } from "../../features/checkout/checkoutWizardLabels";

const props = defineProps({
    /** @type {'cart'|'upsell'|'guest'|'fulfillment'|'confirm'|'success'} */
    group: {
        type: String,
        required: true,
    },
    /** Переопределение реплики официанта */
    line: {
        type: String,
        default: null,
    },
});

const s = useAppDesign().components.checkout.shared;
const { checkoutState } = useCheckoutFlowContext();

const waiterLine = computed(() => {
    if (props.line != null && String(props.line).trim() !== "") {
        return String(props.line).trim();
    }
    return CHECKOUT_WAITER_LINES[props.group] ?? null;
});

const stepMeta = computed(() => {
    const bag = checkoutState.checkoutStepMeta;
    if (!bag || typeof bag !== "object") return null;
    return bag[props.group] ?? null;
});

const showProgress = computed(() => {
    const meta = stepMeta.value;
    return (
        meta
        && Number(meta.n) >= 1
        && Number(meta.total) >= 1
    );
});
</script>

<template>
    <div :class="[s.flowBody, 'space-y-3']">
        <div
            v-if="showProgress || waiterLine"
            :class="s.storyHeader"
        >
            <p
                v-if="showProgress"
                :class="s.storyProgress"
            >
                Шаг {{ stepMeta.n }} из {{ stepMeta.total }}
            </p>
            <p
                v-if="waiterLine"
                :class="s.storyWaiterLine"
            >
                {{ waiterLine }}
            </p>
        </div>

        <slot />

        <div v-if="$slots.nav">
            <slot name="nav" />
        </div>
    </div>
</template>
