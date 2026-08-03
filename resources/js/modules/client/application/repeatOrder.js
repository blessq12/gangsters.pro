import { ref } from "vue";
import { useToast } from "vue-toastification";
import { fetchRepeatableOrderLinesRequest } from "../api";
import { useCatalogStore } from "../../catalog/store";
import { useCheckoutStore } from "../../checkout/store";
import { useUiStore } from "../../shell/store/uiStore";

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


/**
 * @typedef {'merge' | 'replace'} RepeatCartMode
 */

/**
 * @param {object} line
 * @returns {object}
 */
function buildProductFromRepeatableLine(line) {
    const catalogStore = useCatalogStore();
    const productId = Number(line.product_id);
    const fromCatalog = catalogStore.flatProducts.find(
        (entry) => Number(entry.id) === productId,
    );

    if (fromCatalog) {
        return fromCatalog;
    }

    return {
        id: productId,
        name: String(line.product_name || ""),
        price: { amount: Number(line.unit_price_rubles) || 0 },
        kind: line.catalog_kind === "set" ? "set" : "product",
    };
}

/**
 * @param {Array<object>} lines
 * @param {{ mode: RepeatCartMode }} options
 */
export async function applyRepeatableLinesToCart(lines, { mode }) {
    const checkoutStore = useCheckoutStore();

    if (!Array.isArray(lines) || lines.length === 0) {
        return;
    }

    if (mode === "replace") {
        await checkoutStore.clearCart();
    }

    for (const line of lines) {
        const product = buildProductFromRepeatableLine(line);
        const quantity = Math.max(1, Number(line.quantity) || 1);
        await checkoutStore.addToCart(product, quantity);
    }
}

/**
 * @param {Array<object>} unavailableLines
 * @returns {string}
 */
export function formatUnavailableLinesMessage(unavailableLines) {
    if (!Array.isArray(unavailableLines) || unavailableLines.length === 0) {
        return "";
    }

    const names = unavailableLines
        .map((line) => String(line.product_name || "Товар").trim())
        .filter(Boolean);

    if (names.length === 0) {
        return "Часть позиций недоступна в каталоге.";
    }

    if (names.length === 1) {
        return `Недоступно: ${names[0]}.`;
    }

    return `Недоступно ${names.length} позиций: ${names.slice(0, 3).join(", ")}${
        names.length > 3 ? "…" : ""
    }`;
}
