<script setup>
import { useCheckoutFlow } from "../../composables/checkout/useCheckoutFlow";
import { provideCheckoutFlow } from "../../composables/checkout/checkoutFlowContext";

const flow = useCheckoutFlow();
provideCheckoutFlow(flow);

const { cartStore, activeStep } = flow;
</script>

<template>
    <div
        class="rounded-3xl border border-amber-400/30 bg-[rgba(0,0,0,0.88)] px-4 sm:px-6 lg:px-8 py-4 shadow-[0_0_26px_rgba(0,0,0,0.85)] backdrop-blur"
    >
        <div class="flex flex-col gap-3">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm sm:text-base font-semibold text-slate-50">
                    Корзина
                </p>
                <div
                    class="flex h-8 items-center rounded-full bg-black/70 px-3 text-xs text-slate-200"
                >
                    {{ cartStore.cartTotalItems }} шт
                </div>
            </div>

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
