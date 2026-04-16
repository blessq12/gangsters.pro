<script setup>
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";

const {
    orderStore,
    cartItems,
    totalAmount,
    formatPrice,
    isAuthenticated,
    handleStartCheckout,
    handleContinueAsGuest,
} = useCheckoutFlowContext();

function decrementCart(productId) {
    emitDomainEvent(DOMAIN_EVENTS.CART_DECREMENT_REQUESTED, {
        productId,
        source: "checkout",
    });
}

function incrementCart(productId) {
    emitDomainEvent(DOMAIN_EVENTS.CART_INCREMENT_REQUESTED, {
        productId,
        source: "checkout",
    });
}

function removeFromCart(productId) {
    emitDomainEvent(DOMAIN_EVENTS.CART_REMOVE_REQUESTED, {
        productId,
        source: "checkout",
    });
}
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
                            @click="decrementCart(item.productId)"
                        >
                            –
                        </button>
                        <span class="px-2 font-semibold">
                            {{ item.qty }}
                        </span>
                        <button
                            type="button"
                            class="flex h-6 w-6 items-center justify-center rounded-full bg-black/70 text-[14px]"
                            @click="incrementCart(item.productId)"
                        >
                            +
                        </button>
                    </div>

                    <button
                        type="button"
                        class="shrink-0 text-[11px] text-slate-400 transition-colors hover:text-red-400"
                        @click="removeFromCart(item.productId)"
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

        <!-- complimentary preview удалён вместе с vertical Promotions -->

        <div
            v-if="cartItems.length"
            class="mt-3 flex flex-col gap-2"
        >
            <template v-if="isAuthenticated">
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center rounded-full bg-amber-400 px-4 py-2 text-xs font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.7)] transition hover:bg-amber-300"
                    @click="handleStartCheckout"
                >
                    Перейти к оформлению
                </button>
            </template>
            <template v-else>
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center rounded-full bg-amber-400 px-4 py-2 text-xs font-semibold text-black shadow-[0_0_18px_rgba(251,191,36,0.7)] transition hover:bg-amber-300"
                    @click="handleStartCheckout"
                >
                    Войти или зарегистрироваться
                </button>
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-medium text-slate-100 transition hover:bg-white/10"
                    @click="handleContinueAsGuest"
                >
                    Продолжить без регистрации
                </button>
            </template>
        </div>
    </div>
</template>
