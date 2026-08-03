import { roundRubles2 } from "../../../platform/moneyFormat";

function emptyMoneyBenefit() {
    return {
        isActive: false,
        isReached: false,
        thresholdKopecks: null,
        currentKopecks: 0,
        remainingKopecks: 0,
        isPreview: false,
    };
}

function emptyComplementBenefit() {
    return {
        isActive: false,
        isReached: false,
        rollsPerSet: null,
        currentRollCount: 0,
        entitledSetCount: 0,
        remainingRollCount: 0,
    };
}

function normalizeMoneyBenefit(raw) {
    if (!raw || typeof raw !== "object") {
        return emptyMoneyBenefit();
    }

    return {
        isActive: Boolean(raw.isActive ?? raw.is_active),
        isReached: Boolean(raw.isReached ?? raw.is_reached),
        thresholdKopecks: raw.thresholdKopecks ?? raw.threshold_kopecks ?? null,
        currentKopecks: Number(raw.currentKopecks ?? raw.current_kopecks) || 0,
        remainingKopecks: Number(raw.remainingKopecks ?? raw.remaining_kopecks) || 0,
        isPreview: Boolean(raw.isPreview ?? raw.is_preview),
    };
}

function normalizeComplementBenefit(raw) {
    if (!raw || typeof raw !== "object") {
        return emptyComplementBenefit();
    }

    return {
        isActive: Boolean(raw.isActive ?? raw.is_active),
        isReached: Boolean(raw.isReached ?? raw.is_reached),
        rollsPerSet: raw.rollsPerSet ?? raw.rolls_per_set ?? null,
        currentRollCount: Number(raw.currentRollCount ?? raw.current_roll_count) || 0,
        entitledSetCount: Number(raw.entitledSetCount ?? raw.entitled_set_count) || 0,
        remainingRollCount: Number(raw.remainingRollCount ?? raw.remaining_roll_count) || 0,
    };
}

/**
 * @param {object|null|undefined} benefitsProgress
 */
export function normalizeBenefitsProgress(benefitsProgress) {
    if (!benefitsProgress || typeof benefitsProgress !== "object") {
        return null;
    }

    return {
        delivery: normalizeMoneyBenefit(benefitsProgress.delivery),
        gift: normalizeMoneyBenefit(benefitsProgress.gift),
        complement: normalizeComplementBenefit(benefitsProgress.complement),
    };
}

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

    const rawLines = Array.isArray(cart.items)
        ? cart.items
        : Array.isArray(cart.lines)
          ? cart.lines
          : [];

    const items = rawLines
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
        .filter(Boolean);

    const itemsSubtotalRubles = roundRubles2(
        Number(cart.items_total_rubles) ||
            items
                .filter((item) => !item.isSystem)
                .reduce((sum, item) => sum + (item.pricing.lineTotalKopecks || 0) / 100, 0),
    );
    const itemsTotalRubles = roundRubles2(
        Number(cart.payable_total_rubles ?? cart.items_total_rubles) || itemsSubtotalRubles,
    );

    return {
        items,
        itemsTotalRubles,
        itemsSubtotalRubles,
    };
}
