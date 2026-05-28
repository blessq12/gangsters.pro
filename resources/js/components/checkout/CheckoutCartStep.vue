<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useCartStore } from "../../stores/cartStore";
import { useUiStore } from "../../stores/uiStore";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";

const chk = useAppDesign().components.checkout;
const c = chk.cart;

const {
    checkoutState,
    handleStartCheckout,
    handleResumeCheckout,
    openProfileDock,
} = useCheckoutFlowContext();

const cartStore = useCartStore();
const uiStore = useUiStore();
const {
    cartItems,
    userItems: userCartItems,
    systemItems: systemCartItems,
    hasDeliveryPricing,
} = storeToRefs(cartStore);

const {
    totalAmount,
    itemsTotalAmount,
    deliveryFeeAmount,
    isDeliveryFree,
    formatPrice,
    isAuthenticated,
    promoState,
    deliveryBenefit,
    giftBenefit,
    canResumeCheckout,
    resumeCheckoutLabel,
} = checkoutState;

const isCartEmpty = computed(() => cartItems.value.length === 0);
const hasUserLines = computed(() => userCartItems.value.length > 0);
const hasSystemOnlyCart = computed(
    () => !isCartEmpty.value && !hasUserLines.value && systemCartItems.value.length > 0,
);

const giftPromotion = computed(() => {
    const state = promoState?.value ?? promoState;
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
function openGiftModal() {
    if (!isGiftEligible.value) {
        return;
    }
    emitDomainEvent(DOMAIN_EVENTS.BENEFIT_BANNER_CTA_CLICK, {
        source: "cart",
        cta: "choose_gift",
    });
    uiStore.openGiftSelectionModal({ source: "manual" });
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

function formatKopecksToRub(kopecks) {
    const rub = Number(kopecks || 0) / 100;
    return formatPrice(rub);
}
</script>

<template>
    <div>
        <div
            v-if="canResumeCheckout"
            :class="c.resumeBanner"
        >
            <div :class="c.resumeBannerRow">
                <p :class="c.resumeBannerText">
                    Есть незавершённое оформление — можно продолжить с того места, где остановился.
                </p>
                <button
                    type="button"
                    :class="chk.shared.btnPrimaryMd"
                    @click="handleResumeCheckout"
                >
                    {{ resumeCheckoutLabel }}
                </button>
            </div>
        </div>

        <div
            v-if="isCartEmpty"
            :class="c.emptyState"
        >
            Корзина пока пустая. Добавь пару вкусных позиций, и тут станет веселее.
        </div>

        <ul
            v-else-if="hasUserLines"
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

        <p
            v-else-if="hasSystemOnlyCart"
            :class="[chk.shared.introMuted, 'mb-2']"
        >
            В корзине только автодобавления по акции. Добавь блюда из меню, чтобы оформить заказ.
        </p>

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
                <span :class="c.systemLineName">
                    • {{ item.productSnapshot?.name || `Товар #${item.productId}` }}
                </span>
                <span :class="c.systemLineMeta">
                    {{ item.qty }} × {{ formatPrice(0) }} ₽
                </span>
            </li>
        </ul>

        <div
            v-if="!isCartEmpty"
            :class="c.totalsCard"
        >
            <div :class="c.totalsRow">
                <span :class="c.totalsLabelMuted">Товары</span>
                <span :class="c.totalsValue">{{ formatPrice(itemsTotalAmount) }} ₽</span>
            </div>
            <div
                v-if="hasDeliveryPricing"
                :class="c.totalsRow"
            >
                <span :class="c.totalsLabelMuted">Доставка</span>
                <span
                    v-if="isDeliveryFree"
                    :class="c.totalsValue"
                >
                    Бесплатно
                </span>
                <span
                    v-else
                    :class="c.totalsValue"
                >
                    {{ formatPrice(deliveryFeeAmount) }} ₽
                </span>
            </div>
            <div :class="c.totalsDivider">
                <span :class="c.totalsLabelStrong">Итого</span>
                <span :class="c.grandTotal">{{ formatPrice(totalAmount) }} ₽</span>
            </div>
        </div>

        <div
            v-if="!isCartEmpty"
            :class="[c.totalsCard, 'mt-3 space-y-2']"
        >
            <p class="text-xs text-app-muted">Прогресс выгод</p>
            <p
                v-if="deliveryBenefit?.isReached"
                class="text-sm text-app-accent"
            >
                Бесплатная доставка активна
            </p>
            <p
                v-else
                class="text-sm text-app-muted"
            >
                До бесплатной доставки осталось
                {{ formatKopecksToRub(deliveryBenefit?.remainingKopecks) }} ₽
            </p>
            <p
                v-if="giftBenefit?.isReached"
                class="text-sm text-app-accent"
            >
                Подарок доступен — выберите в корзине
            </p>
            <p
                v-else-if="giftBenefit?.isActive"
                class="text-sm text-app-muted"
            >
                До подарка осталось {{ formatKopecksToRub(giftBenefit?.remainingKopecks) }} ₽
            </p>
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
            v-if="hasUserLines"
            :class="c.authActions"
        >
            <button
                type="button"
                :class="chk.shared.btnPrimaryMd"
                @click="handleStartCheckout"
            >
                {{ isAuthenticated ? "Перейти к оформлению" : "Оформить заказ" }}
            </button>
            <button
                v-if="!isAuthenticated"
                type="button"
                :class="c.loginLink"
                @click="openProfileDock"
            >
                Войти в аккаунт
            </button>
        </div>

    </div>
</template>
