<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { wizardNonComplementSystemItems } from "../../features/checkout/normalizeCheckoutCart";
import { useCartStore } from "../../stores/cartStore";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";
import CheckoutBenefitsPanel from "./CheckoutBenefitsPanel.vue";
import CheckoutComplementOffers from "./CheckoutComplementOffers.vue";
import CheckoutTotalsSummary from "./CheckoutTotalsSummary.vue";

const chk = useAppDesign().components.checkout;
const c = chk.cart;

const {
    checkoutState,
    handleStartCheckout,
    handleResumeCheckout,
    openProfileDock,
} = useCheckoutFlowContext();

const cartStore = useCartStore();
const {
    cartItems,
    userItems: userCartItems,
    systemItems: systemCartItems,
} = storeToRefs(cartStore);

const {
    formatPrice,
    isAuthenticated,
    canResumeCheckout,
    resumeCheckoutLabel,
} = checkoutState;

const isCartEmpty = computed(() => cartItems.value.length === 0);
const hasUserLines = computed(() => userCartItems.value.length > 0);
const wizardSystemItems = computed(() =>
    wizardNonComplementSystemItems(systemCartItems.value),
);
const hasSystemOnlyCart = computed(
    () => !isCartEmpty.value && !hasUserLines.value && wizardSystemItems.value.length > 0,
);

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

        <CheckoutComplementOffers />

        <ul
            v-if="wizardSystemItems.length"
            :class="c.systemList"
        >
            <li :class="chk.shared.subsectionKickerSm">
                Автодобавления
            </li>
            <li
                v-for="item in wizardSystemItems"
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

        <CheckoutTotalsSummary v-if="!isCartEmpty" />

        <CheckoutBenefitsPanel />

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
