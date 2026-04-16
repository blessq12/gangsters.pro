import { useOrderStore } from "../../stores/orderStore";
import { useCartReadModel } from "../shoppingSession/useCartReadModel";
import { useClientReadModel } from "../client/useClientReadModel";
import { useClientAddressSelectionModel } from "../client/useClientAddressSelectionModel";

export function useOrderCommands() {
    const orderStore = useOrderStore();
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

        return orderStore.createOrder(selectedAddress, cartItems, {
            isGuest,
        });
    }

    return {
        // черновик заказа (используется checkout-процессом)
        setDeliveryInfo: orderStore.setDeliveryInfo,
        setPaymentInfo: orderStore.setPaymentInfo,
        setCustomerComment: orderStore.setCustomerComment,
        setGuestContact: orderStore.setGuestContact,
        patchDeliveryAddress: orderStore.patchDeliveryAddress,
        clearDraft: orderStore.clearDraft,

        // работа со списком заказов
        fetchOrders,

        // high-level команда оформления заказа из текущего checkout-контекста
        createOrderFromCheckout,
    };
}

