import { computed } from "vue";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { formatKopecksToRub } from "../../utils/moneyFormat";

function emptyMoneyBenefit() {
    return {
        isActive: false,
        isReached: false,
        remainingKopecks: 0,
        thresholdKopecks: null,
        currentKopecks: 0,
        isPreview: false,
    };
}

/**
 * Единая read-model сессии оформления: корзина, pricing, benefits.
 */
export function useCheckoutSession() {
    const session = useCheckoutStore();

    const items = computed(() => session.cartItems);
    const userItems = computed(() => session.userItems);
    const systemItems = computed(() => session.systemItems);
    const totalAmount = computed(() =>
        session.hasDeliveryPricing
            ? session.grandTotalWithDelivery
            : session.cartTotalAmount,
    );
    const hasCartItems = computed(() => userItems.value.length > 0);
    const promoState = computed(() => session.promoState);
    const deliveryPricing = computed(() => session.deliveryPricing);
    const itemsTotalAmount = computed(() => session.itemsTotalAmount);
    const deliveryFeeAmount = computed(() => session.deliveryFeeAmount);
    const grandTotalWithDelivery = computed(() => session.grandTotalWithDelivery);
    const isDeliveryFree = computed(() => session.isDeliveryFree);
    const hasDeliveryPricing = computed(() => session.hasDeliveryPricing);
    const benefitsProgress = computed(() => session.benefitsProgress);
    const hasBenefitsProgress = computed(() => session.hasBenefitsProgress);
    const totalItems = computed(() => session.cartTotalItems);
    const systemItemsCount = computed(() => session.cartSystemItemsCount);

    const delivery = computed(
        () => benefitsProgress.value?.delivery ?? emptyMoneyBenefit(),
    );
    const gift = computed(() => benefitsProgress.value?.gift ?? emptyMoneyBenefit());
    const hasActiveBenefits = computed(
        () => Boolean(delivery.value.isActive || gift.value.isActive),
    );
    const canShowBenefitsBanner = computed(
        () => totalItems.value > 0 && hasBenefitsProgress.value && hasActiveBenefits.value,
    );

    const deliveryProgressPercent = computed(() => {
        const threshold = Number(delivery.value.thresholdKopecks);
        const current = Number(delivery.value.currentKopecks);
        if (!Number.isFinite(threshold) || threshold <= 0) {
            return delivery.value.isReached ? 100 : 0;
        }
        return Math.min(100, Math.max(0, Math.round((current / threshold) * 100)));
    });

    const giftProgressPercent = computed(() => {
        const threshold = Number(gift.value.thresholdKopecks);
        const current = Number(gift.value.currentKopecks);
        if (!Number.isFinite(threshold) || threshold <= 0) {
            return gift.value.isReached ? 100 : 0;
        }
        return Math.min(100, Math.max(0, Math.round((current / threshold) * 100)));
    });

    const deliveryLabel = computed(() => {
        if (!delivery.value.isActive) {
            return null;
        }
        if (delivery.value.isReached) {
            return delivery.value.isPreview
                ? "Бесплатная доставка курьером"
                : "Бесплатная доставка";
        }
        const remaining = formatKopecksToRub(delivery.value.remainingKopecks);
        return `Ещё ${remaining} ₽ до бесплатной доставки`;
    });

    const giftLabel = computed(() => {
        if (!gift.value.isActive) {
            return null;
        }
        if (gift.value.isReached) {
            return "Подарок доступен";
        }
        const remaining = formatKopecksToRub(gift.value.remainingKopecks);
        return `Ещё ${remaining} ₽ до подарка`;
    });

    const benefitLines = computed(() =>
        [deliveryLabel.value, giftLabel.value].filter(Boolean),
    );

    function quantityByProduct(productId) {
        return session.cartQuantityByProduct(productId);
    }

    return {
        session,
        items,
        userItems,
        systemItems,
        totalAmount,
        userTotalAmount: computed(() => session.cartUserTotalAmount),
        systemTotalAmount: computed(() => session.cartSystemTotalAmount),
        promoState,
        deliveryPricing,
        itemsTotalAmount,
        deliveryFeeAmount,
        grandTotalWithDelivery,
        isDeliveryFree,
        hasDeliveryPricing,
        benefitsProgress,
        hasBenefitsProgress,
        totalItems,
        systemItemsCount,
        hasCartItems,
        quantityByProduct,
        delivery,
        gift,
        deliveryBenefit: delivery,
        giftBenefit: gift,
        hasActiveBenefits,
        canShowBenefitsBanner,
        deliveryProgressPercent,
        giftProgressPercent,
        deliveryLabel,
        giftLabel,
        benefitLines,
        formatKopecksToRub,
        cartItems: items,
        userCartItems: userItems,
        systemCartItems: systemItems,
        benefits: {
            delivery,
            gift,
            hasBenefitsProgress,
            hasActiveBenefits,
            canShowBenefitsBanner,
            deliveryProgressPercent,
            giftProgressPercent,
            deliveryLabel,
            giftLabel,
            benefitLines,
            formatKopecksToRub,
        },
    };
}
