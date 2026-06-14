import { computed, ref, watch } from "vue";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useOrderStore } from "../../stores/orderStore";
import { useUserStore } from "../../stores/userStore";
import { useUiStore } from "../../stores/uiStore";
import { useClientCommands, useClientReadModel } from "../client/useClient";
import { useClientAddressSelectionModel } from "../client/useClientAddressSelectionModel";
import { useCartReadModel } from "../shoppingSession/useCartReadModel";
import { useBenefitProgress } from "../shoppingSession/useBenefitProgress";
import { formatMoneyRublesRu } from "../../utils/moneyFormat";
import { formatRuPhone } from "../../utils/phone/formatRuPhone";
import { validateRuPhoneForSubmit } from "../../validation/ruPhone";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../shared/domainEvents";
import {
    isCheckoutPaymentMethod,
    normalizeCheckoutPaymentMethod,
} from "./checkoutPaymentMethods";
import { resolveResumeCheckoutLabel } from "./checkoutWizardGroups";

const RESUME_STEPS = ["guest", "delivery", "payment", "confirm"];

function isGuestContactComplete(gc) {
    const name = String(gc?.name || "").trim();
    const phone = String(gc?.phone || "").trim();

    return name !== "" && phone !== "";
}

export function useCheckout() {
    const checkoutIntent = useCheckoutStore();
    const orderStore = useOrderStore();
    const userStore = useUserStore();
    const uiStore = useUiStore();
    const clientReadModel = useClientReadModel();
    const clientCommands = useClientCommands();
    const addressSelection = useClientAddressSelectionModel();
    const cartReadModel = useCartReadModel();
    const benefits = useBenefitProgress();

    const cartItems = computed(() => cartReadModel.items.value);
    const userCartItems = computed(() => cartReadModel.userItems.value);
    const systemCartItems = computed(() => cartReadModel.systemItems.value);
    const totalAmount = computed(() =>
        cartReadModel.hasDeliveryPricing.value
            ? cartReadModel.grandTotalWithDelivery.value
            : cartReadModel.totalAmount.value,
    );
    const hasDeliveryPricing = computed(() => cartReadModel.hasDeliveryPricing.value);
    const itemsTotalAmount = computed(() => cartReadModel.itemsTotalAmount.value);
    const deliveryFeeAmount = computed(() => cartReadModel.deliveryFeeAmount.value);
    const isDeliveryFree = computed(() => cartReadModel.isDeliveryFree.value);
    const userTotalAmount = computed(() => cartReadModel.userTotalAmount.value);
    const systemTotalAmount = computed(() => cartReadModel.systemTotalAmount.value);
    const promoState = computed(() => cartReadModel.promoState.value);
    const benefitsProgress = computed(() => cartReadModel.benefitsProgress.value);
    const deliveryBenefit = computed(() => benefits.delivery.value);
    const giftBenefit = computed(() => benefits.gift.value);
    const isAuthenticated = computed(() => clientReadModel.isAuthenticated.value);
    const hasCartItems = computed(() => userCartItems.value.length > 0);

    const activeStep = ref("cart");
    const isGuestCheckout = ref(false);
    const resumeCheckoutStep = ref(null);

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
    const guestStepError = ref("");
    const deliveryStepError = ref("");
    const paymentStepError = ref("");

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

    function syncResumeFromSuggested() {
        const step = checkoutIntent.suggestedStep;
        if (step && RESUME_STEPS.includes(step)) {
            resumeCheckoutStep.value = step;
        } else {
            resumeCheckoutStep.value = null;
        }
    }

    function getGuestStepError() {
        const gc = checkoutIntent.guestContact;
        if (!String(gc?.name || "").trim()) {
            return "Укажи имя для связи.";
        }
        const phoneCheck = validateRuPhoneForSubmit(gc?.phone);
        if (!phoneCheck.ok) {
            return phoneCheck.message;
        }

        return "";
    }

    function validateGuestStep() {
        const message = getGuestStepError();
        guestStepError.value = message;

        return message === "";
    }

    function getDeliveryStepError(selectedAddress) {
        if (!checkoutIntent.deliveryInfo.method) {
            return "Выбери способ доставки.";
        }

        if (isGuestCheckout.value) {
            if (checkoutIntent.deliveryInfo.method === "courier") {
                const a = checkoutIntent.deliveryInfo.address;
                if (!String(a?.street || "").trim() || !String(a?.house || "").trim()) {
                    return "Укажи улицу и дом для курьера.";
                }
            }
        } else if (checkoutIntent.deliveryInfo.method === "courier") {
            const addressCount = userStore.addresses?.length ?? 0;
            if (addressCount === 0) {
                return "Заполни и сохрани адрес доставки.";
            }
            if (!selectedAddress) {
                return "Выбери адрес доставки или добавь новый.";
            }
        }

        return "";
    }

    function validateDeliveryStep(selectedAddress) {
        const message = getDeliveryStepError(selectedAddress);
        deliveryStepError.value = message;

        return message === "";
    }

    function getPaymentStepError() {
        if (!checkoutIntent.paymentInfo.method) {
            return "Выбери способ оплаты.";
        }

        return "";
    }

    function validatePaymentStep() {
        const message = getPaymentStepError();
        paymentStepError.value = message;

        return message === "";
    }

    function canConfirmOrder(selectedAddress) {
        if (!hasCartItems.value) return false;

        const guestOk = !isGuestCheckout.value || getGuestStepError() === "";

        return (
            guestOk &&
            getDeliveryStepError(selectedAddress) === "" &&
            getPaymentStepError() === ""
        );
    }

    function ensureCheckoutDefaults() {
        if (!checkoutIntent.deliveryInfo.method) {
            checkoutIntent.setDeliveryInfo({ method: "courier" });
        }
        if (!isCheckoutPaymentMethod(checkoutIntent.paymentInfo.method)) {
            checkoutIntent.setPaymentInfo({
                method: normalizeCheckoutPaymentMethod(
                    checkoutIntent.paymentInfo.method,
                ),
            });
        }
    }

    function ensureAuthAddressUi() {
        if (!isAuthenticated.value || isGuestCheckout.value) {
            return;
        }
        if (checkoutIntent.deliveryInfo.method === "pickup") {
            return;
        }
        const addressCount = userStore.addresses?.length ?? 0;
        isNewAddressOpen.value = addressCount === 0;
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
        ensureAuthAddressUi();
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
            ensureAuthAddressUi();
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

        const order = ["guest", "delivery", "payment", "confirm"];
        const targetIdx = order.indexOf(step);
        if (targetIdx <= 0) {
            return "guest";
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
                ensureAuthAddressUi();
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
        if (!validateGuestStep()) {
            return;
        }
        await checkoutIntent.flushClientToServer({ isGuest: true });
        activeStep.value = "delivery";
    }

    async function goToPayment() {
        const selectedAddress = addressSelection.selectedAddress.value;
        if (!validateDeliveryStep(selectedAddress)) {
            return;
        }
        await checkoutIntent.flushDeliveryToServer(selectedAddress);
        activeStep.value = "payment";
    }

    async function goToConfirm() {
        if (!validatePaymentStep()) {
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

    async function syncDeliveryBenefitsPreview() {
        const method = checkoutIntent.deliveryInfo.method;
        if (!method) {
            return;
        }

        try {
            const selectedAddress =
                method === "courier" ? addressSelection.selectedAddress.value : null;
            await checkoutIntent.flushDeliveryToServer(selectedAddress);
        } catch (e) {
            console.error("syncDeliveryBenefitsPreview / checkout", e);
        }
    }

    async function setDeliveryMethod(method) {
        const normalized = method === "pickup" ? "pickup" : "courier";
        if (checkoutIntent.deliveryInfo.method === normalized) {
            return;
        }

        checkoutIntent.setDeliveryInfo({ method: normalized });
        ensureAuthAddressUi();
        await syncDeliveryBenefitsPreview();
    }

    function toggleNewAddressOpen() {
        isNewAddressOpen.value = !isNewAddressOpen.value;
    }

    function setDeliveryComment(comment) {
        checkoutIntent.setDeliveryInfo({ comment });
    }

    function setGuestContact(payload) {
        checkoutIntent.setGuestContact(payload);
    }

    function patchDeliveryAddress(partial) {
        checkoutIntent.patchDeliveryAddress(partial);
    }

    function selectAddress(addressId) {
        clientCommands.selectAddress(addressId);
    }

    function setPaymentMethod(method) {
        checkoutIntent.setPaymentInfo({
            method: normalizeCheckoutPaymentMethod(method),
        });
    }

    function setPaymentChangeFrom(changeFrom) {
        checkoutIntent.setPaymentInfo({ changeFrom });
    }

    function setCustomerComment(comment) {
        checkoutIntent.setCustomerComment(comment);
    }

    async function handleConfirmOrder() {
        const selectedAddress = addressSelection.selectedAddress.value;
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

    async function handleCreateAddress() {
        newAddressError.value = "";

        if (!newAddressForm.value.street || !newAddressForm.value.house) {
            newAddressError.value = "Укажи улицу и дом";
            return;
        }

        newAddressLoading.value = true;

        try {
            const data = await clientCommands.addAddress({
                title: newAddressForm.value.title || null,
                street: newAddressForm.value.street,
                house: newAddressForm.value.house,
                entrance: newAddressForm.value.entrance || null,
                apartment: newAddressForm.value.apartment || null,
                comment: newAddressForm.value.comment || null,
                make_default: newAddressForm.value.make_default,
            });

            isNewAddressOpen.value = false;

            if (!userStore.selectedAddressId && userStore.addresses.length > 0) {
                const fallbackId =
                    data?.client?.default_address_id ??
                    userStore.addresses[userStore.addresses.length - 1]?.id;
                if (fallbackId != null) {
                    clientCommands.selectAddress(fallbackId);
                }
            }

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
        checkoutIntent,
        checkoutStore: checkoutIntent,
        orderStore,
        userStore,
        clientReadModel,
        cartItems,
        userCartItems,
        systemCartItems,
        totalAmount,
        itemsTotalAmount,
        deliveryFeeAmount,
        isDeliveryFree,
        hasDeliveryPricing,
        userTotalAmount,
        systemTotalAmount,
        promoState,
        benefitsProgress,
        benefits,
        deliveryBenefit,
        giftBenefit,
        isAuthenticated,
        hasCartItems,
        activeStep,
        isGuestCheckout,
        resumeCheckoutStep,
        canResumeCheckout,
        resumeCheckoutLabel,
        checkoutStepMeta,
        newAddressForm,
        newAddressLoading,
        newAddressError,
        isNewAddressOpen,
        guestStepError,
        deliveryStepError,
        paymentStepError,
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
        toggleNewAddressOpen,
    };
}
