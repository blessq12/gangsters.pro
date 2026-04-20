<script setup>
import { computed, ref, watch } from "vue";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";

const { checkoutState, handleStartCheckout, handleContinueAsGuest } =
    useCheckoutFlowContext();
const {
    cartItems,
    userCartItems,
    systemCartItems,
    totalAmount,
    userTotalAmount,
    formatPrice,
    isAuthenticated,
    promoState,
    orderStore,
} = checkoutState;
const showGiftModal = ref(false);
const selectedGiftProductId = ref(null);
const giftApplying = ref(false);

const giftPromotion = computed(() => {
    const state = promoState?.value;
    if (!state || typeof state !== "object") {
        return null;
    }
    return state.gift_promotion && typeof state.gift_promotion === "object"
        ? state.gift_promotion
        : null;
});

const isGiftEligible = computed(() => giftPromotion.value?.eligible === true);
const hasGiftSelected = computed(() => Number(giftPromotion.value?.selected_product_id) > 0);
const giftCtaLabel = computed(() =>
    hasGiftSelected.value ? "Изменить подарок" : "Выбрать подарок",
);
const giftCandidates = computed(() => {
    const promo = giftPromotion.value;
    if (!promo) {
        return [];
    }

    const candidateItems = Array.isArray(promo.candidate_items) ? promo.candidate_items : [];
    if (candidateItems.length > 0) {
        return candidateItems
            .map((item) => ({
                id: Number(item?.id) || 0,
                name: item?.name ? String(item.name) : "",
                priceRub: Number(item?.price_rub) || 0,
                imageUrl: item?.image_url ? String(item.image_url) : null,
            }))
            .filter((item) => item.id > 0);
    }

    const ids = Array.isArray(promo.candidate_product_ids) ? promo.candidate_product_ids : [];
    return ids
        .map((id) => Number(id) || 0)
        .filter((id) => id > 0)
        .map((id) => ({
            id,
            name: `Товар #${id}`,
            priceRub: 0,
            imageUrl: null,
        }));
});
const canApplyGiftSelection = computed(() =>
    giftCandidates.value.some((item) => item.id === Number(selectedGiftProductId.value)),
);

watch(
    giftPromotion,
    (promo) => {
        const selectedId = Number(promo?.selected_product_id) || null;
        selectedGiftProductId.value = selectedId;
    },
    { immediate: true },
);

function openGiftModal() {
    if (!isGiftEligible.value) {
        return;
    }
    const selectedId = Number(giftPromotion.value?.selected_product_id) || null;
    selectedGiftProductId.value = selectedId;
    showGiftModal.value = true;
}

async function applyGiftSelection() {
    if (!canApplyGiftSelection.value || giftApplying.value) {
        return;
    }

    giftApplying.value = true;
    try {
        await orderStore.setPromotionGift(selectedGiftProductId.value);
        showGiftModal.value = false;
    } finally {
        giftApplying.value = false;
    }
}

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

function unitPriceRub(item) {
    const kopecks = Number(item?.pricing?.finalUnitPriceKopecks);
    if (Number.isFinite(kopecks)) return kopecks / 100;
    return Number(item?.productSnapshot?.price) || 0;
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
            v-else-if="userCartItems.length"
            class="space-y-2 text-xs sm:text-sm text-slate-200"
        >
            <li class="px-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                Вы добавили
            </li>
            <li
                v-for="item in userCartItems"
                :key="item.lineKey"
                class="flex items-center justify-between gap-3 rounded-2xl bg-[rgba(255,255,255,0.03)] px-3 py-2"
            >
                <div class="min-w-0">
                    <p class="truncate font-medium text-slate-100">
                        {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                    </p>
                    <p class="mt-0.5 text-[11px] text-slate-400">
                        {{ formatPrice(unitPriceRub(item)) }} ₽ за шт
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

        <ul
            v-if="systemCartItems.length"
            class="mt-2 rounded-xl border border-amber-400/25 bg-amber-400/8 px-2.5 py-2 text-[11px] text-slate-200"
        >
            <li class="px-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                Комплект и автодобавления
            </li>
            <li
                v-for="item in systemCartItems"
                :key="item.lineKey"
                class="mt-1 flex items-center justify-between gap-2 rounded-lg px-1 py-0.5"
            >
                <span class="min-w-0 truncate text-slate-100">
                    • {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                </span>
                <span class="shrink-0 text-slate-300">
                    {{ item.qty }} × {{ formatPrice(0) }} ₽
                </span>
            </li>
        </ul>

        <div
            v-if="cartItems.length"
            class="mt-3 space-y-1 rounded-2xl border border-white/10 bg-[rgba(255,255,255,0.02)] px-3 py-2 text-xs sm:text-sm"
        >
            <div class="flex items-center justify-between">
                <span class="text-slate-300/85">Товары</span>
                <span class="text-slate-100">{{ formatPrice(userTotalAmount) }} ₽</span>
            </div>
            <div class="flex items-center justify-between border-t border-white/10 pt-1">
                <span class="font-medium text-slate-300/90">Итого</span>
                <span class="font-semibold text-amber-300">{{ formatPrice(totalAmount) }} ₽</span>
            </div>
        </div>

        <div
            v-if="isGiftEligible && giftCandidates.length"
            class="mt-3 rounded-2xl border border-amber-300/25 bg-amber-400/10 px-3 py-2 text-xs sm:text-sm"
        >
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="font-semibold text-amber-200">Подарок к заказу доступен</p>
                    <p
                        v-if="hasGiftSelected"
                        class="mt-0.5 truncate text-[11px] text-slate-300"
                    >
                        Выбран: {{
                            giftCandidates.find((item) => item.id === Number(giftPromotion?.selected_product_id))
                                ?.name || `Товар #${giftPromotion?.selected_product_id}`
                        }}
                    </p>
                </div>
                <button
                    type="button"
                    class="shrink-0 rounded-full border border-amber-300/60 bg-black/40 px-3 py-1 text-[11px] font-medium text-amber-200 transition hover:bg-black/60"
                    @click="openGiftModal"
                >
                    {{ giftCtaLabel }}
                </button>
            </div>
        </div>

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

        <BaseModal v-model="showGiftModal">
            <template #header>Выбери подарок</template>

            <div class="space-y-2">
                <label
                    v-for="item in giftCandidates"
                    :key="item.id"
                    class="flex cursor-pointer items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-3 py-2 transition hover:border-amber-300/40"
                >
                    <input
                        v-model="selectedGiftProductId"
                        :value="item.id"
                        type="radio"
                        name="gift-candidate"
                        class="h-4 w-4 accent-amber-300"
                    />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-100">
                            {{ item.name || `Товар #${item.id}` }}
                        </p>
                        <p class="text-xs text-slate-400">
                            Цена в меню: {{ formatPrice(item.priceRub) }} ₽, в корзине — 0 ₽
                        </p>
                    </div>
                    <img
                        v-if="item.imageUrl"
                        :src="item.imageUrl"
                        :alt="item.name || `Товар #${item.id}`"
                        class="h-10 w-10 rounded-lg object-cover"
                    />
                </label>
            </div>

            <template #footer>
                <div class="flex justify-end">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-full bg-amber-400 px-4 py-2 text-xs font-semibold text-black transition hover:bg-amber-300 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="!canApplyGiftSelection || giftApplying"
                        @click="applyGiftSelection"
                    >
                        {{ giftApplying ? "Применяем..." : "Применить" }}
                    </button>
                </div>
            </template>
        </BaseModal>
    </div>
</template>
