<script setup>
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";

const {
    orderStore,
    cartStore,
    cartItems,
    complimentaryPreviewItems,
    totalAmount,
    formatPrice,
    handleStartCheckout,
} = useCheckoutFlowContext();
</script>

<template>
    <div>
        <div
            v-if="!cartItems.length"
            class="rounded-2xl bg-[rgba(255,255,255,0.03)] px-4 py-5 text-sm text-slate-300"
        >
            Корзина пока пустая. Добавь пару вкусных позиций, и тут станет веселее.
        </div>

        <ul
            v-else
            class="space-y-2 text-xs sm:text-sm text-slate-200"
        >
            <li
                v-for="item in cartItems"
                :key="item.productId"
                class="flex items-center justify-between gap-3 rounded-2xl bg-[rgba(255,255,255,0.03)] px-3 py-2"
            >
                <div class="min-w-0">
                    <p class="truncate font-medium text-slate-100">
                        {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                    </p>
                    <p class="mt-0.5 text-[11px] text-slate-400">
                        {{ formatPrice(item.productSnapshot?.price) }} ₽ за шт
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="inline-flex items-center justify-between rounded-full border border-amber-400/60 bg-black/70 px-2 py-1 text-xs text-slate-50"
                    >
                        <button
                            type="button"
                            class="flex h-6 w-6 items-center justify-center rounded-full bg-black/70 text-[14px]"
                            @click="cartStore.decrementCart(item.productId)"
                        >
                            –
                        </button>
                        <span class="px-2 font-semibold">
                            {{ item.qty }}
                        </span>
                        <button
                            type="button"
                            class="flex h-6 w-6 items-center justify-center rounded-full bg-black/70 text-[14px]"
                            @click="cartStore.incrementCart(item.productId)"
                        >
                            +
                        </button>
                    </div>

                    <button
                        type="button"
                        class="shrink-0 text-[11px] text-slate-400 transition-colors hover:text-red-400"
                        @click="cartStore.removeFromCart(item.productId)"
                    >
                        Убрать
                    </button>
                </div>
            </li>
        </ul>

        <div
            v-if="cartItems.length"
            class="mt-3 flex items-center justify-between text-xs sm:text-sm"
        >
            <span class="text-slate-300/85">Итого</span>
            <span class="font-semibold text-amber-300">
                {{ formatPrice(totalAmount) }} ₽
            </span>
        </div>

        <div
            v-if="complimentaryPreviewItems.length"
            class="mt-3 rounded-2xl border border-emerald-400/30 bg-emerald-950/20 px-3 py-2"
        >
            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-300">
                Добавится бесплатно
            </p>
            <ul class="mt-2 space-y-1 text-xs text-emerald-100">
                <li
                    v-for="item in complimentaryPreviewItems"
                    :key="`complimentary-${item.product_id}-${item.rule_id}`"
                    class="flex items-center justify-between gap-2"
                >
                    <span class="truncate">{{ item.name }}</span>
                    <span class="shrink-0">{{ item.quantity }} шт · 0 ₽</span>
                </li>
            </ul>
        </div>

        <div
            v-if="orderStore.error.complimentaryPreview"
            class="mt-3 rounded-2xl border border-red-500/40 bg-red-950/40 px-3 py-2 text-[11px] text-red-200"
        >
            {{ orderStore.error.complimentaryPreview }}
        </div>

        <div
            v-if="cartItems.length"
            class="mt-3"
        >
            <button
                type="button"
                class="inline-flex w-full items-center justify-center rounded-full bg-amber-400 px-4 py-2 text-xs font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.7)] transition hover:bg-amber-300"
                @click="handleStartCheckout"
            >
                Перейти к оформлению
            </button>
        </div>
    </div>
</template>
