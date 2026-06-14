import { computed } from "vue";
import { useCheckoutPricingStore } from "../../stores/checkoutPricingStore";

export function useCartReadModel() {
    const cartStore = useCheckoutPricingStore();

    return {
        items: computed(() => cartStore.cartItems),
        userItems: computed(() => cartStore.userItems),
        systemItems: computed(() => cartStore.systemItems),
        totalAmount: computed(() => cartStore.cartTotalAmount),
        userTotalAmount: computed(() => cartStore.cartUserTotalAmount),
        systemTotalAmount: computed(() => cartStore.cartSystemTotalAmount),
        promoState: computed(() => cartStore.promoState),
        deliveryPricing: computed(() => cartStore.deliveryPricing),
        itemsTotalAmount: computed(() => cartStore.itemsTotalAmount),
        deliveryFeeAmount: computed(() => cartStore.deliveryFeeAmount),
        grandTotalWithDelivery: computed(() => cartStore.grandTotalWithDelivery),
        isDeliveryFree: computed(() => cartStore.isDeliveryFree),
        hasDeliveryPricing: computed(() => cartStore.hasDeliveryPricing),
        benefitsProgress: computed(() => cartStore.benefitsProgress),
        hasBenefitsProgress: computed(() => cartStore.hasBenefitsProgress),
        totalItems: computed(() => cartStore.cartTotalItems),
        systemItemsCount: computed(() => cartStore.cartSystemItemsCount),
        quantityByProduct(productId) {
            return cartStore.cartQuantityByProduct(productId);
        },
    };
}
