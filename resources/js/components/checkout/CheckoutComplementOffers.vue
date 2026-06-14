<script setup>
import { ref } from "vue";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useComplementWizardOffers } from "../../features/checkout/useComplementWizardOffers";

const chk = useAppDesign().components.checkout;
const c = chk.cart;

const { checkoutState } = useCheckoutFlowContext();
const { formatPrice } = checkoutState;

const { offers, hasComplementOffers, addComplementToUserCart } = useComplementWizardOffers();

const addingProductId = ref(null);

async function handleAdd(productId) {
    if (addingProductId.value != null) {
        return;
    }

    addingProductId.value = productId;
    try {
        await addComplementToUserCart(productId);
    } finally {
        addingProductId.value = null;
    }
}
</script>

<template>
    <ul
        v-if="hasComplementOffers"
        :class="c.systemList"
    >
        <li :class="chk.shared.subsectionKickerSm">
            Комплект к заказу
        </li>
        <li
            v-for="offer in offers"
            :key="offer.productId"
            :class="c.userLineItem"
        >
            <div class="min-w-0">
                <p :class="c.lineTitle">
                    {{ offer.name }}
                </p>
                <p :class="c.lineSub">
                    <span v-if="offer.freeQty > 0">
                        По акции: {{ offer.freeQty }} × бесплатно
                    </span>
                    <span v-if="offer.freeQty > 0 && offer.priceRub > 0">
                        ·
                    </span>
                    <span v-if="offer.priceRub > 0">
                        Докупить: {{ formatPrice(offer.priceRub) }} ₽ за шт
                    </span>
                </p>
                <p
                    v-if="offer.userQty > 0"
                    :class="c.lineSub"
                >
                    В основной корзине: {{ offer.userQty }} шт
                </p>
            </div>

            <div :class="c.lineActions">
                <div :class="c.qtyBar">
                    <span :class="c.qtyLabel">
                        {{ offer.userQty }}
                    </span>
                    <button
                        type="button"
                        :class="c.qtyBtn"
                        :disabled="addingProductId === offer.productId"
                        @click="handleAdd(offer.productId)"
                    >
                        +
                    </button>
                </div>
            </div>
        </li>
    </ul>
</template>
