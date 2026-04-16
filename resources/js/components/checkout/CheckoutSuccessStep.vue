<script setup>
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

const { checkoutState, goToCart } = useCheckoutFlowContext();
const { orderStore } = checkoutState;
</script>

<template>
    <div class="space-y-3 text-xs sm:text-sm text-slate-200">
        <p class="text-[11px] uppercase tracking-[0.18em] text-amber-300">
            Заказ оформлен
        </p>
        <p class="text-sm font-semibold text-slate-50">
            Спасибо, бро. Мы приняли заказ и скоро свяжемся для подтверждения.
        </p>
        <p class="text-xs text-slate-300">
            Номер заказа:
            <span class="font-mono text-amber-300">
                {{ orderStore.lastCreatedOrder?.id ?? "—" }}
            </span>
        </p>
        <div class="mt-2 flex justify-end">
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-full bg-amber-400 px-4 py-2 text-xs font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.7)] transition hover:bg-amber-300"
                @click="goToCart"
            >
                Вернуться к меню
            </button>
        </div>
    </div>
</template>
