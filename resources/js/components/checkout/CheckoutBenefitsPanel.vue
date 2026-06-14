<script setup>
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useBenefitProgress } from "../../features/shoppingSession/useBenefitProgress";

const chk = useAppDesign().components.checkout;
const c = chk.cart;

const { checkoutState } = useCheckoutFlowContext();
const { hasCartItems } = checkoutState;

const {
    canShowBenefitsBanner,
    delivery,
    gift,
    complement,
    deliveryLabel,
    giftLabel,
    complementLabel,
    deliveryProgressPercent,
    giftProgressPercent,
    complementProgressPercent,
} = useBenefitProgress();
</script>

<template>
    <div
        v-if="hasCartItems && canShowBenefitsBanner"
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
            v-if="giftLabel"
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

        <div
            v-if="complementLabel"
            class="space-y-1"
        >
            <div class="flex items-center justify-between gap-2 text-xs text-app-muted">
                <span>Комплект</span>
                <span>{{ complementProgressPercent }}%</span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-app-accent-soft-bg">
                <div
                    class="h-full rounded-full bg-app-accent transition-all"
                    :style="{ width: `${complementProgressPercent}%` }"
                />
            </div>
            <p
                :class="complement.isReached ? 'text-sm text-app-accent' : 'text-sm text-app-muted'"
            >
                {{ complementLabel }}
            </p>
        </div>
    </div>
</template>
