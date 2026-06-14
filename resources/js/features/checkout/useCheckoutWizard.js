import { computed, ref, watch } from "vue";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";
import { formatRuPhone } from "../../utils/phone/formatRuPhone";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";
import { resolveResumeCheckoutLabel } from "./checkoutWizardGroups";
import { isGuestContactComplete } from "./useCheckoutGuestStep";

const RESUME_STEPS = ["guest", "delivery", "payment", "confirm"];

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
    const resumeCheckoutStep = ref(null);

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

    const canResumeCheckout = computed(
        () =>
            hasCartItems.value &&
            resumeCheckoutStep.value !== null &&
            RESUME_STEPS.includes(resumeCheckoutStep.value),
    );

    const resumeCheckoutLabel = computed(() =>
        resolveResumeCheckoutLabel(resumeCheckoutStep.value),
    );

    const formatPrice = (value) => formatMoneyRublesRu(value);

    function formatPhone(raw) {
        return formatRuPhone(raw);
    }

    function ensureCheckoutDefaults() {
        deliveryStep.ensureDeliveryDefaults();
        paymentStep.ensurePaymentDefaults();
    }

    function syncResumeFromSuggested() {
        const step = checkoutIntent.suggestedStep;
        if (step && RESUME_STEPS.includes(step)) {
            resumeCheckoutStep.value = step;
        } else {
            resumeCheckoutStep.value = null;
        }
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
        () => checkoutIntent.suggestedStep,
        () => {
            syncResumeFromSuggested();
        },
        { immediate: true },
    );

    watch(
        () => uiStore.dockActiveId,
        (dockId) => {
            if (dockId === "cart") {
                syncResumeFromSuggested();
            }
        },
    );

    watch(isAuthenticated, (authed) => {
        if (authed && activeStep.value === "guest") {
            beginAuthenticatedCheckout();
        }
    });

    watch(activeStep, (step) => {
        uiStore.setCheckoutWizardStep(step);
        if (step !== "confirm") {
            uiStore.closeGiftSelectionModal({ dismissAuto: false });
        }
        if (step === "delivery") {
            deliveryStep.ensureAuthAddressUi();
        }
    }, { immediate: true });

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

    function resolveResumeStep(step) {
        if (!isGuestCheckout.value || isGuestContactComplete(checkoutIntent.guestContact)) {
            return step;
        }

        return "guest";
    }

    function handleResumeCheckout() {
        const step = resumeCheckoutStep.value;
        if (!step || !RESUME_STEPS.includes(step) || !hasCartItems.value) {
            return;
        }

        ensureCheckoutDefaults();

        if (isAuthenticated.value) {
            isGuestCheckout.value = false;
            activeStep.value = step;
            if (step === "delivery") {
                deliveryStep.ensureAuthAddressUi();
            }
            return;
        }

        isGuestCheckout.value = true;
        activeStep.value = resolveResumeStep(step);
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
        await checkoutIntent.flushDeliveryToServer(selectedAddress);
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
        resumeCheckoutStep.value = null;
    }

    function canConfirmOrder(selectedAddress) {
        if (!hasCartItems.value) return false;

        const guestOk = !isGuestCheckout.value || guestStep.getGuestStepError() === "";

        return (
            guestOk &&
            deliveryStep.getDeliveryStepError(selectedAddress) === "" &&
            paymentStep.getPaymentStepError() === ""
        );
    }

    async function handleConfirmOrder() {
        const selectedAddress = deliveryStep.addressSelection.selectedAddress.value;
        if (!canConfirmOrder(selectedAddress)) {
            return;
        }

        orderStore.loading.create = true;
        orderStore.error.create = null;

        try {
            if (isGuestCheckout.value) {
                await checkoutIntent.flushClientToServer({ isGuest: true });
            } else {
                await checkoutIntent.flushClientToServer({
                    clientId: userStore.profile.id,
                });
            }
            await checkoutIntent.flushDeliveryToServer(selectedAddress);
            await checkoutIntent.flushPaymentToServer();
            const confirmed = await checkoutIntent.confirmCheckout();
            orderStore.lastCreatedOrder = {
                id: confirmed.checkout_id,
            };
            emitDomainEvent(DOMAIN_EVENTS.ORDER_CREATED, {
                order: orderStore.lastCreatedOrder,
            });
            goToSuccess();
        } catch (e) {
            orderStore.error.create =
                e?.response?.data?.message ||
                checkoutIntent.error ||
                "Не удалось подтвердить оформление.";
        } finally {
            orderStore.loading.create = false;
        }
    }

    return {
        activeStep,
        isGuestCheckout,
        resumeCheckoutStep,
        isAuthenticated,
        checkoutStepMeta,
        canResumeCheckout,
        resumeCheckoutLabel,
        formatPrice,
        formatPhone,
        handleStartCheckout,
        handleContinueAsGuest,
        handleResumeCheckout,
        openProfileDock,
        goToCart,
        goToGuest,
        goToDelivery,
        goToGuestNext,
        goToPayment,
        goToConfirm,
        goToSuccess,
        handleConfirmOrder,
    };
}
