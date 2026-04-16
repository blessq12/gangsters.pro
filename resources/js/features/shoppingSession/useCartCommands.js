import { useCartStore } from "../../stores/cartStore";

export function useCartCommands() {
    const cartStore = useCartStore();

    return {
        cartStore,
        addProductToCart(product, qty = 1) {
            cartStore.addToCart(product, qty);
        },
        incrementProductInCart(productId) {
            cartStore.incrementCart(productId);
        },
        decrementProductInCart(productId) {
            cartStore.decrementCart(productId);
        },
        removeProductFromCart(productId) {
            cartStore.removeFromCart(productId);
        },
        clearCart() {
            cartStore.clear();
        },
    };
}
