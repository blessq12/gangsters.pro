import { toCatalogStorageUrl } from "../../utils/catalog/productMedia";

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

export function normalizeCatalogProduct(apiProduct) {
    if (!apiProduct || typeof apiProduct !== "object") return null;

    const id = apiProduct.id ?? null;
    if (!id) return null;

    const rawPrice = Number(apiProduct.price);
    const price = Number.isFinite(rawPrice) ? Math.round(rawPrice) : 0;

    let imageUrl = null;
    let imageSrcset = [];
    if (Array.isArray(apiProduct.images) && apiProduct.images.length) {
        const firstImage = apiProduct.images[0];
        if (firstImage && Array.isArray(firstImage.variants)) {
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
                imageSrcset = ordered
                    .map((v) => ({
                        url: toCatalogStorageUrl(v.path),
                        width:
                            Number(v.width) ||
                            (v.size === "thumb" ? 300 : v.size === "medium" ? 800 : 1200),
                    }))
                    .filter((e) => e.url);

                imageUrl = toCatalogStorageUrl(ordered[ordered.length - 1].path);
            }
        }
    }

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

