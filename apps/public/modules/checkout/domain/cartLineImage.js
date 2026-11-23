/**
 * URL миниатюры строки корзины: снимок линии или каталог филиала.
 *
 * @param {{ productSnapshot?: { imageUrl?: string|null }, productId?: number }|null|undefined} item
 * @param {{
 *   flatProducts?: Array<{ id?: number, imageUrl?: string|null, images?: string[] }>,
 *   accompanyingCategories?: Array<{ products?: Array<{ id?: number, imageUrl?: string|null, images?: string[] }> }>,
 *   complementProducts?: Array<{ id?: number, imageUrl?: string|null, images?: string[] }>,
 * }|null|undefined} catalog
 * @returns {string|null}
 */
export function resolveCartLineImageUrl(item, catalog = null) {
    const fromSnapshot = item?.productSnapshot?.imageUrl;
    if (typeof fromSnapshot === "string" && fromSnapshot.trim() !== "") {
        return fromSnapshot.trim();
    }

    const productId = Number(item?.productId);
    if (!Number.isFinite(productId) || productId < 1 || !catalog) {
        return null;
    }

    const pools = [
        ...(Array.isArray(catalog.flatProducts) ? catalog.flatProducts : []),
        ...(Array.isArray(catalog.complementProducts) ? catalog.complementProducts : []),
    ];
    for (const category of Array.isArray(catalog.accompanyingCategories)
        ? catalog.accompanyingCategories
        : []) {
        if (Array.isArray(category?.products)) {
            pools.push(...category.products);
        }
    }

    const product = pools.find((entry) => Number(entry?.id) === productId);
    if (!product) {
        return null;
    }

    if (typeof product.imageUrl === "string" && product.imageUrl.trim() !== "") {
        return product.imageUrl.trim();
    }

    const first = Array.isArray(product.images) ? product.images[0] : null;
    return typeof first === "string" && first.trim() !== "" ? first.trim() : null;
}

/**
 * @param {unknown} product
 * @returns {string|null}
 */
export function productImageUrl(product) {
    if (!product || typeof product !== "object") {
        return null;
    }
    if (typeof product.imageUrl === "string" && product.imageUrl.trim() !== "") {
        return product.imageUrl.trim();
    }
    const first = Array.isArray(product.images) ? product.images[0] : null;
    return typeof first === "string" && first.trim() !== "" ? first.trim() : null;
}
