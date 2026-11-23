<script setup>
import { storeToRefs } from "pinia";
import { computed } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCatalogStore } from "../../modules/catalog/store";
import { useCheckoutFlowContext } from "../../modules/checkout/application/flowContext";
import { CHECKOUT_NAV_LABELS } from "../../modules/checkout/application/session";
import { resolveCartLineImageUrl } from "../../modules/checkout/domain/cartLineImage.js";
import { useCheckoutStore } from "../../modules/checkout/store";
import CheckoutComplementOffers from "./CheckoutComplementOffers.vue";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutStepNav from "./CheckoutStepNav.vue";

const chk = useAppDesign().components.checkout;
const c = chk.cart;

const { checkoutState, handleStartCheckout } = useCheckoutFlowContext();

const catalogStore = useCatalogStore();
const cartStore = useCheckoutStore();
const { userItems: userCartItems } = storeToRefs(cartStore);
const { complementProducts, accompanyingCategories } = storeToRefs(catalogStore);

const { formatPrice } = checkoutState;

const complementProductIds = computed(() => {
    const ids = new Set();
    for (const product of complementProducts.value || []) {
        if (product?.id != null) {
            ids.add(Number(product.id));
        }
    }
    return ids;
});

/** Основные блюда меню (без комплектных). */
const menuFoodItems = computed(() =>
    (userCartItems.value || []).filter(
        (item) => !complementProductIds.value.has(Number(item.productId)),
    ),
);

/** Комплектные, добавленные как платные ordinary-линии. */
const paidComplementItems = computed(() =>
    (userCartItems.value || []).filter((item) =>
        complementProductIds.value.has(Number(item.productId)),
    ),
);

/** Все платные линии корзины: блюда + докупленные комплектные. */
const displayCartItems = computed(() => [
    ...menuFoodItems.value,
    ...paidComplementItems.value,
]);

const isCartEmpty = computed(() => (userCartItems.value || []).length === 0);
const hasMenuFood = computed(() => menuFoodItems.value.length > 0);
const canStartCheckout = computed(() => hasMenuFood.value);

const catalogImageSource = computed(() => ({
    flatProducts: catalogStore.flatProducts,
    accompanyingCategories: accompanyingCategories.value,
    complementProducts: complementProducts.value,
}));

function lineImageUrl(item) {
    return resolveCartLineImageUrl(item, catalogImageSource.value);
}

function decrementCart(productId) {
    void cartStore.decrementCart(productId);
}

function incrementCart(productId) {
    void cartStore.incrementCart(productId);
}

function removeFromCart(productId) {
    void cartStore.removeFromCart(productId);
}

function unitPriceRub(item) {
    const kopecks = Number(item?.pricing?.finalUnitPriceKopecks);
    if (Number.isFinite(kopecks)) {
        return kopecks / 100;
    }
    return Number(item?.productSnapshot?.price) || 0;
}
</script>

<template>
    <CheckoutStepFrame group="cart">
        <div v-if="isCartEmpty" :class="c.emptyState">Корзина пуста</div>

        <CheckoutSection v-else-if="displayCartItems.length > 0" title="">
            <ul :class="c.userList">
                <li
                    v-for="item in displayCartItems"
                    :key="item.lineKey"
                    :class="c.userLineItem"
                >
                    <div :class="c.lineThumb" aria-hidden="true">
                        <img
                            v-if="lineImageUrl(item)"
                            :src="lineImageUrl(item)"
                            :alt="item.productSnapshot?.name || ''"
                            :class="c.lineThumbImg"
                            loading="lazy"
                        >
                        <span v-else :class="c.lineThumbPlaceholder">—</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p :class="c.lineTitle">
                            {{
                                item.productSnapshot?.name ||
                                `Товар #${item.productId}`
                            }}
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

        <p v-else-if="!isCartEmpty" :class="chk.shared.introMuted">
            Добавь блюда из меню
        </p>

        <CheckoutComplementOffers />

        <template v-if="canStartCheckout" #nav>
            <CheckoutStepNav
                :show-back="false"
                :primary-label="CHECKOUT_NAV_LABELS.cartPrimary"
                @primary="handleStartCheckout"
            />
        </template>
    </CheckoutStepFrame>
</template>
