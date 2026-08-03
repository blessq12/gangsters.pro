import { computed, ref, watch } from "vue";
import { useFormFieldErrors } from "../../../platform/useFormFieldErrors";
import { validateRuPhoneForSubmit } from "../../../platform/ruPhone";
import { isCheckoutPaymentMethod, normalizeCheckoutPaymentMethod } from "../domain/checkoutServerMappers";
import { storeToRefs } from "pinia";
import { formatMoneyRublesRu } from "../../../platform/moneyFormat";
import { formatRuPhone } from "../../../platform/ruPhone";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../../platform/domainEvents";
import { useCatalogStore } from "../../catalog/store";
import { useOrderStore } from "../../client/store/orderStore";
import { useUserStore } from "../../client/store/userStore";
import { useUiStore } from "../../shell/store/uiStore";
import { useCheckoutStore } from "../store";
import { resolveGiftSelectionRequired, isCheckoutUpsellStepAvailable } from "./preview";
import { useOrderPreview } from "./preview";
import { useCheckoutSession } from "./session";
import {
    CHECKOUT_LOADING_LABELS,
    CHECKOUT_NAV_LABELS,
    CHECKOUT_STEP_HINTS,
    CHECKOUT_WAITER_LINES,
    CHECKOUT_WIZARD_GROUPS,
} from "./session";
import { useCheckoutDeliveryStep } from "./delivery";
import { useCheckoutFlowContext } from "./flowContext";
import {
    formatServerDeliveryLine,
    formatServerPaymentLine,
} from "../domain/checkoutServerMappers";
import { CHECKOUT_PAYMENT_METHOD_LABELS } from "../domain/checkoutServerMappers";
import { CHECKOUT_DELIVERY_METHOD_META } from "../domain/checkoutServerMappers";
import CheckoutCartStep from "../../../components/checkout/CheckoutCartStep.vue";
import CheckoutUpsellStep from "../../../components/checkout/CheckoutUpsellStep.vue";
import CheckoutGuestStep from "../../../components/checkout/CheckoutGuestStep.vue";
import CheckoutFulfillmentStep from "../../../components/checkout/CheckoutFulfillmentStep.vue";
import CheckoutConfirmStep from "../../../components/checkout/CheckoutConfirmStep.vue";
import CheckoutSuccessStep from "../../../components/checkout/CheckoutSuccessStep.vue";

export {
    CHECKOUT_LOADING_LABELS,
    CHECKOUT_NAV_LABELS,
    CHECKOUT_STEP_HINTS,
    CHECKOUT_WAITER_LINES,
    CHECKOUT_WIZARD_GROUPS,
};

export const CHECKOUT_WIZARD_FLOW_GUEST = Object.freeze(["upsell", "guest", "fulfillment", "confirm"]);
export const CHECKOUT_WIZARD_FLOW_AUTH = Object.freeze(["upsell", "fulfillment", "confirm"]);
export function mapServerWizardStep(serverStep) { if (serverStep == null || serverStep === "") return null; if (["upsell", "guest", "fulfillment", "confirm"].includes(serverStep)) return serverStep; return ({ delivery: "fulfillment", payment: "fulfillment", drinks: "upsell" })[serverStep] ?? null; }
export function resolveWizardFlowSteps(isGuestCheckout, options = {}) { const base = isGuestCheckout ? CHECKOUT_WIZARD_FLOW_GUEST : CHECKOUT_WIZARD_FLOW_AUTH; return options.includeUpsell === false ? Object.freeze(base.filter((step) => step !== "upsell")) : base; }
export function resolveWizardStepMeta(step, isGuestCheckout, options = {}) { const flow = resolveWizardFlowSteps(isGuestCheckout, options); const index = flow.indexOf(step); return index === -1 ? null : { n: index + 1, total: flow.length, label: CHECKOUT_WIZARD_GROUPS[step] }; }
export function resolveCheckoutDockTitle(step) { return CHECKOUT_WIZARD_GROUPS[step] ?? CHECKOUT_WIZARD_GROUPS.cart; }
export { useCheckoutNavTotal } from "./preview";
export function defineCheckoutWizardStep(definition) { if (!definition || typeof definition !== "object" || !definition.id || !definition.component || !definition.title) throw new Error("Invalid checkout wizard step"); return Object.freeze({ id: definition.id, component: definition.component, title: definition.title, hint: definition.hint ?? null }); }
const stepsById = new Map([
    ["cart", defineCheckoutWizardStep({ id: "cart", component: CheckoutCartStep, title: CHECKOUT_WIZARD_GROUPS.cart, hint: CHECKOUT_WAITER_LINES.cart })],
    ["upsell", defineCheckoutWizardStep({ id: "upsell", component: CheckoutUpsellStep, title: CHECKOUT_WIZARD_GROUPS.upsell, hint: CHECKOUT_WAITER_LINES.upsell })],
    ["guest", defineCheckoutWizardStep({ id: "guest", component: CheckoutGuestStep, title: CHECKOUT_WIZARD_GROUPS.guest, hint: CHECKOUT_WAITER_LINES.guest })],
    ["fulfillment", defineCheckoutWizardStep({ id: "fulfillment", component: CheckoutFulfillmentStep, title: CHECKOUT_WIZARD_GROUPS.fulfillment, hint: CHECKOUT_WAITER_LINES.fulfillment })],
    ["confirm", defineCheckoutWizardStep({ id: "confirm", component: CheckoutConfirmStep, title: CHECKOUT_WIZARD_GROUPS.confirm, hint: CHECKOUT_WAITER_LINES.confirm })],
    ["success", defineCheckoutWizardStep({ id: "success", component: CheckoutSuccessStep, title: CHECKOUT_WIZARD_GROUPS.success, hint: CHECKOUT_WAITER_LINES.success })],
]);
export function registerCheckoutWizardStep(step) { if (!step?.id) throw new Error("registerCheckoutWizardStep: step.id required"); stepsById.set(step.id, step); }
export function getCheckoutWizardStep(id) { return id ? stepsById.get(id) ?? null : null; }
export function listCheckoutWizardSteps() { return Object.freeze([...stepsById.values()]); }
export function hasCheckoutWizardStep(id) { return stepsById.has(id); }

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


export function useCheckout() {
    const checkoutIntent = useCheckoutStore();
    const orderStore = useOrderStore();
    const userStore = useUserStore();
    const uiStore = useUiStore();
    const isGuestCheckout = ref(false);
    const isAuthenticated = computed(
        () => Boolean(userStore.token) && Boolean(userStore.profile?.id),
    );

    const cartView = useCheckoutSession();
    const guestStep = useCheckoutGuestStep(checkoutIntent);
    const paymentStep = useCheckoutPaymentStep(checkoutIntent);
    const deliveryStep = useCheckoutDeliveryStep({
        checkoutIntent,
        userStore,
        isGuestCheckout,
        isAuthenticated,
    });
    const wizard = useCheckoutWizard({
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
    });

    return {
        cartStore: checkoutIntent,
        checkoutIntent,
        checkoutStore: checkoutIntent,
        orderStore,
        userStore,
        ...cartView,
        ...wizard,
        ...deliveryStep,
        ...guestStep,
        ...paymentStep,
    };
}
export function useCheckoutPaymentStep(checkoutIntent) {
    const paymentFieldErrors = useFormFieldErrors();

    function validatePaymentStep() {
        paymentFieldErrors.clearAll();

        if (!checkoutIntent.paymentInfo.method) {
            paymentFieldErrors.setFieldError("method", "Выбери способ оплаты.");
        }

        return !paymentFieldErrors.hasAny.value;
    }

    function ensurePaymentDefaults() {
        if (!isCheckoutPaymentMethod(checkoutIntent.paymentInfo.method)) {
            checkoutIntent.setPaymentInfo({
                method: normalizeCheckoutPaymentMethod(
                    checkoutIntent.paymentInfo.method,
                ),
            });
        }
    }

    function setPaymentMethod(method) {
        checkoutIntent.setPaymentInfo({
            method: normalizeCheckoutPaymentMethod(method),
        });
        paymentFieldErrors.clearField("method");
    }

    function setPaymentChangeFrom(changeFrom) {
        checkoutIntent.setPaymentInfo({ changeFrom });
    }

    return {
        paymentFieldErrors,
        validatePaymentStep,
        ensurePaymentDefaults,
        setPaymentMethod,
        setPaymentChangeFrom,
    };
}

export function isGuestContactComplete(guestContact) {
    const name = String(guestContact?.name || "").trim();
    const phone = String(guestContact?.phone || "").trim();

    return name !== "" && phone !== "";
}

export function useCheckoutGuestStep(checkoutIntent) {
    const guestFieldErrors = useFormFieldErrors();

    function validateGuestStep() {
        guestFieldErrors.clearAll();

        const guestContact = checkoutIntent.guestContact;
        if (!String(guestContact?.name || "").trim()) {
            guestFieldErrors.setFieldError("name", "Укажи имя для связи.");
        }

        const phoneCheck = validateRuPhoneForSubmit(guestContact?.phone);
        if (!phoneCheck.ok) {
            guestFieldErrors.setFieldError("phone", phoneCheck.message);
        }

        return !guestFieldErrors.hasAny.value;
    }

    function setGuestContact(payload) {
        checkoutIntent.setGuestContact(payload);
        if (payload?.name != null) {
            guestFieldErrors.clearField("name");
        }
        if (payload?.phone != null) {
            guestFieldErrors.clearField("phone");
        }
    }

    return {
        guestFieldErrors,
        validateGuestStep,
        setGuestContact,
    };
}
