/**
 * Публичный URL файла каталога (storage).
 */
export function toCatalogStorageUrl(path) {
    if (!path || typeof path !== "string") return null;
    let url = String(path);
    if (url.startsWith("products/") || url.startsWith("uploads/")) {
        url = `/storage/${url}`;
    } else if (!url.startsWith("/")) {
        url = `/storage/${url.replace(/^\/+/, "")}`;
    }
    return url;
}

/**
 * Слайды для галереи модалки: из raw.images (варианты) или из нормализованного product.images.
 * @returns {{ url: string }[]}
 */
export function buildProductGallerySlides(product) {
    if (!product) return [];
    const rawImages = product.raw?.images;
    if (Array.isArray(rawImages) && rawImages.length) {
        return rawImages
            .map((img) => {
                const variants = img?.variants || [];
                const bySize = (s) =>
                    variants.find((v) => v?.size === s && v?.path);
                const v =
                    bySize("large") ||
                    bySize("medium") ||
                    bySize("thumb") ||
                    variants[0];
                const url = v ? toCatalogStorageUrl(v.path) : null;
                return url ? { url } : null;
            })
            .filter(Boolean);
    }
    if (Array.isArray(product.images) && product.images.length) {
        return product.images
            .map((url) =>
                typeof url === "string"
                    ? { url }
                    : url?.url
                      ? { url: url.url }
                      : null,
            )
            .filter(Boolean);
    }
    return [];
}

/**
 * Числа КБЖУ для отображения (карточка, модалка).
 * @returns {{ calories: number, proteins: number, fats: number, carbs: number } | null}
 */
export function getProductNutritionNumbers(product) {
    const n = product?.nutrition ?? product?.raw?.nutrition;
    if (!n || typeof n !== "object") return null;
    return {
        calories: Number(n.calories) || 0,
        proteins: Number(n.proteins) || 0,
        fats: Number(n.fats) || 0,
        carbs: Number(n.carbs) || 0,
    };
}

export function hasProductNutrition(product) {
    const n = getProductNutritionNumbers(product);
    return Boolean(
        n && (n.calories || n.proteins || n.fats || n.carbs),
    );
}
