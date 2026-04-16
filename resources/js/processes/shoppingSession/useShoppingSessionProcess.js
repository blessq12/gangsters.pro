import { DOMAIN_EVENTS, subscribeDomainEvent } from "../../shared/domainEvents";
import { useCartCommands } from "../../features/shoppingSession/useCartCommands";

let processInitialized = false;
let cleanupHandlers = [];

export function useShoppingSessionProcess() {
    if (!processInitialized) {
        const cartCommands = useCartCommands();

        cleanupHandlers = [
            subscribeDomainEvent(DOMAIN_EVENTS.CART_ADD_REQUESTED, ({ product, qty }) => {
                cartCommands.addProductToCart(product, qty);
            }),
            subscribeDomainEvent(DOMAIN_EVENTS.CART_INCREMENT_REQUESTED, ({ productId }) => {
                cartCommands.incrementProductInCart(productId);
            }),
            subscribeDomainEvent(DOMAIN_EVENTS.CART_DECREMENT_REQUESTED, ({ productId }) => {
                cartCommands.decrementProductInCart(productId);
            }),
            subscribeDomainEvent(DOMAIN_EVENTS.CART_REMOVE_REQUESTED, ({ productId }) => {
                cartCommands.removeProductFromCart(productId);
            }),
        ];

        processInitialized = true;
    }

    return {
        dispose() {
            cleanupHandlers.forEach((cleanup) => cleanup());
            cleanupHandlers = [];
            processInitialized = false;
        },
    };
}
