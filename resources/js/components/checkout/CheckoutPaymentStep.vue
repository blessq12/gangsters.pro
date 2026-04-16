<script setup>
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

const {
    checkoutState,
    goToDelivery,
    goToConfirm,
    setPaymentMethod,
    setPaymentChangeFrom,
    setCustomerComment,
} = useCheckoutFlowContext();
const { orderStore, paymentStepError } = checkoutState;
</script>

<template>
    <div class="space-y-3 text-xs sm:text-sm text-slate-200">
        <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400">
            Шаг 2 из 3 — Оплата
        </p>

        <div class="space-y-2">
            <p class="text-xs font-semibold text-slate-100">
                Способ оплаты
            </p>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="method in ['cash', 'card', 'transfer']"
                    :key="method"
                    type="button"
                    class="rounded-full px-3 py-1 text-[11px] transition"
                    :class="
                        orderStore.paymentInfo.method === method
                            ? 'bg-amber-400 text-black shadow-[0_0_14px_rgba(251,191,36,0.7)]'
                            : 'bg-white/5 text-slate-200 hover:bg-white/10'
                    "
                    @click="setPaymentMethod(method)"
                >
                    {{
                        method === "cash"
                            ? "Наличными"
                            : method === "card"
                              ? "Банковская карта"
                              : "Перевод"
                    }}
                </button>
            </div>
        </div>

        <div
            v-if="orderStore.paymentInfo.method === 'cash'"
            class="space-y-1"
        >
            <p class="text-xs font-semibold text-slate-100">
                Сдача с
            </p>
            <input
                type="number"
                min="0"
                class="w-full rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-xs text-slate-100 placeholder-slate-500 outline-none focus:border-amber-400"
                placeholder="Например, 2000"
                :value="orderStore.paymentInfo.changeFrom ?? ''"
                @input="setPaymentChangeFrom($event.target.value)"
            />
        </div>

        <p
            v-if="paymentStepError"
            class="text-[11px] text-red-400"
        >
            {{ paymentStepError }}
        </p>

        <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-100">
                Комментарий к заказу
            </p>
            <textarea
                rows="2"
                class="w-full rounded-2xl border border-white/10 bg-black/40 px-3 py-2 text-xs text-slate-100 placeholder-slate-500 outline-none focus:border-amber-400"
                placeholder="Например: без лука, позвонить за 10 минут до доставки"
                :value="orderStore.customerComment"
                @input="setCustomerComment($event.target.value)"
            />
        </div>

        <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
            <button
                type="button"
                class="underline-offset-2 hover:underline"
                @click="goToDelivery"
            >
                Назад: доставка
            </button>
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-full bg-amber-400 px-3 py-1.5 text-[11px] font-semibold text-black shadow-[0_0_14px_rgba(251,191,36,0.7)] transition hover:bg-amber-300"
                @click="goToConfirm"
            >
                Далее: подтвердить
            </button>
        </div>
    </div>
</template>
