import { isComplementCartLine } from "./normalizeCheckoutCart";

/**
 * @param {object[]|null|undefined} userItems
 * @param {number} productId
 */
function userQtyForProduct(userItems, productId) {
    if (!Array.isArray(userItems)) {
        return 0;
    }

    const line = userItems.find(
        (item) => !item?.isSystem && Number(item?.productId) === productId,
    );

    return Number(line?.qty) || 0;
}

/**
 * @param {object|null|undefined} promoState
 * @param {object[]|null|undefined} userItems
 * @returns {object[]}
 */
function offersFromPromoState(promoState, userItems) {
    const promo = promoState?.complement_promotion;
    if (!promo || typeof promo !== "object" || promo.eligible !== true) {
        return [];
    }

    const candidateItems = Array.isArray(promo.candidate_items) ? promo.candidate_items : [];
    const freeQty = Number(promo.entitled_set_count) || 0;

    return candidateItems
        .map((item) => {
            const productId = Number(item?.id) || 0;
            if (productId <= 0) {
                return null;
            }

            return {
                productId,
                name: item?.name ? String(item.name) : `Товар #${productId}`,
                priceRub: Number(item?.price_rub) || 0,
                freeQty,
                userQty: userQtyForProduct(userItems, productId),
            };
        })
        .filter(Boolean);
}

/**
 * @param {object[]|null|undefined} systemItems
 * @param {object[]|null|undefined} userItems
 * @returns {object[]}
 */
function offersFromSystemLines(systemItems, userItems) {
    if (!Array.isArray(systemItems)) {
        return [];
    }

    return systemItems
        .filter((item) => isComplementCartLine(item))
        .map((item) => ({
            productId: item.productId,
            name: item.productSnapshot?.name
                ? String(item.productSnapshot.name)
                : `Товар #${item.productId}`,
            priceRub: Number(item.productSnapshot?.price) || 0,
            freeQty: Number(item.qty) || 0,
            userQty: userQtyForProduct(userItems, item.productId),
        }));
}

/**
 * @param {{
 *   systemItems?: object[]|null|undefined,
 *   userItems?: object[]|null|undefined,
 *   promoState?: object|null|undefined,
 * }} input
 * @returns {{ productId: number, name: string, priceRub: number, freeQty: number, userQty: number }[]}
 */
export function resolveComplementWizardOffers(input) {
    const fromSystem = offersFromSystemLines(input?.systemItems, input?.userItems);
    if (fromSystem.length > 0) {
        return fromSystem;
    }

    return offersFromPromoState(input?.promoState, input?.userItems);
}
