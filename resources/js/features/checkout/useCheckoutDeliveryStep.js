import { ref } from "vue";
import { useClientCommands } from "../client/useClient";
import { useClientAddressSelectionModel } from "../client/useClientAddressSelectionModel";

export function useCheckoutDeliveryStep({
    checkoutIntent,
    userStore,
    isGuestCheckout,
    isAuthenticated,
}) {
    const addressSelection = useClientAddressSelectionModel();
    const clientCommands = useClientCommands();

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

    function getDeliveryStepError(selectedAddress) {
        if (!checkoutIntent.deliveryInfo.method) {
            return "Выбери способ доставки.";
        }

        if (isGuestCheckout.value) {
            if (checkoutIntent.deliveryInfo.method === "courier") {
                const address = checkoutIntent.deliveryInfo.address;
                if (!String(address?.street || "").trim() || !String(address?.house || "").trim()) {
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

    function ensureDeliveryDefaults() {
        if (!checkoutIntent.deliveryInfo.method) {
            checkoutIntent.setDeliveryInfo({ method: "courier" });
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

    function patchDeliveryAddress(partial) {
        checkoutIntent.patchDeliveryAddress(partial);
    }

    function selectAddress(addressId) {
        clientCommands.selectAddress(addressId);
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
        addressSelection,
        newAddressForm,
        newAddressLoading,
        newAddressError,
        isNewAddressOpen,
        deliveryStepError,
        getDeliveryStepError,
        validateDeliveryStep,
        ensureDeliveryDefaults,
        ensureAuthAddressUi,
        setDeliveryMethod,
        toggleNewAddressOpen,
        setDeliveryComment,
        patchDeliveryAddress,
        selectAddress,
        handleCreateAddress,
    };
}
