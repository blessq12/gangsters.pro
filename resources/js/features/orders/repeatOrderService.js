import { useCatalogStore } from "../../stores/catalogStore";
import { useCheckoutStore } from "../../stores/checkoutStore";

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
