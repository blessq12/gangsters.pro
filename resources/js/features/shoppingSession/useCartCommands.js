import { useCheckoutPricingStore } from "../../stores/checkoutPricingStore";

export function useCartCommands() {
    const cartStore = useCheckoutPricingStore();

    return {
        cartStore,
        addProductToCart(product, qty = 1) {
            return cartStore.addToCart(product, qty);
        },
        incrementProductInCart(productId) {
            return cartStore.incrementCart(productId);
        },
        decrementProductInCart(productId) {
            return cartStore.decrementCart(productId);
        },
        removeProductFromCart(productId) {
            return cartStore.removeFromCart(productId);
        },
        clearCart() {
            return cartStore.clear();
        },
    };
}
