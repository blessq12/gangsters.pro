/** Slug категории напитков для upsell-шага визарда. */
export const CHECKOUT_DRINKS_CATEGORY_SLUG = "napitki";

/**
 * @param {unknown} categories
 * @returns {object[]}
 */
export function resolveCheckoutDrinksProducts(categories) {
    const list = Array.isArray(categories) ? categories : [];
    const entry = list.find(
        (row) => String(row?.category?.slug || "") === CHECKOUT_DRINKS_CATEGORY_SLUG,
    );
    const products = Array.isArray(entry?.products) ? entry.products : [];

    return products.filter((product) => product?.id != null);
}

/**
 * @param {unknown} categories
 */
export function isCheckoutDrinksStepAvailable(categories) {
    return resolveCheckoutDrinksProducts(categories).length > 0;
}
