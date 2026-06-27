<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { CHECKOUT_NAV_LABELS } from "../../features/checkout/checkoutWizardLabels";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import CheckoutPromoStrip from "./CheckoutPromoStrip.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutStepNav from "./CheckoutStepNav.vue";
import CheckoutTotalsBlock from "./CheckoutTotalsBlock.vue";

const chk = useAppDesign().components.checkout;
const c = chk.cart;

const { checkoutState, handleStartCheckout } = useCheckoutFlowContext();

const cartStore = useCheckoutStore();
const cartCommands = useCartCommands();
const {
    cartItems,
    userItems: userCartItems,
} = storeToRefs(cartStore);

const { formatPrice } = checkoutState;

const isCartEmpty = computed(() => cartItems.value.length === 0);
const hasUserLines = computed(() => userCartItems.value.length > 0);

function decrementCart(productId) {
    void cartCommands.decrementProductInCart(productId);
}

function incrementCart(productId) {
    void cartCommands.incrementProductInCart(productId);
}

function removeFromCart(productId) {
    void cartCommands.removeProductFromCart(productId);
}

function unitPriceRub(item) {
    const kopecks = Number(item?.pricing?.finalUnitPriceKopecks);
    if (Number.isFinite(kopecks)) return kopecks / 100;
    return Number(item?.productSnapshot?.price) || 0;
}
</script>

<template>
    <CheckoutStepFrame group="cart">
        <div
            v-if="isCartEmpty"
            :class="c.emptyState"
        >
            Корзина пуста
        </div>

        <CheckoutSection
            v-else-if="hasUserLines"
            title="Товары"
        >
            <ul :class="c.userList">
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
                            {{ formatPrice(unitPriceRub(item)) }} ₽
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
        </CheckoutSection>

        <p
            v-else-if="!isCartEmpty"
            :class="chk.shared.introMuted"
        >
            Добавь блюда из меню
        </p>

        <CheckoutPromoStrip variant="cart" />
        <CheckoutTotalsBlock depth="items" />

        <template
            v-if="hasUserLines"
            #nav
        >
            <CheckoutStepNav
                :show-back="false"
                :primary-label="CHECKOUT_NAV_LABELS.cartPrimary"
                @primary="handleStartCheckout"
            />
        </template>
    </CheckoutStepFrame>
</template>
