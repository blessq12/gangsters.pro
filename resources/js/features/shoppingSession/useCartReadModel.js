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
        totalItems: computed(() => cartStore.cartTotalItems),
        systemItemsCount: computed(() => cartStore.cartSystemItemsCount),
        quantityByProduct(productId) {
            return cartStore.cartQuantityByProduct(productId);
        },
    };
}
