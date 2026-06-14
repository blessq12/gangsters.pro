<script setup>
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useBenefitProgress } from "../../features/shoppingSession/useBenefitProgress";
import { useCheckoutBenefitVisibility } from "../../features/checkout/useCheckoutBenefitVisibility";

const chk = useAppDesign().components.checkout;
const c = chk.cart;

const { checkoutState } = useCheckoutFlowContext();
const { hasCartItems } = checkoutState;

const {
    hasBenefitsProgress,
    delivery,
    gift,
    deliveryLabel,
    giftLabel,
    deliveryProgressPercent,
    giftProgressPercent,
} = useBenefitProgress();

const { showGiftProgress } = useCheckoutBenefitVisibility();

const canShowPanel = computed(() => {
    if (!hasCartItems.value || !hasBenefitsProgress.value) {
        return false;
    }

    const deliveryVisible = delivery.value.isActive;
    const giftVisible = showGiftProgress.value && gift.value.isActive;

    return deliveryVisible || giftVisible;
});
</script>

<template>
    <div
        v-if="canShowPanel"
        :class="[c.totalsCard, 'mt-3 space-y-3']"
    >
        <p class="text-xs text-app-muted">
            Прогресс выгод
        </p>

        <div
            v-if="deliveryLabel"
            class="space-y-1"
        >
            <div class="flex items-center justify-between gap-2 text-xs text-app-muted">
                <span>Доставка</span>
                <span>{{ deliveryProgressPercent }}%</span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-app-accent-soft-bg">
                <div
                    class="h-full rounded-full bg-app-accent transition-all"
                    :style="{ width: `${deliveryProgressPercent}%` }"
                />
            </div>
            <p
                :class="delivery.isReached ? 'text-sm text-app-accent' : 'text-sm text-app-muted'"
            >
                {{ deliveryLabel }}
            </p>
        </div>

        <div
            v-if="showGiftProgress && giftLabel"
            class="space-y-1"
        >
            <div class="flex items-center justify-between gap-2 text-xs text-app-muted">
                <span>Подарок</span>
                <span>{{ giftProgressPercent }}%</span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-app-accent-soft-bg">
                <div
                    class="h-full rounded-full bg-app-accent transition-all"
                    :style="{ width: `${giftProgressPercent}%` }"
                />
            </div>
            <p
                :class="gift.isReached ? 'text-sm text-app-accent' : 'text-sm text-app-muted'"
            >
                {{ giftLabel }}
            </p>
        </div>
    </div>
</template>
