import { selectedGiftCartLine } from "../../domain/order/normalizeCheckoutCart";

/**
 * @param {{ eligible?: boolean, phase?: string|null, selectedProductId?: number|null }|null|undefined} giftCta
 */
export function isGiftSelectionRequired(giftCta) {
    if (!giftCta || typeof giftCta !== "object" || !giftCta.eligible) {
        return false;
    }

    if (Number(giftCta.selectedProductId) > 0 || giftCta.phase === "selected") {
        return false;
    }

    return true;
}

/**
 * @param {{ gift_promotion?: { eligible?: boolean, phase?: string, selected_product_id?: number|null } }|null|undefined} promoState
 */
export function isGiftSelectionRequiredFromPromoState(promoState) {
    const giftPromotion = promoState?.gift_promotion;
    if (!giftPromotion || typeof giftPromotion !== "object" || giftPromotion.eligible !== true) {
        return false;
    }

    if (
        Number(giftPromotion.selected_product_id) > 0
        || giftPromotion.phase === "selected"
    ) {
        return false;
    }

    return true;
}

/**
 * @param {{
 *   giftCta?: object|null,
 *   promoState?: object|null,
 *   wizardMissingBlocks?: string[],
 *   cartItems?: object[],
 *   giftSummary?: { productId?: number|null }|null,
 * }} input
 */
export function resolveGiftSelectionRequired({
    giftCta = null,
    promoState = null,
    wizardMissingBlocks = [],
    cartItems = [],
    giftSummary = null,
}) {
    if (selectedGiftCartLine(cartItems)) {
        return false;
    }

    if (Number(giftSummary?.productId) > 0) {
        return false;
    }

    const selectedFromCta = Number(giftCta?.selectedProductId) || 0;
    const selectedFromPromo = Number(promoState?.gift_promotion?.selected_product_id) || 0;
    if (selectedFromCta > 0 || selectedFromPromo > 0) {
        return false;
    }

    if (giftCta?.phase === "selected" || promoState?.gift_promotion?.phase === "selected") {
        return false;
    }

    if (wizardMissingBlocks.includes("gift")) {
        return true;
    }

    return isGiftSelectionRequired(giftCta) || isGiftSelectionRequiredFromPromoState(promoState);
}
