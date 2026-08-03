import { ref } from "vue";
import { useToast } from "vue-toastification";
import { fetchRepeatableOrderLinesRequest } from "../../api/clientApi";
import { useCheckoutStore } from "../../stores/checkoutStore";
import { useUiStore } from "../../stores/uiStore";
import {
    applyRepeatableLinesToCart,
    formatUnavailableLinesMessage,
} from "./repeatOrderService";

/**
 * @typedef {'merge' | 'replace'} RepeatCartMode
 */

export function useRepeatOrder() {
    const toast = useToast();
    const checkoutStore = useCheckoutStore();
    const uiStore = useUiStore();

    const repeatingOrderId = ref(null);
    const cartChoiceModalOpen = ref(false);
    const pendingRepeat = ref(null);
    const applyingRepeat = ref(false);

    async function finalizeRepeat(data, mode) {
        applyingRepeat.value = true;

        try {
            await applyRepeatableLinesToCart(data.available_lines, { mode });

            const unavailableMessage = formatUnavailableLinesMessage(
                data.unavailable_lines,
            );
            if (unavailableMessage) {
                toast.warning(unavailableMessage);
            }

            toast.success("Товары добавлены в корзину");
            uiStore.setDockActive("cart");
        } catch (e) {
            toast.error(
                e?.response?.data?.message ||
                    checkoutStore.cartError ||
                    "Не удалось повторить заказ.",
            );
            throw e;
        } finally {
            applyingRepeat.value = false;
            pendingRepeat.value = null;
            cartChoiceModalOpen.value = false;
        }
    }

    async function requestRepeatOrder(orderId) {
        if (repeatingOrderId.value != null || applyingRepeat.value) {
            return;
        }

        repeatingOrderId.value = orderId;

        try {
            const data = await fetchRepeatableOrderLinesRequest(orderId);
            const available = Array.isArray(data?.available_lines)
                ? data.available_lines
                : [];

            if (available.length === 0) {
                const unavailableMessage = formatUnavailableLinesMessage(
                    data?.unavailable_lines,
                );
                toast.error(
                    unavailableMessage ||
                        "Ни одна позиция из этого заказа сейчас недоступна.",
                );
                return;
            }

            if (checkoutStore.hasCartItems) {
                pendingRepeat.value = data;
                cartChoiceModalOpen.value = true;
                return;
            }

            await finalizeRepeat(data, "merge");
        } catch (e) {
            toast.error(
                e?.response?.data?.message || "Не удалось подготовить повтор заказа.",
            );
        } finally {
            repeatingOrderId.value = null;
        }
    }

    /**
     * @param {RepeatCartMode} mode
     */
    async function confirmCartChoice(mode) {
        if (!pendingRepeat.value) {
            cartChoiceModalOpen.value = false;
            return;
        }

        await finalizeRepeat(pendingRepeat.value, mode);
    }

    function cancelCartChoice() {
        pendingRepeat.value = null;
        cartChoiceModalOpen.value = false;
    }

    function isRepeatingOrder(orderId) {
        return repeatingOrderId.value === orderId || applyingRepeat.value;
    }

    return {
        repeatingOrderId,
        cartChoiceModalOpen,
        pendingRepeat,
        applyingRepeat,
        requestRepeatOrder,
        confirmCartChoice,
        cancelCartChoice,
        isRepeatingOrder,
    };
}
