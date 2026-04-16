import { computed } from "vue";
import { useCartStore } from "../../stores/cartStore";

export function useCartReadModel() {
    const cartStore = useCartStore();

    return {
        items: computed(() => cartStore.cartItems),
        totalAmount: computed(() => cartStore.cartTotalAmount),
        totalItems: computed(() => cartStore.cartTotalItems),
        quantityByProduct(productId) {
            return cartStore.cartQuantityByProduct(productId);
        },
    };
}
