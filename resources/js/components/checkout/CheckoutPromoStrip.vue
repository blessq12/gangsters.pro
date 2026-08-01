<script setup>
import { computed, ref } from "vue";
import { storeToRefs } from "pinia";
import { useAppDesign } from "../../design/useAppDesign";
import { useCheckoutFlowContext } from "../../composables/checkout/checkoutFlowContext";
import { useOrderPreview } from "../../features/checkout/useOrderPreview";
import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import { useCatalogStore } from "../../stores/catalogStore";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useUiStore } from "../../stores/uiStore";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";
import CheckoutSection from "./CheckoutSection.vue";

const props = defineProps({
    /** @type {'cart'|'confirm'} */
    variant: {
        type: String,
        required: true,
        validator: (value) => ["cart", "confirm"].includes(value),
    },
});

const chk = useAppDesign().components.checkout;
const c = chk.cart;
const p = chk.promo;
const s = chk.shared;

const { checkoutState } = useCheckoutFlowContext();
const { formatPrice } = checkoutState;
const uiStore = useUiStore();
const catalogStore = useCatalogStore();
const checkoutStore = useCheckoutStore();
const cartCommands = useCartCommands();

const { complementProducts } = storeToRefs(catalogStore);

const {
    complementLines,
    autoLines,
    hasComplementLines,
    hasRollsInCart,
    hasAutoLines,
    complement,
    complementProgressPercent,
    complementLabel,
    showComplementProgress,
    isGiftEligible,
    hasGiftSelected,
    giftCtaLabel,
    giftCandidates,
    selectedGiftName,
} = useOrderPreview();

const cartExpanded = ref(false);

const hasComplementProducts = computed(
    () => (complementProducts.value?.length ?? 0) > 0,
);

const showCartToggle = computed(
    () =>
        props.variant === "cart"
        && (showComplementProgress.value
            || (hasRollsInCart.value && hasComplementLines.value)
            || hasAutoLines.value
            || hasComplementProducts.value),
);

const showGiftCta = computed(
    () =>
        props.variant === "confirm"
        && isGiftEligible.value
        && giftCandidates.value.length > 0,
);

function paidQty(productId) {
    return checkoutStore.cartQuantityByProduct(productId);
}

function unitPriceRub(product) {
    const amount = product?.price?.amount ?? product?.price;
    return Number(amount) || 0;
}

async function incrementPaidComplement(product) {
    const id = product?.id;
    if (id == null) return;

    if (paidQty(id) <= 0) {
        await cartCommands.addProductToCart(product, 1);
        return;
    }

    await cartCommands.incrementProductInCart(id);
}

async function decrementPaidComplement(productId) {
    if (productId == null) return;
    await cartCommands.decrementProductInCart(productId);
}

function openGiftModal() {
    if (!isGiftEligible.value) {
        return;
    }

    emitDomainEvent(DOMAIN_EVENTS.BENEFIT_BANNER_CTA_CLICK, {
        source: "confirm",
        cta: "choose_gift",
    });
    uiStore.openGiftSelectionModal({ source: "manual" });
}
</script>

<template>
    <div
        v-if="showCartToggle"
        :class="p.wrap"
    >
        <button
            type="button"
            :class="s.expandRowBtn"
            @click="cartExpanded = !cartExpanded"
        >
            <span>Акции и дополнения</span>
            <span :class="s.expandRowChevronMuted">
                {{ cartExpanded ? "Скрыть" : "Показать" }}
            </span>
        </button>

        <Transition name="checkout-fade">
            <div
                v-if="cartExpanded"
                :class="p.cartBody"
            >
                <div
                    v-if="showComplementProgress"
                    :class="c.complementProgressCard"
                >
                    <div class="flex items-center justify-between gap-2 text-xs text-app-muted">
                        <span>Комплект к роллам</span>
                        <span>{{ complementProgressPercent }}%</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-app-accent-soft-bg">
                        <div
                            class="h-full rounded-full bg-app-accent transition-all"
                            :style="{ width: `${complementProgressPercent}%` }"
                        />
                    </div>
                    <p
                        :class="complement.isReached ? 'text-sm text-app-accent' : 'text-sm text-app-muted'"
                    >
                        {{ complementLabel }}
                    </p>
                </div>

                <ul
                    v-if="hasRollsInCart && hasComplementLines"
                    :class="c.systemList"
                >
                    <li :class="s.subsectionKickerSm">
                        Комплект
                    </li>
                    <li
                        v-for="line in complementLines"
                        :key="`complement:${line.productId}`"
                        :class="c.systemLine"
                    >
                        <span :class="c.systemLineName">
                            {{ line.name }}
                        </span>
                        <span :class="c.systemLineMeta">
                            {{ line.quantity }} ×
                            {{ line.isFree ? "Бесплатно" : `${formatPrice(line.priceRubles)} ₽` }}
                        </span>
                    </li>
                </ul>

                <ul
                    v-if="hasComplementProducts"
                    :class="[c.systemList, 'space-y-2']"
                >
                    <li :class="s.subsectionKickerSm">
                        Докупить
                    </li>
                    <li
                        v-for="product in complementProducts"
                        :key="`paid-complement:${product.id}`"
                        :class="[c.userLineItem, '!mt-0']"
                    >
                        <div class="min-w-0">
                            <p :class="c.lineTitle">
                                {{ product.name || `Товар #${product.id}` }}
                            </p>
                            <p :class="c.lineSub">
                                {{ formatPrice(unitPriceRub(product)) }} ₽
                            </p>
                        </div>

                        <div :class="c.qtyBar">
                            <button
                                type="button"
                                :class="c.qtyBtn"
                                :disabled="paidQty(product.id) <= 0"
                                @click="decrementPaidComplement(product.id)"
                            >
                                –
                            </button>
                            <span :class="c.qtyLabel">
                                {{ paidQty(product.id) }}
                            </span>
                            <button
                                type="button"
                                :class="c.qtyBtn"
                                @click="incrementPaidComplement(product)"
                            >
                                +
                            </button>
                        </div>
                    </li>
                </ul>

                <CheckoutSection
                    v-if="hasAutoLines"
                    title="Автодобавления"
                >
                    <ul :class="c.systemList">
                        <li
                            v-for="line in autoLines"
                            :key="`auto:${line.productId}`"
                            :class="c.systemLine"
                        >
                            <span :class="c.systemLineName">
                                {{ line.name }}
                            </span>
                            <span :class="c.systemLineMeta">
                                {{ line.quantity }} × {{ formatPrice(0) }} ₽
                            </span>
                        </li>
                    </ul>
                </CheckoutSection>
            </div>
        </Transition>
    </div>

    <CheckoutSection
        v-if="showGiftCta"
        title="Подарок"
        variant="inset"
    >
        <div :class="[c.giftCard, '!mt-0']">
            <div :class="c.giftRow">
                <div class="min-w-0">
                    <p :class="c.giftTitle">
                        {{ hasGiftSelected ? selectedGiftName : "Выбери подарок" }}
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
    </CheckoutSection>
</template>

<style scoped>
.checkout-fade-enter-active,
.checkout-fade-leave-active {
    transition: opacity 0.2s ease;
}
.checkout-fade-enter-from,
.checkout-fade-leave-to {
    opacity: 0;
}
</style>
