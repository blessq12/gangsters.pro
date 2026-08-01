<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { CHECKOUT_NAV_LABELS } from "../../features/checkout/checkoutWizardLabels";
import { useCheckoutNavTotal } from "../../features/checkout/useCheckoutNavTotal";
import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import { useCatalogStore } from "../../stores/catalogStore";
import { useCheckoutStore } from "../../stores/checkoutStore";
import CheckoutSection from "./CheckoutSection.vue";
import CheckoutStepFrame from "./CheckoutStepFrame.vue";
import CheckoutStepNav from "./CheckoutStepNav.vue";

const chk = useAppDesign().components.checkout;
const c = chk.cart;
const s = chk.shared;

const { checkoutState, goToCart, goToUpsellNext } = useCheckoutFlowContext();
const { formatPrice } = checkoutState;
const { navTotalLabel } = useCheckoutNavTotal();

const catalogStore = useCatalogStore();
const checkoutStore = useCheckoutStore();
const cartCommands = useCartCommands();
const { accompanyingCategories } = storeToRefs(catalogStore);

const accompanyingSections = computed(() =>
    (accompanyingCategories.value || []).filter(
        (entry) => Array.isArray(entry?.products) && entry.products.length > 0,
    ),
);

const primaryLabel = computed(() => {
    const hasAny = accompanyingSections.value.some((entry) =>
        (entry.products || []).some((p) => paidQty(p.id) > 0),
    );
    return hasAny
        ? CHECKOUT_NAV_LABELS.upsellPrimary
        : CHECKOUT_NAV_LABELS.upsellSkip;
});

function paidQty(productId) {
    return checkoutStore.cartQuantityByProduct(productId);
}

function unitPriceRub(product) {
    const amount = product?.price?.amount ?? product?.price;
    return Number(amount) || 0;
}

function thumbUrl(product) {
    const fromImages = Array.isArray(product?.images) ? product.images[0] : null;
    const raw = fromImages ?? product?.imageUrl ?? product?.image_url ?? null;
    if (raw == null) return null;
    const url = String(raw).trim();
    return url !== "" ? url : null;
}

async function incrementProduct(product) {
    const id = product?.id;
    if (id == null) return;
    if (paidQty(id) <= 0) {
        await cartCommands.addProductToCart(product, 1);
        return;
    }
    await cartCommands.incrementProductInCart(id);
}

async function decrementProduct(productId) {
    if (productId == null) return;
    await cartCommands.decrementProductInCart(productId);
}
</script>

<template>
    <CheckoutStepFrame group="upsell">
        <CheckoutSection
            v-for="entry in accompanyingSections"
            :key="`accompanying:${entry.category?.id ?? entry.category?.slug}`"
        >
            <ul class="space-y-2">
                <li
                    v-for="product in entry.products"
                    :key="`accompanying-item:${product.id}`"
                    :class="s.offerCard"
                >
                    <img
                        v-if="thumbUrl(product)"
                        :src="thumbUrl(product)"
                        :alt="product.name || ''"
                        :class="c.drinkUpsellThumb"
                        loading="lazy"
                    >
                    <div
                        v-else
                        :class="c.drinkUpsellThumbPlaceholder"
                    >
                        —
                    </div>
                    <div class="min-w-0 flex-1">
                        <p :class="s.offerCardTitle">
                            {{ product.name || `Товар #${product.id}` }}
                        </p>
                        <p :class="s.offerCardMeta">
                            {{ formatPrice(unitPriceRub(product)) }} ₽
                        </p>
                    </div>
                    <div :class="c.qtyBar">
                        <button
                            type="button"
                            :class="c.qtyBtn"
                            :disabled="paidQty(product.id) <= 0"
                            @click="decrementProduct(product.id)"
                        >
                            –
                        </button>
                        <span :class="c.qtyLabel">
                            {{ paidQty(product.id) }}
                        </span>
                        <button
                            type="button"
                            :class="c.qtyBtn"
                            @click="incrementProduct(product)"
                        >
                            +
                        </button>
                    </div>
                </li>
            </ul>
        </CheckoutSection>

        <template #nav>
            <CheckoutStepNav
                :primary-label="primaryLabel"
                show-nav-total
                :total-label="navTotalLabel"
                @back="goToCart"
                @primary="goToUpsellNext"
            />
        </template>
    </CheckoutStepFrame>
</template>
