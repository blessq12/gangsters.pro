import { computed } from "vue";
import { useCartStore } from "../../stores/cartStore";

export function useCartReadModel() {
    const cartStore = useCartStore();

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
