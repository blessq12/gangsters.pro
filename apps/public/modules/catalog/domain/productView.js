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
 * @param {unknown} rawFocus
 * @returns {{ x: number, y: number }}
 */
export function normalizeImageFocus(rawFocus) {
    const fallback = { x: 50, y: 50 };
    if (!rawFocus || typeof rawFocus !== "object") {
        return fallback;
    }

    const x = Number(rawFocus.x);
    const y = Number(rawFocus.y);
    if (!Number.isFinite(x) || !Number.isFinite(y)) {
        return fallback;
    }
    if (x < 0 || x > 100 || y < 0 || y > 100) {
        return fallback;
    }

    return { x, y };
}

/**
 * CSS object-position из focus {x,y}.
 * @param {unknown} rawFocus
 * @returns {string}
 */
export function focusToObjectPosition(rawFocus) {
    const focus = normalizeImageFocus(rawFocus);
    return `${focus.x}% ${focus.y}%`;
}

/**
 * Слайды для галереи модалки: из raw.images (варианты + focus) или из нормализованного product.images.
 * @returns {{ url: string, objectPosition: string }[]}
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
                if (!url) return null;

                return {
                    url,
                    objectPosition: focusToObjectPosition(img?.focus),
                };
            })
            .filter(Boolean);
    }
    if (Array.isArray(product.images) && product.images.length) {
        const fallbackPosition = focusToObjectPosition(product.imageFocus);
        return product.images
            .map((entry) => {
                if (typeof entry === "string") {
                    return { url: entry, objectPosition: fallbackPosition };
                }
                if (entry?.url) {
                    return {
                        url: entry.url,
                        objectPosition: focusToObjectPosition(
                            entry.focus ?? product.imageFocus,
                        ),
                    };
                }
                return null;
            })
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
