<script setup>
import { computed } from "vue";
import { useUserStore } from "../../stores/userStore";

const userStore = useUserStore();

const cartItems = computed(() => userStore.cartItems);
const totalAmount = computed(() => userStore.cartTotalAmount);

const formatPrice = (value) =>
    new Intl.NumberFormat("ru-RU").format(Number(value) || 0);
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
                    {{ userStore.cartTotalItems }} шт
                </div>
            </div>

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
                                @click="userStore.decrementCart(item.productId)"
                            >
                                –
                            </button>
                            <span class="px-2 font-semibold">
                                {{ item.qty }}
                            </span>
                            <button
                                type="button"
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-black/70 text-[14px]"
                                @click="userStore.incrementCart(item.productId)"
                            >
                                +
                            </button>
                        </div>

                        <button
                            type="button"
                            class="shrink-0 text-[11px] text-slate-400 transition-colors hover:text-red-400"
                            @click="userStore.removeFromCart(item.productId)"
                        >
                            Убрать
                        </button>
                    </div>
                </li>
            </ul>

            <div
                v-if="cartItems.length"
                class="mt-1 flex items-center justify-between text-xs sm:text-sm"
            >
                <span class="text-slate-300/85">Итого</span>
                <span class="font-semibold text-amber-300">
                    {{ formatPrice(totalAmount) }} ₽
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped></style>

