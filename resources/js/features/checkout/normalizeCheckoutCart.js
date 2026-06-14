import { roundRubles2 } from "../../utils/moneyFormat";

/**
 * @param {object|null|undefined} item
 */
export function isComplementCartLine(item) {
    if (!item || typeof item !== "object") {
        return false;
    }

    if (item.lineKind === "complement") {
        return true;
    }

    const payloadKind = item.payload?.kind;
    if (payloadKind === "complement") {
        return true;
    }

    return String(item.lineKey || "").startsWith("complement:");
}

/**
 * @param {object|null|undefined} item
 */
export function isGiftCartLine(item) {
    if (!item || typeof item !== "object") {
        return false;
    }

    if (item.lineKind === "gift") {
        return true;
    }

    const payloadKind = item.payload?.kind;
    if (payloadKind === "gift") {
        return true;
    }

    return String(item.lineKey || "").startsWith("gift:");
}

/**
 * Системные строки визарда без подарка (подарок — в саммари).
 *
 * @param {object[]|null|undefined} items
 */
export function wizardVisibleSystemItems(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    return items.filter((item) => !isGiftCartLine(item));
}

/**
 * @param {object[]|null|undefined} items
 */
export function wizardNonComplementSystemItems(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    return items.filter((item) => !isComplementCartLine(item) && !isGiftCartLine(item));
}

/**
 * @param {object[]|null|undefined} items
 * @returns {object|null}
 */
export function selectedGiftCartLine(items) {
    if (!Array.isArray(items)) {
        return null;
    }

    return items.find((item) => isGiftCartLine(item)) ?? null;
}

/**
 * @param {object|null|undefined} promoState
 * @returns {{ productId: number, name: string, qty: number }|null}
 */
function resolveSelectedGiftFromPromoState(promoState) {
    const giftPromotion = promoState?.gift_promotion;
    if (!giftPromotion || typeof giftPromotion !== "object") {
        return null;
    }

    const productId = Number(giftPromotion.selected_product_id) || 0;
    if (productId <= 0) {
        return null;
    }

    const candidateItems = Array.isArray(giftPromotion.candidate_items)
        ? giftPromotion.candidate_items
        : [];
    const candidate = candidateItems.find((item) => Number(item?.id) === productId);

    return {
        productId,
        name: candidate?.name ? String(candidate.name) : `Товар #${productId}`,
        qty: 1,
    };
}

/**
 * @param {{
 *   cartItems?: object[]|null|undefined,
 *   promoState?: object|null|undefined,
 * }} input
 * @returns {{ productId: number, name: string, qty: number }|null}
 */
export function resolveSelectedGiftSummary(input) {
    const line = selectedGiftCartLine(input?.cartItems);
    if (line) {
        return {
            productId: line.productId,
            name: line.productSnapshot?.name
                ? String(line.productSnapshot.name)
                : `Товар #${line.productId}`,
            qty: Number(line.qty) || 1,
        };
    }

    return resolveSelectedGiftFromPromoState(input?.promoState);
}

/**
 * Адаптер блока cart из Checkout API к legacy-формату cartStore для UI.
 *
 * @param {object|null|undefined} cart
 * @returns {{ items: object[], itemsTotalRubles: number, itemsSubtotalRubles: number }}
 */
export function normalizeCheckoutCartBlock(cart) {
    if (!cart || typeof cart !== "object") {
        return { items: [], itemsTotalRubles: 0, itemsSubtotalRubles: 0 };
    }

    const items = Array.isArray(cart.items)
        ? cart.items
              .map((row) => {
                  if (!row || typeof row !== "object") {
                      return null;
                  }

                  const productId = Number(row.product_id) || 0;
                  const qty = Number(row.quantity) || 0;
                  if (productId <= 0 || qty <= 0) {
                      return null;
                  }

                  const unitRub = roundRubles2(Number(row.unit_price_rubles) || 0);
                  const lineRub = roundRubles2(Number(row.line_total_rubles) || unitRub * qty);
                  const unitKopecks = Math.round(unitRub * 100);
                  const lineKopecks = Math.round(lineRub * 100);
                  const payload =
                      row.payload && typeof row.payload === "object" ? row.payload : null;
                  const lineKind = payload?.kind === "gift"
                      ? "gift"
                      : payload?.kind === "complement"
                        ? "complement"
                        : "user";
                  const isSystem = lineKind === "gift" || lineKind === "complement";

                  return {
                      lineKey: `${lineKind}:${productId}`,
                      origin: isSystem ? "system" : "user",
                      isSystem,
                      lineKind,
                      productId,
                      qty,
                      productSnapshot: {
                          id: productId,
                          name: row.product_name ? String(row.product_name) : "",
                          price: unitRub,
                      },
                      pricing: {
                          listUnitPriceKopecks: unitKopecks,
                          finalUnitPriceKopecks: unitKopecks,
                          lineTotalKopecks: lineKopecks,
                      },
                      payload: row.payload ?? null,
                  };
              })
              .filter(Boolean)
        : [];

    const itemsSubtotalRubles = roundRubles2(Number(cart.items_total_rubles) || 0);
    const itemsTotalRubles = roundRubles2(
        Number(cart.payable_total_rubles ?? cart.items_total_rubles) || 0,
    );

    return {
        items,
        itemsTotalRubles,
        itemsSubtotalRubles,
    };
}
