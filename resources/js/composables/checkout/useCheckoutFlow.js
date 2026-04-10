import { computed, onBeforeUnmount, ref, watch } from "vue";
import { useUserStore } from "../../stores/userStore";
import { useCartStore } from "../../stores/cartStore";
import { useOrderStore } from "../../stores/orderStore";
import { formatRuPhone } from "../../utils/phone/formatRuPhone";
import { validateRuPhoneForSubmit } from "../../validation/ruPhone";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";

export function useCheckoutFlow() {
    const userStore = useUserStore();
    const cartStore = useCartStore();
    const orderStore = useOrderStore();

    orderStore.initFromStorage();

    const cartItems = computed(() => cartStore.cartItems);
    const totalAmount = computed(() => cartStore.cartTotalAmount);
    const isAuthenticated = computed(
        () => !!userStore.token && !!userStore.profile.id,
    );

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
    let complimentaryPreviewTimer = null;

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
            !userStore.selectedAddress
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
        const ids = cartItems.value.map((item) => item.productId);
        ids.forEach((id) => cartStore.removeFromCart(id));
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
            !userStore.selectedAddress
        ) {
            return;
        }

        try {
            await orderStore.createOrder(userStore.selectedAddress, cartItems.value, {
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
            await userStore.addClientAddress({
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

    function scheduleComplimentaryPreview() {
        if (complimentaryPreviewTimer) {
            clearTimeout(complimentaryPreviewTimer);
        }

        complimentaryPreviewTimer = setTimeout(async () => {
            if (!cartItems.value.length) {
                orderStore.complimentaryPreviewItems = [];
                return;
            }

            try {
                await orderStore.fetchComplimentaryPreview(cartItems.value);
            } catch (e) {
                // Ошибка уже отражается в orderStore.error.complimentaryPreview.
            }
        }, 250);
    }

    watch(
        cartItems,
        () => {
            scheduleComplimentaryPreview();
        },
        { deep: true, immediate: true },
    );

    onBeforeUnmount(() => {
        if (complimentaryPreviewTimer) {
            clearTimeout(complimentaryPreviewTimer);
        }
    });

    return {
        userStore,
        cartStore,
        orderStore,
        cartItems,
        totalAmount,
        complimentaryPreviewItems: computed(
            () => orderStore.complimentaryPreviewItems,
        ),
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
