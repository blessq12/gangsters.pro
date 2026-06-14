import { toCatalogStorageUrl } from "../../utils/catalog/productMedia";
import { roundRubles2 } from "../../utils/moneyFormat";

function extractWeightGrams(text) {
    if (!text || typeof text !== "string") return null;

    const raw = text.replace(",", ".").toLowerCase();
    const match = raw.match(/(\d+(?:\.\d+)?)\s*(г|гр|грамм|g)\b/iu);
    if (!match) return null;

    const grams = Number(match[1]);
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
 * @param {unknown} apiImages
 * @returns {{ imageUrl: string|null, imageSrcset: Array<{ url: string, width: number }> }}
 */
function normalizeCatalogImages(apiImages) {
    if (!Array.isArray(apiImages) || apiImages.length === 0) {
        return { imageUrl: null, imageSrcset: [] };
    }

    const firstImage = apiImages[0];
    if (!firstImage || !Array.isArray(firstImage.variants)) {
        return { imageUrl: null, imageSrcset: [] };
    }

    const variants = firstImage.variants;
    const bySize = (size) =>
        variants.find(
            (v) => v && typeof v === "object" && v.size === size && v.path,
        );
    const thumb = bySize("thumb");
    const medium = bySize("medium");
    const large = bySize("large");
    const ordered = [thumb, medium, large].filter(Boolean);

    if (!ordered.length) {
        return { imageUrl: null, imageSrcset: [] };
    }

    const imageSrcset = ordered
        .map((v) => ({
            url: toCatalogStorageUrl(v.path),
            width:
                Number(v.width) ||
                (v.size === "thumb" ? 300 : v.size === "medium" ? 800 : 1200),
        }))
        .filter((entry) => entry.url);

    const imageUrl = toCatalogStorageUrl(ordered[ordered.length - 1].path);

    return {
        imageUrl: imageUrl || null,
        imageSrcset,
    };
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
    const { imageUrl, imageSrcset } = normalizeCatalogImages(apiProduct.images);

    const derivedWeight =
        extractWeightGrams(apiProduct.weight ?? apiProduct.description) ||
        extractWeightGrams(apiProduct.name);

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

    return {
        id,
        kind: "product",
        slug: String(apiProduct.slug || "").trim(),
        name: apiProduct.name || "",
        price,
        weight: derivedWeight,
        tags: normalizeProductTags(apiProduct.tags),
        images: imageUrl ? [imageUrl] : [],
        imageSrcset,
        nutrition,
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
    const { imageUrl, imageSrcset } = normalizeCatalogImages(apiSet.images);

    const derivedWeight =
        extractWeightGrams(apiSet.description) || extractWeightGrams(apiSet.name);

    const lines = Array.isArray(apiSet.lines)
        ? apiSet.lines
              .map((line) => {
                  if (!line || typeof line !== "object") return null;
                  const productId = line.product_id ?? null;
                  if (productId == null) return null;
                  return {
                      product_id: productId,
                      quantity: Number(line.quantity) || 0,
                  };
              })
              .filter(Boolean)
        : [];

    return {
        id,
        kind: "set",
        slug: String(apiSet.slug || "").trim(),
        name: apiSet.name || "",
        price,
        weight: derivedWeight,
        tags: normalizeProductTags(apiSet.tags),
        images: imageUrl ? [imageUrl] : [],
        imageSrcset,
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
        raw: category,
    };
}
