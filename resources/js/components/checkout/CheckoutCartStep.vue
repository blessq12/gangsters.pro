<script setup>
import { computed, ref, watch } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";

const chk = useAppDesign().components.checkout;
const c = chk.cart;

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
            :class="c.emptyState"
        >
            Корзина пока пустая. Добавь пару вкусных позиций, и тут станет веселее.
        </div>

        <ul
            v-else-if="userCartItems.length"
            :class="c.userList"
        >
            <li :class="chk.shared.subsectionKickerSm">
                Вы добавили
            </li>
            <li
                v-for="item in userCartItems"
                :key="item.lineKey"
                :class="c.userLineItem"
            >
                <div class="min-w-0">
                    <p :class="c.lineTitle">
                        {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                    </p>
                    <p :class="c.lineSub">
                        {{ formatPrice(unitPriceRub(item)) }} ₽ за шт
                    </p>
                </div>

                <div :class="c.lineActions">
                    <div :class="c.qtyBar">
                        <button
                            type="button"
                            :class="c.qtyBtn"
                            @click="decrementCart(item.productId)"
                        >
                            –
                        </button>
                        <span :class="c.qtyLabel">
                            {{ item.qty }}
                        </span>
                        <button
                            type="button"
                            :class="c.qtyBtn"
                            @click="incrementCart(item.productId)"
                        >
                            +
                        </button>
                    </div>

                    <button
                        type="button"
                        :class="c.removeLink"
                        @click="removeFromCart(item.productId)"
                    >
                        Убрать
                    </button>
                </div>
            </li>
        </ul>

        <ul
            v-if="systemCartItems.length"
            :class="c.systemList"
        >
            <li :class="chk.shared.subsectionKickerSm">
                Комплект и автодобавления
            </li>
            <li
                v-for="item in systemCartItems"
                :key="item.lineKey"
                :class="c.systemLine"
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
            :class="c.totalsCard"
        >
            <div :class="c.totalsRow">
                <span :class="c.totalsLabelMuted">Товары</span>
                <span :class="c.totalsValue">{{ formatPrice(userTotalAmount) }} ₽</span>
            </div>
            <div :class="c.totalsDivider">
                <span :class="c.totalsLabelStrong">Итого</span>
                <span :class="c.grandTotal">{{ formatPrice(totalAmount) }} ₽</span>
            </div>
        </div>

        <div
            v-if="isGiftEligible && giftCandidates.length"
            :class="c.giftCard"
        >
            <div :class="c.giftRow">
                <div class="min-w-0">
                    <p :class="c.giftTitle">Подарок к заказу доступен</p>
                    <p
                        v-if="hasGiftSelected"
                        :class="c.giftSelectedHint"
                    >
                        Выбран: {{
                            giftCandidates.find((item) => item.id === Number(giftPromotion?.selected_product_id))
                                ?.name || `Товар #${giftPromotion?.selected_product_id}`
                        }}
                    </p>
                </div>
                <button
                    type="button"
                    :class="c.giftCta"
                    @click="openGiftModal"
                >
                    {{ giftCtaLabel }}
                </button>
            </div>
        </div>

        <div
            v-if="cartItems.length"
            :class="c.authActions"
        >
            <template v-if="isAuthenticated">
                <button
                    type="button"
                    :class="chk.shared.btnPrimaryMd"
                    @click="handleStartCheckout"
                >
                    Перейти к оформлению
                </button>
            </template>
            <template v-else>
                <button
                    type="button"
                    :class="chk.shared.btnPrimaryMd"
                    @click="handleStartCheckout"
                >
                    Войти или зарегистрироваться
                </button>
                <button
                    type="button"
                    :class="chk.shared.btnSecondaryOutline"
                    @click="handleContinueAsGuest"
                >
                    Продолжить без регистрации
                </button>
            </template>
        </div>

        <BaseModal v-model="showGiftModal">
            <template #header>Выбери подарок</template>

            <div :class="c.giftModalList">
                <label
                    v-for="item in giftCandidates"
                    :key="item.id"
                    :class="c.giftRadioLabel"
                >
                    <input
                        v-model="selectedGiftProductId"
                        :value="item.id"
                        type="radio"
                        name="gift-candidate"
                        :class="c.giftRadioInput"
                    />
                    <div :class="c.giftRadioBody">
                        <p :class="c.giftRadioTitle">
                            {{ item.name || `Товар #${item.id}` }}
                        </p>
                        <p :class="c.giftRadioPrice">
                            Цена в меню: {{ formatPrice(item.priceRub) }} ₽, в корзине — 0 ₽
                        </p>
                    </div>
                    <img
                        v-if="item.imageUrl"
                        :src="item.imageUrl"
                        :alt="item.name || `Товар #${item.id}`"
                        :class="c.giftThumb"
                    />
                </label>
            </div>

            <template #footer>
                <div :class="c.giftFooterRow">
                    <button
                        type="button"
                        :class="c.giftApplyBtn"
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
