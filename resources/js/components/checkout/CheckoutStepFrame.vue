<script setup>
import { computed, unref } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import {
    CHECKOUT_WIZARD_GROUPS,
    resolveWizardStepMeta,
} from "../../features/checkout/checkoutWizardGroups";

const props = defineProps({
    /** @type {'cart'|'guest'|'delivery'|'payment'|'confirm'} */
    group: {
        type: String,
        required: true,
    },
});

const s = useAppDesign().components.checkout.shared;
const { checkoutState } = useCheckoutFlowContext();
const { isGuestCheckout } = checkoutState;

const headerLabel = computed(() => {
    if (props.group === "cart") {
        return CHECKOUT_WIZARD_GROUPS.cart;
    }

    const guestFlow = unref(isGuestCheckout);
    const meta = resolveWizardStepMeta(props.group, guestFlow);
    if (!meta) {
        return CHECKOUT_WIZARD_GROUPS[props.group] ?? "";
    }

    return `${meta.label} · ${meta.n}/${meta.total}`;
});
</script>

<template>
    <div :class="[s.flowBody, 'space-y-3']">
        <p :class="s.stepKicker">
            {{ headerLabel }}
        </p>
        <slot />
        <div
            v-if="$slots.nav"
            :class="s.navFooterRow"
        >
            <slot name="nav" />
        </div>
    </div>
</template>
