<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import CheckoutOrderPreview from "./CheckoutOrderPreview.vue";
import CheckoutSection from "./CheckoutSection.vue";

const chk = useAppDesign().components.checkout;
const c = chk.cart;

const {
    checkoutState,
    handleStartCheckout,
    openProfileDock,
} = useCheckoutFlowContext();

const cartStore = useCheckoutStore();
const cartCommands = useCartCommands();
const {
    cartItems,
    userItems: userCartItems,
} = storeToRefs(cartStore);

const {
    formatPrice,
    isAuthenticated,
} = checkoutState;

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
    <div class="space-y-3">
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

        <CheckoutOrderPreview variant="cart" />

        <div
            v-if="hasUserLines"
            :class="c.authActions"
        >
            <button
                type="button"
                :class="chk.shared.btnPrimaryMd"
                @click="handleStartCheckout"
            >
                {{ isAuthenticated ? "Далее" : "Оформить" }}
            </button>
            <button
                v-if="!isAuthenticated"
                type="button"
                :class="c.loginLink"
                @click="openProfileDock"
            >
                Войти
            </button>
        </div>
    </div>
</template>
