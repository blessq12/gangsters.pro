import { useCheckoutIntentStore } from "../../stores/checkoutIntentStore";
import { useOrderStore } from "../../stores/orderStore";
import { useCartReadModel } from "../shoppingSession/useCartReadModel";
import { useClientReadModel } from "../client/useClientReadModel";
import { useClientAddressSelectionModel } from "../client/useClientAddressSelectionModel";

export function useOrderCommands() {
    const orderStore = useOrderStore();
    const checkoutIntent = useCheckoutIntentStore();
    const cartReadModel = useCartReadModel();
    const clientReadModel = useClientReadModel();
    const addressSelection = useClientAddressSelectionModel();

    async function fetchOrders() {
        return orderStore.fetchOrders();
    }

    async function createOrderFromCheckout({ isGuest = false } = {}) {
        const cartItems = cartReadModel.items.value;

        const selectedAddress = isGuest
            ? null
            : addressSelection.selectedAddress.value;

        return orderStore.createOrder(selectedAddress, cartItems, checkoutIntent, {
            isGuest,
        });
    }

    return {
        setDeliveryInfo: (payload) => checkoutIntent.setDeliveryInfo(payload),
        setPaymentInfo: (payload) => checkoutIntent.setPaymentInfo(payload),
        setCustomerComment: (comment) => checkoutIntent.setCustomerComment(comment),
        setGuestContact: (payload) => checkoutIntent.setGuestContact(payload),
        patchDeliveryAddress: (partial) => checkoutIntent.patchDeliveryAddress(partial),
        clearIntentLocal: () => checkoutIntent.clearLocal(),
        flushIntentToServer: () => checkoutIntent.flushToServer(),
        setPromotionGift: (productId) => checkoutIntent.setPromotionGift(productId),

        fetchOrders,
        createOrderFromCheckout,
    };
}
