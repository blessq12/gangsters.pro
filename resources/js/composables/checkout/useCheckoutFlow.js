import { computed, onBeforeUnmount, ref, watch } from "vue";
import { useUserStore } from "../../stores/userStore";
import { useCartStore } from "../../stores/cartStore";
import { useOrderStore } from "../../stores/orderStore";
import { formatRuPhone } from "../../utils/phone/formatRuPhone";

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

    const formatPrice = (value) =>
        new Intl.NumberFormat("ru-RU").format(Number(value) || 0);

    function formatPhone(raw) {
        return formatRuPhone(raw);
    }

    function handleStartCheckout() {
        if (!hasCartItems.value) return;
        if (!orderStore.deliveryInfo.method) {
            orderStore.setDeliveryInfo({ method: "courier" });
        }
        if (!orderStore.paymentInfo.method) {
            orderStore.setPaymentInfo({ method: "card" });
        }
        if (!isAuthenticated.value) {
            activeStep.value = "auth";
        } else {
            activeStep.value = "delivery";
        }
    }

    function handleAuthCompleted() {
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

        if (
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
        const ids = cartItems.value.map((item) => item.productId);
        ids.forEach((id) => cartStore.removeFromCart(id));
        goToSuccess();
    }

    async function handleConfirmOrder() {
        if (!hasCartItems.value) return;

        if (
            !orderStore.deliveryInfo.method ||
            (orderStore.deliveryInfo.method === "courier" &&
                !userStore.selectedAddress) ||
            !orderStore.paymentInfo.method
        ) {
            return;
        }
        try {
            const client = userStore.profile;
            await orderStore.createOrder(
                client,
                userStore.selectedAddress,
                cartItems.value,
            );
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
        newAddressForm,
        newAddressLoading,
        newAddressError,
        isNewAddressOpen,
        deliveryStepError,
        paymentStepError,
        formatPrice,
        formatPhone,
        handleStartCheckout,
        handleAuthCompleted,
        goToCart,
        goToDelivery,
        goToPayment,
        goToConfirm,
        handleConfirmOrder,
        handleCreateAddress,
    };
}
