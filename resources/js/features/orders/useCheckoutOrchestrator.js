import { useClientCommands } from "../client/useClientCommands";
import { useClientAddressSelectionModel } from "../client/useClientAddressSelectionModel";
import { useOrderCommands } from "./useOrderCommands";
import { useCheckoutValidation } from "../../composables/checkout/useCheckoutValidation";

export function useCheckoutOrchestrator(checkoutState) {
    const clientCommands = useClientCommands();
    const addressSelection = useClientAddressSelectionModel();
    const orderCommands = useOrderCommands();
    const { validateDeliveryStep, validatePaymentStep, canConfirmOrder } =
        useCheckoutValidation(checkoutState);

    const {
        orderStore,
        cartItems,
        isAuthenticated,
        activeStep,
        isGuestCheckout,
        hasCartItems,
        newAddressForm,
        newAddressLoading,
        newAddressError,
    } = checkoutState;

    function ensureCheckoutDefaults() {
        if (!orderStore.deliveryInfo.method) {
            orderStore.setDeliveryInfo({ method: "courier" });
        }
        if (!orderStore.paymentInfo.method) {
            orderStore.setPaymentInfo({ method: "card" });
        }
    }

    function handleStartCheckout() {
        if (!hasCartItems.value) return;
        ensureCheckoutDefaults();
        isGuestCheckout.value = false;
        activeStep.value = isAuthenticated.value ? "delivery" : "auth";
    }

    function handleContinueAsGuest() {
        if (!hasCartItems.value) return;
        ensureCheckoutDefaults();
        isGuestCheckout.value = true;
        activeStep.value = "delivery";
    }

    function handleAuthCompleted() {
        isGuestCheckout.value = false;
        activeStep.value = hasCartItems.value ? "delivery" : "cart";
    }

    function goToCart() {
        activeStep.value = "cart";
    }

    function goToDelivery() {
        activeStep.value = hasCartItems.value ? "delivery" : "cart";
    }

    function goToPayment() {
        const selectedAddress = addressSelection.selectedAddress.value;
        if (!validateDeliveryStep(selectedAddress)) {
            return;
        }
        activeStep.value = "payment";
    }

    function goToConfirm() {
        if (!validatePaymentStep()) {
            return;
        }
        activeStep.value = "confirm";
    }

    function goToSuccess() {
        activeStep.value = "success";
    }

    function setDeliveryMethod(method) {
        orderCommands.setDeliveryInfo({ method });
    }

    function setDeliveryComment(comment) {
        orderCommands.setDeliveryInfo({ comment });
    }

    function setGuestContact(payload) {
        orderCommands.setGuestContact(payload);
    }

    function patchDeliveryAddress(partial) {
        orderCommands.patchDeliveryAddress(partial);
    }

    function selectAddress(addressId) {
        clientCommands.selectAddress(addressId);
    }

    function setPaymentMethod(method) {
        orderCommands.setPaymentInfo({ method });
    }

    function setPaymentChangeFrom(changeFrom) {
        orderCommands.setPaymentInfo({ changeFrom });
    }

    function setCustomerComment(comment) {
        orderCommands.setCustomerComment(comment);
    }

    async function handleConfirmOrder() {
        const selectedAddress = addressSelection.selectedAddress.value;
        if (!canConfirmOrder(selectedAddress, hasCartItems)) {
            return;
        }

        try {
            await orderCommands.createOrderFromCheckout({
                isGuest: isGuestCheckout.value,
            });
            goToSuccess();
        } catch (e) {
            // ошибка уже сохранена в orderStore.error.create
        }
    }

    async function handleCreateAddress() {
        newAddressError.value = "";

        if (!newAddressForm.value.street || !newAddressForm.value.house) {
            newAddressError.value = "Укажи улицу и дом";
            return;
        }

        newAddressLoading.value = true;

        try {
            await clientCommands.addAddress({
                title: newAddressForm.value.title || null,
                street: newAddressForm.value.street,
                house: newAddressForm.value.house,
                entrance: newAddressForm.value.entrance || null,
                apartment: newAddressForm.value.apartment || null,
                comment: newAddressForm.value.comment || null,
                make_default: newAddressForm.value.make_default,
            });

            newAddressForm.value = {
                title: "",
                street: "",
                house: "",
                entrance: "",
                apartment: "",
                comment: "",
                make_default: true,
            };
        } catch (e) {
            console.error(e);
            newAddressError.value =
                e?.response?.data?.message ||
                "Не удалось сохранить адрес. Попробуй ещё раз.";
        } finally {
            newAddressLoading.value = false;
        }
    }

    return {
        orderStore,
        cartItems,
        isAuthenticated,
        hasCartItems,
        handleStartCheckout,
        handleContinueAsGuest,
        handleAuthCompleted,
        goToCart,
        goToDelivery,
        goToPayment,
        goToConfirm,
        goToSuccess,
        setDeliveryMethod,
        setDeliveryComment,
        setGuestContact,
        patchDeliveryAddress,
        selectAddress,
        setPaymentMethod,
        setPaymentChangeFrom,
        setCustomerComment,
        handleConfirmOrder,
        handleCreateAddress,
    };
}
