<script setup>
import { useCheckoutFlow } from "../../composables/checkout/useCheckoutFlow";
import { provideCheckoutFlow } from "../../composables/checkout/checkoutFlowContext";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";

const flow = useCheckoutFlow();
provideCheckoutFlow(flow);

const { cartStore, activeStep } = flow;

function formatPrice(value) {
    return formatMoneyRublesRu(value);
}
</script>

<template>
    <div
        class="rounded-3xl border border-amber-400/30 bg-[rgba(0,0,0,0.88)] px-4 sm:px-6 lg:px-8 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
    >
        <div class="flex flex-col gap-3 lg:gap-4">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm sm:text-base font-semibold text-slate-50">
                    Корзина
                </p>
                <div class="flex items-center gap-2">
                    <div
                        v-if="cartStore.cartSystemItemsCount > 0"
                        class="hidden sm:flex h-8 items-center rounded-full border border-amber-400/35 bg-amber-400/10 px-3 text-[11px] text-amber-200"
                    >
                        +{{ cartStore.cartSystemItemsCount }} авто
                    </div>
                    <div
                        class="flex h-8 items-center rounded-full bg-black/70 px-3 text-xs text-slate-200"
                    >
                    {{ cartStore.cartTotalItems }} шт
                    </div>
                </div>
            </div>

            <p
                v-if="cartStore.cartSystemItemsCount > 0"
                class="mt-1 text-[11px] text-slate-400"
            >
                Автодобавления: {{ cartStore.cartSystemItemsCount }} шт,
                {{ formatPrice(cartStore.cartSystemTotalAmount) }} ₽
            </p>

            <CheckoutCartStep v-if="activeStep === 'cart'" />
            <CheckoutAuthStep v-else-if="activeStep === 'auth'" />
            <CheckoutDeliveryStep v-else-if="activeStep === 'delivery'" />
            <CheckoutPaymentStep v-else-if="activeStep === 'payment'" />
            <CheckoutConfirmStep v-else-if="activeStep === 'confirm'" />
            <CheckoutSuccessStep v-else-if="activeStep === 'success'" />
        </div>
    </div>
</template>

<style scoped></style>
