import { normalizeImageFocus, toCatalogStorageUrl } from "./productView";
import { roundRubles2 } from "../../../platform/moneyFormat";

/**
 * Вес из API (граммы). null / 0 → null.
 * @param {unknown} weight
 * @returns {number|null}
 */
export function parseCatalogWeightGrams(weight) {
    if (weight == null || weight === "") return null;
    const grams = Number(weight);
    if (!Number.isFinite(grams) || grams <= 0) return null;
    return Math.round(grams);
}

function normalizeProductTags(rawTags) {
    if (!Array.isArray(rawTags)) return [];

    return rawTags
        .map((tag) => {
            const code = String(tag?.code || "").trim();
            if (!code) return null;

            return {
                code,
                label: String(tag?.label || code).trim(),
                color: String(tag?.color || "amber").trim(),
            };
        })
        .filter(Boolean);
}

/**
 * @param {unknown} price
 * @returns {number}
 */
export function parseCatalogPriceRubles(price) {
    if (price == null) return 0;
    if (typeof price === "number" && Number.isFinite(price)) {
        return roundRubles2(price);
    }
    if (typeof price === "object" && price.amount != null) {
        const n = Number(price.amount);
        return Number.isFinite(n) ? roundRubles2(n) : 0;
    }
    return 0;
}

/**
 * Опциональная цена из API. null / 0 → null.
 * @param {unknown} price
 * @returns {number|null}
 */
export function parseOptionalCatalogPriceRubles(price) {
    if (price == null) return null;
    const amount = parseCatalogPriceRubles(price);
    return amount > 0 ? amount : null;
}

/**
 * Старая цена для витрины: только если строго больше актуальной.
 * @param {number} price
 * @param {number|null|undefined} oldPrice
 * @returns {number|null}
 */
export function resolveDisplayOldPrice(price, oldPrice) {
    if (oldPrice == null) return null;
    const current = Number(price) || 0;
    const previous = Number(oldPrice);
    if (!Number.isFinite(previous) || previous <= 0 || previous <= current) {
        return null;
    }
    return roundRubles2(previous);
}

/**
 * @param {unknown} apiImages
 * @returns {{
 *   imageUrl: string|null,
 *   imageSrcset: Array<{ url: string, width: number }>,
 *   imageFocus: { x: number, y: number }
 * }}
 */
function normalizeCatalogImages(apiImages) {
    const empty = {
        imageUrl: null,
        imageSrcset: [],
        imageFocus: { x: 50, y: 50 },
    };

    if (!Array.isArray(apiImages) || apiImages.length === 0) {
        return empty;
    }

    const firstImage = apiImages[0];
    if (!firstImage || typeof firstImage !== "object") {
        return empty;
    }

    const imageFocus = normalizeImageFocus(firstImage.focus);

    if (Array.isArray(firstImage.variants)) {
        const variants = firstImage.variants;
        const bySize = (size) =>
            variants.find(
                (v) => v && typeof v === "object" && v.size === size && v.path,
            );
        const thumb = bySize("thumb");
        const medium = bySize("medium");
        const large = bySize("large");
        const ordered = [thumb, medium, large].filter(Boolean);

        if (ordered.length) {
            const imageSrcset = ordered
                .map((v) => ({
                    url: toCatalogStorageUrl(v.path),
                    width:
                        Number(v.width) ||
                        (v.size === "thumb"
                            ? 300
                            : v.size === "medium"
                              ? 800
                              : 1200),
                }))
                .filter((entry) => entry.url);

            const imageUrl = toCatalogStorageUrl(
                ordered[ordered.length - 1].path,
            );

            return {
                imageUrl: imageUrl || null,
                imageSrcset,
                imageFocus,
            };
        }
    }

    const fallbackPath =
        typeof firstImage.path === "string" ? firstImage.path.trim() : "";
    if (fallbackPath !== "") {
        const imageUrl = toCatalogStorageUrl(fallbackPath);

        return {
            imageUrl,
            imageSrcset: imageUrl ? [{ url: imageUrl, width: 800 }] : [],
            imageFocus,
        };
    }

    return empty;
}

/**
 * @param {unknown} apiProduct
 * @returns {object|null}
 */
export function normalizeCatalogProduct(apiProduct) {
    if (!apiProduct || typeof apiProduct !== "object") return null;

    const id = apiProduct.id ?? null;
    if (!id) return null;

    const price = parseCatalogPriceRubles(apiProduct.price);
    const oldPrice = resolveDisplayOldPrice(
        price,
        parseOptionalCatalogPriceRubles(
            apiProduct.old_price ?? apiProduct.oldPrice,
        ),
    );
    const { imageUrl, imageSrcset, imageFocus } = normalizeCatalogImages(
        apiProduct.images,
    );
    const weight = parseCatalogWeightGrams(apiProduct.weight);

    const nutrition =
        apiProduct.nutrition && typeof apiProduct.nutrition === "object"
            ? {
                  calories: Number(apiProduct.nutrition.calories) || 0,
                  proteins: Number(apiProduct.nutrition.proteins) || 0,
                  fats: Number(apiProduct.nutrition.fats) || 0,
                  carbs: Number(apiProduct.nutrition.carbs) || 0,
                  basis: apiProduct.nutrition.basis || "per_100g",
              }
            : null;

    const paidTwinRaw = apiProduct.paid_twin ?? apiProduct.paidTwin ?? null;
    const paidTwin =
        paidTwinRaw && typeof paidTwinRaw === "object"
            ? normalizeCatalogProduct(paidTwinRaw)
            : null;
    const paidTwinProductIdRaw =
        apiProduct.paid_twin_product_id ?? apiProduct.paidTwinProductId ?? null;
    const paidTwinProductId = Number(paidTwinProductIdRaw);
    const resolvedPaidTwinProductId =
        Number.isFinite(paidTwinProductId) && paidTwinProductId > 0
            ? paidTwinProductId
            : paidTwin?.id != null
              ? Number(paidTwin.id)
              : null;

    return {
        id,
        kind: "product",
        slug: String(apiProduct.slug || "").trim(),
        name: apiProduct.name || "",
        price,
        oldPrice,
        weight,
        tags: normalizeProductTags(apiProduct.tags),
        images: imageUrl ? [imageUrl] : [],
        imageSrcset,
        imageFocus,
        nutrition,
        paidTwinProductId:
            Number.isFinite(resolvedPaidTwinProductId) &&
            resolvedPaidTwinProductId > 0
                ? resolvedPaidTwinProductId
                : null,
        paidTwin,
        raw: apiProduct,
    };
}

/**
 * @param {unknown} apiSet
 * @returns {object|null}
 */
export function normalizeCatalogSet(apiSet) {
    if (!apiSet || typeof apiSet !== "object") return null;

    const id = apiSet.id ?? null;
    if (!id) return null;

    const price = parseCatalogPriceRubles(apiSet.price);
    const oldPrice = resolveDisplayOldPrice(
        price,
        parseOptionalCatalogPriceRubles(apiSet.old_price ?? apiSet.oldPrice),
    );
    const { imageUrl, imageSrcset, imageFocus } = normalizeCatalogImages(
        apiSet.images,
    );

    const lines = Array.isArray(apiSet.lines)
        ? apiSet.lines
              .map((line) => {
                  if (!line || typeof line !== "object") return null;
                  const productId = line.product_id ?? line.productId ?? null;
                  if (productId == null) return null;
                  const productName = String(
                      line.product_name || line.productName || line.name || "",
                  ).trim();
                  return {
                      product_id: productId,
                      quantity: Number(line.quantity) || 0,
                      product_name: productName || null,
                  };
              })
              .filter(Boolean)
        : [];

    const description =
        typeof apiSet.description === "string" && apiSet.description.trim()
            ? apiSet.description.trim()
            : null;

    return {
        id,
        kind: "set",
        slug: String(apiSet.slug || "").trim(),
        name: apiSet.name || "",
        price,
        oldPrice,
        weight: parseCatalogWeightGrams(apiSet.weight),
        description,
        tags: normalizeProductTags(apiSet.tags),
        images: imageUrl ? [imageUrl] : [],
        imageSrcset,
        imageFocus,
        nutrition: null,
        lines,
        raw: apiSet,
    };
}

/**
 * @param {unknown} apiItem
 * @returns {object|null}
 */
export function normalizeCatalogItem(apiItem) {
    if (!apiItem || typeof apiItem !== "object") return null;

    const kind = String(apiItem.kind || "product").toLowerCase();
    if (kind === "set") {
        return normalizeCatalogSet(apiItem);
    }

    return normalizeCatalogProduct(apiItem);
}

export function normalizeCatalogCategory(apiCategory) {
    const category = apiCategory || {};
    return {
        id: category.id ?? null,
        name: category.name || "",
        slug: category.slug || "",
        sort_order: category.sort_order ?? null,
        is_active: Boolean(category.is_active),
        is_accompanying: Boolean(category.is_accompanying),
        raw: category,
    };
}
