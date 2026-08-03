import { computed, onBeforeUnmount, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { useFormFieldErrors } from "../../../platform/useFormFieldErrors";
import { applyApiFieldErrors } from "../../../platform/extractApiFieldErrors";
import { useClientAddressSelectionModel } from "../../client/application/useClientAddressSelectionModel";
import { useCheckoutStore } from "../store";
import { useUserStore } from "../../client/store/userStore";
import { useCheckoutFlowContext } from "./flowContext";
import { useOrderPreview } from "./preview";
import {
    CHECKOUT_LOADING_LABELS,
    createOrderDraftPreviewScheduler,
} from "./session";
const GUEST_ADDRESS_ZONE_KEYS = new Set(["street", "house"]);

function createEmptyGuestAddressDraft() {
    return {
        street: "",
        house: "",
        entrance: "",
        apartment: "",
    };
}

function readGuestAddressDraftFromStore(checkoutIntent) {
    const address = checkoutIntent.deliveryInfo.address;
    if (!address || typeof address !== "object") {
        return createEmptyGuestAddressDraft();
    }

    return {
        street: address.street ?? "",
        house: address.house ?? "",
        entrance: address.entrance ?? "",
        apartment: address.apartment ?? "",
    };
}

export function useCheckoutDeliveryStep({
    checkoutIntent,
    userStore,
    isGuestCheckout,
    isAuthenticated,
}) {
    const addressSelection = useClientAddressSelectionModel();
    const previewScheduler = createOrderDraftPreviewScheduler(checkoutIntent);
    const deliveryFieldErrors = useFormFieldErrors();
    const newAddressFieldErrors = useFormFieldErrors();

    const newAddressForm = ref({
        street: "",
        house: "",
        entrance: "",
        apartment: "",
        comment: "",
        make_default: true,
    });
    const newAddressLoading = ref(false);
    const isNewAddressOpen = ref(false);
    const guestAddressDraft = ref(readGuestAddressDraftFromStore(checkoutIntent));

    function syncGuestAddressDraftToStore() {
        checkoutIntent.patchDeliveryAddress({ ...guestAddressDraft.value });
        checkoutIntent.persistSession();
    }

    function resolveGuestAddressForValidation() {
        return guestAddressDraft.value;
    }

    function resolvePreviewAddress() {
        if (isGuestCheckout.value) {
            return null;
        }

        return addressSelection.selectedAddress.value ?? null;
    }

    function canPreviewDelivery() {
        const method = checkoutIntent.deliveryInfo.method;
        if (!method) {
            return false;
        }

        if (method === "pickup") {
            return true;
        }

        if (isGuestCheckout.value) {
            const address = guestAddressDraft.value;
            return (
                String(address?.street || "").trim() !== ""
                && String(address?.house || "").trim() !== ""
            );
        }

        return Boolean(addressSelection.selectedAddress.value);
    }

    function scheduleDeliveryPreview() {
        if (!canPreviewDelivery()) {
            return;
        }

        syncGuestAddressDraftToStore();
        previewScheduler.schedule(resolvePreviewAddress());
    }

    function scheduleZonePreview() {
        if (!canPreviewDelivery()) {
            return;
        }

        syncGuestAddressDraftToStore();
        previewScheduler.schedule(resolvePreviewAddress());
    }

    function validateDeliveryStep(selectedAddress) {
        deliveryFieldErrors.clearAll();

        if (isGuestCheckout.value) {
            syncGuestAddressDraftToStore();
        }

        if (!checkoutIntent.deliveryInfo.method) {
            deliveryFieldErrors.setFieldError("method", "Выбери способ доставки.");
            return false;
        }

        if (checkoutIntent.deliveryInfo.method !== "courier") {
            return true;
        }

        if (isGuestCheckout.value) {
            const address = resolveGuestAddressForValidation();
            if (!String(address?.street || "").trim()) {
                deliveryFieldErrors.setFieldError("street", "Укажи улицу.");
            }
            if (!String(address?.house || "").trim()) {
                deliveryFieldErrors.setFieldError("house", "Укажи дом.");
            }
            return !deliveryFieldErrors.hasAny.value;
        }

        const addressCount = userStore.addresses?.length ?? 0;
        if (addressCount === 0) {
            if (!String(newAddressForm.value.street || "").trim()) {
                newAddressFieldErrors.setFieldError("street", "Укажи улицу.");
            }
            if (!String(newAddressForm.value.house || "").trim()) {
                newAddressFieldErrors.setFieldError("house", "Укажи дом.");
            }
            if (!newAddressFieldErrors.hasAny.value) {
                deliveryFieldErrors.setFormError("Сохрани адрес перед продолжением.");
            }
            return false;
        }

        if (!selectedAddress) {
            deliveryFieldErrors.setFieldError(
                "selectedAddress",
                "Выбери адрес доставки или добавь новый.",
            );
            return false;
        }

        return true;
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

    async function setDeliveryMethod(method) {
        const normalized = method === "pickup" ? "pickup" : "courier";
        if (checkoutIntent.deliveryInfo.method === normalized) {
            return;
        }

        checkoutIntent.setDeliveryInfo({ method: normalized });
        deliveryFieldErrors.clearField("method");
        ensureAuthAddressUi();
        scheduleDeliveryPreview();
    }

    function toggleNewAddressOpen() {
        isNewAddressOpen.value = !isNewAddressOpen.value;
    }

    function setDeliveryComment(comment) {
        checkoutIntent.setDeliveryInfo({ comment });
    }

    function patchGuestAddressDraft(partial) {
        if (!partial || typeof partial !== "object") {
            return;
        }

        Object.assign(guestAddressDraft.value, partial);

        if (partial.street != null) {
            deliveryFieldErrors.clearField("street");
        }
        if (partial.house != null) {
            deliveryFieldErrors.clearField("house");
        }

        const touchesZone = Object.keys(partial).some((key) =>
            GUEST_ADDRESS_ZONE_KEYS.has(key),
        );

        syncGuestAddressDraftToStore();

        if (!touchesZone) {
            return;
        }

        checkoutIntent.setDeliveryAddressDraftDirty(true);
        checkoutIntent.invalidateDeliveryZoneResolve();
        scheduleZonePreview();
    }

    function selectAddress(addressId) {
        userStore.selectAddress(addressId);
        deliveryFieldErrors.clearField("selectedAddress");
        previewScheduler.schedule(resolvePreviewAddress(), 200);
    }

    function clearNewAddressField(key) {
        newAddressFieldErrors.clearField(key);
    }

    watch(
        () => checkoutIntent.deliveryInfo.address,
        () => {
            if (
                isGuestCheckout.value
                && !checkoutIntent.deliveryAddressDraftDirty
            ) {
                guestAddressDraft.value = readGuestAddressDraftFromStore(
                    checkoutIntent,
                );
            }
        },
        { deep: true },
    );

    watch(
        newAddressForm,
        (form) => {
            if (form.street) {
                newAddressFieldErrors.clearField("street");
            }
            if (form.house) {
                newAddressFieldErrors.clearField("house");
            }
        },
        { deep: true },
    );

    async function handleCreateAddress() {
        newAddressFieldErrors.clearAll();

        if (!String(newAddressForm.value.street || "").trim()) {
            newAddressFieldErrors.setFieldError("street", "Укажи улицу.");
        }
        if (!String(newAddressForm.value.house || "").trim()) {
            newAddressFieldErrors.setFieldError("house", "Укажи дом.");
        }
        if (newAddressFieldErrors.hasAny.value) {
            return;
        }

        newAddressLoading.value = true;

        try {
            const data = await userStore.addClientAddress({
                street: newAddressForm.value.street,
                house: newAddressForm.value.house,
                entrance: newAddressForm.value.entrance || null,
                apartment: newAddressForm.value.apartment || null,
                comment: newAddressForm.value.comment || null,
                make_default: newAddressForm.value.make_default,
            });

            isNewAddressOpen.value = false;
            deliveryFieldErrors.clearAll();

            if (!userStore.selectedAddressId && userStore.addresses.length > 0) {
                const fallbackId =
                    data?.client?.default_address_id ??
                    userStore.addresses[userStore.addresses.length - 1]?.id;
                if (fallbackId != null) {
                    userStore.selectAddress(fallbackId);
                }
            }

            newAddressForm.value = {
                street: "",
                house: "",
                entrance: "",
                apartment: "",
                comment: "",
                make_default: true,
            };

            previewScheduler.schedule(resolvePreviewAddress(), 200);
        } catch (e) {
            console.error(e);
            if (
                !applyApiFieldErrors(newAddressFieldErrors, e, {
                    street: "street",
                    house: "house",
                })
            ) {
                newAddressFieldErrors.setFormError(
                    e?.response?.data?.message ||
                        "Не удалось сохранить адрес. Попробуй ещё раз.",
                );
            }
        } finally {
            newAddressLoading.value = false;
        }
    }

    async function flushDeliveryPreview() {
        if (isGuestCheckout.value) {
            syncGuestAddressDraftToStore();
        }

        const result = await previewScheduler.flush(resolvePreviewAddress());
        if (isGuestCheckout.value) {
            checkoutIntent.setDeliveryAddressDraftDirty(false);
        }
        return result;
    }

    onBeforeUnmount(() => {
        previewScheduler.cancel();
    });

    return {
        addressSelection,
        guestAddressDraft,
        newAddressForm,
        newAddressLoading,
        isNewAddressOpen,
        deliveryFieldErrors,
        newAddressFieldErrors,
        validateDeliveryStep,
        ensureDeliveryDefaults,
        ensureAuthAddressUi,
        setDeliveryMethod,
        toggleNewAddressOpen,
        setDeliveryComment,
        patchGuestAddressDraft,
        selectAddress,
        handleCreateAddress,
        scheduleDeliveryPreview,
        flushDeliveryPreview,
    };
}

export const DELIVERY_ZONE_PHASE = Object.freeze({
    HIDDEN: "hidden",
    PICKUP: "pickup",
    IDLE: "idle",
    PENDING: "pending",
    IN_ZONE: "in_zone",
    OUT_OF_ZONE: "out_of_zone",
    UNKNOWN: "unknown",
});

export const DELIVERY_ZONE_MESSAGES = Object.freeze({
    [DELIVERY_ZONE_PHASE.IDLE]:
        "Укажи улицу и дом — проверим зону и стоимость доставки.",
    [DELIVERY_ZONE_PHASE.PENDING]: CHECKOUT_LOADING_LABELS.zoneCheck,
    [DELIVERY_ZONE_PHASE.IN_ZONE]: "Адрес в зоне доставки",
    [DELIVERY_ZONE_PHASE.OUT_OF_ZONE]:
        "Адрес вне зоны — доплата за отдалённый район",
    [DELIVERY_ZONE_PHASE.UNKNOWN]:
        "Не удалось проверить адрес. Уточним доставку при подтверждении заказа.",
});

/**
 * @param {{
 *   method: string | null | undefined,
 *   addressReady: boolean,
 *   previewLoading: boolean,
 *   inZone: boolean | null | undefined,
 * }} input
 * @returns {string}
 */
export function resolveDeliveryZonePhase({
    method,
    addressReady,
    previewLoading,
    inZone,
}) {
    if (method === "pickup") {
        return DELIVERY_ZONE_PHASE.PICKUP;
    }

    if (method !== "courier") {
        return DELIVERY_ZONE_PHASE.HIDDEN;
    }

    if (!addressReady) {
        return DELIVERY_ZONE_PHASE.IDLE;
    }

    if (previewLoading) {
        return DELIVERY_ZONE_PHASE.PENDING;
    }

    if (inZone === true) {
        return DELIVERY_ZONE_PHASE.IN_ZONE;
    }

    if (inZone === false) {
        return DELIVERY_ZONE_PHASE.OUT_OF_ZONE;
    }

    return DELIVERY_ZONE_PHASE.UNKNOWN;
}

export function useDeliveryZoneStatus() {
    const checkoutStore = useCheckoutStore();
    const userStore = useUserStore();
    const { checkoutState } = useCheckoutFlowContext();
    const { deliveryInfo, flushing } = storeToRefs(checkoutStore);
    const { totals, previewLoading } = useOrderPreview();

    const addressReady = computed(() => {
        if (deliveryInfo.value.method !== "courier") {
            return false;
        }

        if (checkoutState.isGuestCheckout) {
            const draft = checkoutState.guestAddressDraft;
            const street = String(draft?.street ?? "").trim();
            const house = String(draft?.house ?? "").trim();

            return street !== "" && house !== "";
        }

        return userStore.selectedAddressId != null;
    });

    const phase = computed(() =>
        resolveDeliveryZonePhase({
            method: deliveryInfo.value.method,
            addressReady: addressReady.value,
            previewLoading: previewLoading.value || flushing.value,
            inZone: totals.value.inZone,
        }),
    );

    const message = computed(() => DELIVERY_ZONE_MESSAGES[phase.value] ?? null);

    const showPanel = computed(
        () =>
            deliveryInfo.value.method === "courier"
            && phase.value !== DELIVERY_ZONE_PHASE.HIDDEN,
    );

    return {
        phase,
        message,
        showPanel,
        addressReady,
        totals,
        previewLoading,
    };
}