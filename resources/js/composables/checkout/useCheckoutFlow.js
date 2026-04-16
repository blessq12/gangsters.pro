import { computed, ref } from "vue";
import { useOrderStore } from "../../stores/orderStore";
import { useClientCommands } from "../../features/client/useClientCommands";
import { useClientAddressSelectionModel } from "../../features/client/useClientAddressSelectionModel";
import { useClientReadModel } from "../../features/client/useClientReadModel";
import { useCartCommands } from "../../features/shoppingSession/useCartCommands";
import { useCartReadModel } from "../../features/shoppingSession/useCartReadModel";
import { formatRuPhone } from "../../utils/phone/formatRuPhone";
import { validateRuPhoneForSubmit } from "../../validation/ruPhone";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";

export function useCheckoutFlow() {
    const clientCommands = useClientCommands();
    const clientReadModel = useClientReadModel();
    const orderStore = useOrderStore();
    const addressSelection = useClientAddressSelectionModel();
    const cartCommands = useCartCommands();
    const cartReadModel = useCartReadModel();

    orderStore.initFromStorage();

    const cartItems = computed(() => cartReadModel.items.value);
    const totalAmount = computed(() => cartReadModel.totalAmount.value);
    const isAuthenticated = computed(() => clientReadModel.isAuthenticated.value);

    const activeStep = ref("cart"); // cart | auth | delivery | payment | confirm | success
    const authTab = ref("login"); // login | register
    const isGuestCheckout = ref(false);

    const newAddressForm = ref({
        title: "",
        street: "",
        house: "",
        entrance: "",
        apartment: "",
        comment: "",
        make_default: true,
    });
    const newAddressLoading = ref(false);
    const newAddressError = ref("");
    const isNewAddressOpen = ref(false);
    const deliveryStepError = ref("");
    const paymentStepError = ref("");

    const hasCartItems = computed(() => cartItems.value.length > 0);

    const formatPrice = (value) => formatMoneyRublesRu(value);

    function formatPhone(raw) {
        return formatRuPhone(raw);
    }

    function ensureCheckoutDefaults() {
        if (!orderStore.deliveryInfo.method) {
            orderStore.setDeliveryInfo({ method: "courier" });
        }
        if (!orderStore.paymentInfo.method) {
            orderStore.setPaymentInfo({ method: "card" });
        }
    }

    /** Авторизован: сразу на доставку */
    function handleStartCheckout() {
        if (!hasCartItems.value) return;
        ensureCheckoutDefaults();
        isGuestCheckout.value = false;
        if (!isAuthenticated.value) {
            activeStep.value = "auth";
        } else {
            activeStep.value = "delivery";
        }
    }

    function handleContinueAsGuest() {
        if (!hasCartItems.value) return;
        ensureCheckoutDefaults();
        isGuestCheckout.value = true;
        activeStep.value = "delivery";
    }

    function handleAuthCompleted() {
        isGuestCheckout.value = false;
        if (!hasCartItems.value) {
            activeStep.value = "cart";
            return;
        }
        activeStep.value = "delivery";
    }

    function goToCart() {
        activeStep.value = "cart";
    }

    function goToDelivery() {
        if (!hasCartItems.value) {
            activeStep.value = "cart";
            return;
        }
        activeStep.value = "delivery";
    }

    function goToPayment() {
        deliveryStepError.value = "";

        if (!orderStore.deliveryInfo.method) {
            deliveryStepError.value = "Выбери способ доставки.";
            return;
        }

        if (isGuestCheckout.value) {
            const gc = orderStore.guestContact;
            if (!String(gc?.name || "").trim()) {
                deliveryStepError.value = "Укажи имя для связи.";
                return;
            }
            const phoneCheck = validateRuPhoneForSubmit(gc?.phone);
            if (!phoneCheck.ok) {
                deliveryStepError.value = phoneCheck.message;
                return;
            }
            if (orderStore.deliveryInfo.method === "courier") {
                const a = orderStore.deliveryInfo.address;
                if (!String(a?.street || "").trim() || !String(a?.house || "").trim()) {
                    deliveryStepError.value = "Укажи улицу и дом для курьера.";
                    return;
                }
            }
        } else if (
            orderStore.deliveryInfo.method === "courier" &&
            !addressSelection.selectedAddress.value
        ) {
            deliveryStepError.value = "Выбери адрес доставки или добавь новый.";
            return;
        }

        activeStep.value = "payment";
    }

    function goToConfirm() {
        paymentStepError.value = "";

        if (!orderStore.paymentInfo.method) {
            paymentStepError.value = "Выбери способ оплаты.";
            return;
        }

        activeStep.value = "confirm";
    }

    function goToSuccess() {
        activeStep.value = "success";
    }

    function handlePlaceOrderSuccess() {
        isGuestCheckout.value = false;
        goToSuccess();
    }

    async function handleConfirmOrder() {
        if (!hasCartItems.value) return;

        if (!orderStore.deliveryInfo.method || !orderStore.paymentInfo.method) {
            return;
        }

        if (isGuestCheckout.value) {
            const gc = orderStore.guestContact;
            if (!String(gc?.name || "").trim()) {
                return;
            }
            if (!validateRuPhoneForSubmit(gc?.phone).ok) {
                return;
            }
            if (
                orderStore.deliveryInfo.method === "courier" &&
                (!String(orderStore.deliveryInfo.address?.street || "").trim() ||
                    !String(orderStore.deliveryInfo.address?.house || "").trim())
            ) {
                return;
            }
        } else if (
            orderStore.deliveryInfo.method === "courier" &&
            !addressSelection.selectedAddress.value
        ) {
            return;
        }

        try {
            await orderStore.createOrder(addressSelection.selectedAddress.value, cartItems.value, {
                isGuest: isGuestCheckout.value,
            });
            handlePlaceOrderSuccess();
        } catch (e) {
            // ошибка уже в orderStore.error.create
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
        userStore: {
            get profile() {
                return clientReadModel.profile.value;
            },
            get token() {
                return clientReadModel.token.value;
            },
            get addresses() {
                return clientReadModel.addresses.value;
            },
            get selectedAddress() {
                return clientReadModel.selectedAddress.value;
            },
            get selectedAddressId() {
                return clientReadModel.selectedAddressId.value;
            },
            clearAuth: clientCommands.clearAuth,
            addClientAddress: clientCommands.addAddress,
            selectAddress: clientCommands.selectAddress,
        },
        cartStore: cartCommands.cartStore,
        orderStore,
        cartItems,
        totalAmount,
        isAuthenticated,
        hasCartItems,
        activeStep,
        authTab,
        isGuestCheckout,
        newAddressForm,
        newAddressLoading,
        newAddressError,
        isNewAddressOpen,
        deliveryStepError,
        paymentStepError,
        formatPrice,
        formatPhone,
        handleStartCheckout,
        handleContinueAsGuest,
        handleAuthCompleted,
        goToCart,
        goToDelivery,
        goToPayment,
        goToConfirm,
        handleConfirmOrder,
        handleCreateAddress,
    };
}
