/**
 * Upsell-шаг: есть хотя бы один товар/набор в сопутствующих категориях.
 *
 * @param {unknown} accompanyingCategories
 */
export function isCheckoutUpsellStepAvailable(accompanyingCategories) {
    const nodes = Array.isArray(accompanyingCategories)
        ? accompanyingCategories
        : [];

    return nodes.some(
        (entry) =>
            Array.isArray(entry?.products) && entry.products.length > 0,
    );
}
