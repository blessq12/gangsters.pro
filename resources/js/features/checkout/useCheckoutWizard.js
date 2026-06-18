import { computed, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";
import { formatRuPhone } from "../../utils/phone/formatRuPhone";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";
import { isGuestContactComplete } from "./useCheckoutGuestStep";
import { resolveGiftSelectionRequired } from "./giftSelectionGate";

export function useCheckoutWizard({
    checkoutIntent,
    orderStore,
    userStore,
    uiStore,
    clientReadModel,
    cartView,
    guestStep,
    deliveryStep,
    paymentStep,
    isGuestCheckout,
}) {
    const {
        hasCartItems,
    } = cartView;

    const activeStep = ref("cart");
    const confirmLoading = ref(false);
    const confirmError = ref(null);
    const lastCreatedOrder = ref(null);

    const isAuthenticated = computed(() => clientReadModel.isAuthenticated.value);

    const checkoutStepMeta = computed(() => {
        const guestFlow = isGuestCheckout.value;
        const total = guestFlow ? 4 : 3;

        if (guestFlow) {
            return {
                guest: { n: 1, total },
                delivery: { n: 2, total },
                payment: { n: 3, total },
                confirm: { n: 4, total },
            };
        }

        return {
            guest: null,
            delivery: { n: 1, total },
            payment: { n: 2, total },
            confirm: { n: 3, total },
        };
    });

    const formatPrice = (value) => formatMoneyRublesRu(value);

    function formatPhone(raw) {
        return formatRuPhone(raw);
    }

    function ensureCheckoutDefaults() {
        deliveryStep.ensureDeliveryDefaults();
        paymentStep.ensurePaymentDefaults();
    }

    function beginGuestCheckout() {
        if (!hasCartItems.value) return;
        ensureCheckoutDefaults();
        isGuestCheckout.value = true;
        activeStep.value = "guest";
    }

    function beginAuthenticatedCheckout() {
        if (!hasCartItems.value) return;
        ensureCheckoutDefaults();
        isGuestCheckout.value = false;
        activeStep.value = "delivery";
        deliveryStep.ensureAuthAddressUi();
        void checkoutIntent.flushClientToServer({
            clientId: userStore.profile.id,
        });
    }

    watch(
        () => [checkoutIntent.checkoutId, checkoutIntent.status],
        ([checkoutId, status]) => {
            if (
                checkoutId
                && status === "draft"
                && activeStep.value === "success"
            ) {
                activeStep.value = "cart";
                isGuestCheckout.value = false;
            }
        },
    );

    watch(isAuthenticated, (authed) => {
        if (authed && activeStep.value === "guest") {
            beginAuthenticatedCheckout();
        }
    });

    const {
        orderPreview,
        promoState,
        wizardMissingBlocks,
        cartItems,
        wizardCanConfirm,
    } = storeToRefs(checkoutIntent);

    const giftSelectionRequired = computed(() =>
        resolveGiftSelectionRequired({
            giftCta: orderPreview.value?.giftCta,
            promoState: promoState.value,
            wizardMissingBlocks: wizardMissingBlocks.value,
            cartItems: cartItems.value,
            giftSummary: orderPreview.value?.giftSummary,
        }),
    );

    const canConfirmOrder = computed(
        () =>
            hasCartItems.value
            && wizardCanConfirm.value
            && !giftSelectionRequired.value,
    );

    watch(activeStep, (step) => {
        uiStore.setCheckoutWizardStep(step);
        if (step !== "confirm") {
            uiStore.closeGiftSelectionModal({ dismissAuto: false });
        }
        if (step === "delivery") {
            deliveryStep.ensureAuthAddressUi();
        }
        if (step === "confirm" && giftSelectionRequired.value) {
            uiStore.openGiftSelectionModal({ source: "auto" });
        }
    }, { immediate: true });

    watch(giftSelectionRequired, (required, wasRequired) => {
        if (required && wasRequired === false && activeStep.value === "confirm") {
            uiStore.openGiftSelectionModal({ source: "auto" });
        }
    });

    function handleStartCheckout() {
        if (!hasCartItems.value) return;
        if (isAuthenticated.value) {
            beginAuthenticatedCheckout();
        } else {
            beginGuestCheckout();
        }
    }

    function handleContinueAsGuest() {
        beginGuestCheckout();
    }

    function openProfileDock() {
        if (uiStore.dockActiveId !== "profile") {
            uiStore.setDockActive("profile");
        }
    }

    function goToCart() {
        activeStep.value = "cart";
    }

    function goToGuest() {
        activeStep.value = hasCartItems.value && isGuestCheckout.value ? "guest" : "cart";
    }

    function goToDelivery() {
        if (!hasCartItems.value) {
            activeStep.value = "cart";
            return;
        }
        if (isGuestCheckout.value) {
            activeStep.value = isGuestContactComplete(checkoutIntent.guestContact)
                ? "delivery"
                : "guest";
            return;
        }
        activeStep.value = "delivery";
    }

    async function goToGuestNext() {
        if (!guestStep.validateGuestStep()) {
            return;
        }
        await checkoutIntent.flushClientToServer({ isGuest: true });
        activeStep.value = "delivery";
    }

    async function goToPayment() {
        const selectedAddress = deliveryStep.addressSelection.selectedAddress.value;
        if (!deliveryStep.validateDeliveryStep(selectedAddress)) {
            return;
        }

        try {
            await deliveryStep.flushDeliveryPreview();
        } catch (e) {
            deliveryStep.deliveryFieldErrors.setFormError(
                e?.response?.data?.message || "Не удалось сохранить адрес доставки.",
            );
            return;
        }

        activeStep.value = "payment";
    }

    async function goToConfirm() {
        if (!paymentStep.validatePaymentStep()) {
            return;
        }
        await checkoutIntent.flushPaymentToServer();
        activeStep.value = "confirm";
    }

    function goToSuccess() {
        isGuestCheckout.value = false;
        activeStep.value = "success";
    }

    async function handleConfirmOrder() {
        if (giftSelectionRequired.value) {
            confirmError.value = "Выбери подарок, чтобы подтвердить заказ.";
            uiStore.openGiftSelectionModal({ source: "auto" });
            return;
        }

        if (!canConfirmOrder.value) {
            confirmError.value = "Заполни все шаги оформления.";
            return;
        }

        confirmLoading.value = true;
        confirmError.value = null;

        try {
            const confirmed = await checkoutIntent.confirmCheckout();
            lastCreatedOrder.value = confirmed.order ?? null;
            emitDomainEvent(DOMAIN_EVENTS.ORDER_CREATED, {
                order: lastCreatedOrder.value,
            });
            goToSuccess();
        } catch (e) {
            confirmError.value =
                e?.response?.data?.message ||
                checkoutIntent.error ||
                "Не удалось подтвердить оформление.";
        } finally {
            confirmLoading.value = false;
        }
    }

    return {
        activeStep,
        isGuestCheckout,
        isAuthenticated,
        checkoutStepMeta,
        formatPrice,
        formatPhone,
        handleStartCheckout,
        handleContinueAsGuest,
        openProfileDock,
        goToCart,
        goToGuest,
        goToDelivery,
        goToGuestNext,
        goToPayment,
        goToConfirm,
        goToSuccess,
        handleConfirmOrder,
        confirmLoading,
        confirmError,
        lastCreatedOrder,
        giftSelectionRequired,
        canConfirmOrder,
    };
}
