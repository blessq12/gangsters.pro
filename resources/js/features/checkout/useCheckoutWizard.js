import { computed, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";
import { formatRuPhone } from "../../utils/phone/formatRuPhone";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";
import { useCatalogStore } from "../../stores/catalogStore";
import { isGuestContactComplete } from "./useCheckoutGuestStep";
import { resolveGiftSelectionRequired } from "./giftSelectionGate";
import { isCheckoutUpsellStepAvailable } from "./checkoutUpsellAvailability";
import { resolveWizardStepMeta } from "./checkoutWizardGroups";
import {
    formatServerDeliveryLine,
    formatServerPaymentLine,
} from "../../domain/order/checkoutServerMappers";
import { CHECKOUT_PAYMENT_METHOD_LABELS } from "./checkoutPaymentMethods";
import { CHECKOUT_DELIVERY_METHOD_META } from "./checkoutDeliveryMethods";

export function useCheckoutWizard({
    checkoutIntent,
    orderStore,
    userStore,
    uiStore,
    isAuthenticated,
    cartView,
    guestStep,
    deliveryStep,
    paymentStep,
    isGuestCheckout,
}) {
    const { hasCartItems } = cartView;
    const catalogStore = useCatalogStore();

    const activeStep = ref("cart");
    const confirmLoading = ref(false);
    const confirmError = ref(null);
    const lastCreatedOrder = ref(null);
    const successSummary = ref(null);
    const includeUpsellStep = computed(() =>
        isCheckoutUpsellStepAvailable(catalogStore.accompanyingCategories),
    );

    const checkoutStepMeta = computed(() => {
        const meta = resolveWizardStepMeta(activeStep.value, isGuestCheckout.value, {
            includeUpsell: includeUpsellStep.value,
        });
        if (meta) {
            return { [activeStep.value]: meta };
        }
        return {};
    });

    const formatPrice = (value) => formatMoneyRublesRu(value);

    function formatPhone(raw) {
        return formatRuPhone(raw);
    }

    function ensureCheckoutDefaults() {
        deliveryStep.ensureDeliveryDefaults();
        paymentStep.ensurePaymentDefaults();
    }

    function firstCheckoutStep() {
        return includeUpsellStep.value ? "upsell" : nextAfterUpsell();
    }

    function nextAfterUpsell() {
        return isGuestCheckout.value ? "guest" : "fulfillment";
    }

    function beginGuestCheckout() {
        if (!hasCartItems.value) return;
        ensureCheckoutDefaults();
        isGuestCheckout.value = true;
        activeStep.value = firstCheckoutStep();
    }

    function beginAuthenticatedCheckout() {
        if (!hasCartItems.value) return;
        ensureCheckoutDefaults();
        isGuestCheckout.value = false;
        activeStep.value = firstCheckoutStep();
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
        if (step === "fulfillment") {
            deliveryStep.ensureAuthAddressUi();
        }
        if (step === "confirm" && giftSelectionRequired.value) {
            uiStore.openGiftSelectionModal({ source: "auto" });
        }
    }, { immediate: true });

    watch(includeUpsellStep, (available) => {
        if (!available && activeStep.value === "upsell") {
            activeStep.value = nextAfterUpsell();
        }
    });

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

    function buildSuccessSummary(order, store) {
        if (!order || typeof order !== "object") {
            return null;
        }

        const deliveryMethod = order.delivery?.method;
        const deliveryLine =
            deliveryMethod === "pickup"
                ? CHECKOUT_DELIVERY_METHOD_META.pickup.label
                : formatServerDeliveryLine(order.delivery);

        const paymentMethod = order.payment?.method;
        const paymentLabel =
            paymentMethod === "cash"
                ? CHECKOUT_PAYMENT_METHOD_LABELS.cash
                : CHECKOUT_PAYMENT_METHOD_LABELS.card;

        return {
            orderId: order.id ?? null,
            totalRubles: Number(order.total) || store.itemsTotalRubles || 0,
            deliveryLine,
            paymentLine: paymentLabel,
        };
    }

    function goToCart() {
        activeStep.value = "cart";
    }

    function goToUpsell() {
        if (!hasCartItems.value) {
            activeStep.value = "cart";
            return;
        }
        // Назад с гостя / вход в upsell: без товаров upsell — в корзину.
        activeStep.value = includeUpsellStep.value ? "upsell" : "cart";
    }

    function goToUpsellNext() {
        activeStep.value = nextAfterUpsell();
    }

    function goToGuest() {
        if (!hasCartItems.value) {
            activeStep.value = "cart";
            return;
        }
        if (isGuestCheckout.value) {
            activeStep.value = "guest";
            return;
        }
        activeStep.value = includeUpsellStep.value ? "upsell" : "fulfillment";
    }

    function goToFulfillment() {
        if (!hasCartItems.value) {
            activeStep.value = "cart";
            return;
        }
        if (isGuestCheckout.value) {
            activeStep.value = isGuestContactComplete(checkoutIntent.guestContact)
                ? "fulfillment"
                : "guest";
            return;
        }
        activeStep.value = "fulfillment";
    }

    function goToFulfillmentBack() {
        if (isGuestCheckout.value) {
            activeStep.value = "guest";
            return;
        }
        activeStep.value = includeUpsellStep.value ? "upsell" : "cart";
    }

    async function goToGuestNext() {
        if (!guestStep.validateGuestStep()) {
            return;
        }
        await checkoutIntent.flushClientToServer({ isGuest: true });
        activeStep.value = "fulfillment";
    }

    async function goToFulfillmentNext() {
        if (!paymentStep.validatePaymentStep()) {
            return;
        }

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

        activeStep.value = "confirm";
    }

    function goToConfirmBack() {
        activeStep.value = "fulfillment";
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
            successSummary.value = buildSuccessSummary(
                lastCreatedOrder.value,
                checkoutIntent,
            );
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
        goToUpsell,
        goToUpsellNext,
        goToGuest,
        goToFulfillment,
        goToFulfillmentBack,
        goToGuestNext,
        goToFulfillmentNext,
        goToConfirmBack,
        goToSuccess,
        handleConfirmOrder,
        confirmLoading,
        confirmError,
        lastCreatedOrder,
        successSummary,
        giftSelectionRequired,
        canConfirmOrder,
    };
}
