import { roundRubles2 } from "../../utils/moneyFormat";

/**
 * Адаптер блока cart из Checkout API к legacy-формату cartStore для UI.
 *
 * @param {object|null|undefined} cart
 * @returns {{ items: object[], itemsTotalRubles: number }}
 */
export function normalizeCheckoutCartBlock(cart) {
    if (!cart || typeof cart !== "object") {
        return { items: [], itemsTotalRubles: 0 };
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

    return {
        items,
        itemsTotalRubles: roundRubles2(Number(cart.items_total_rubles) || 0),
    };
}
