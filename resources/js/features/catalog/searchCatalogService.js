/**
 * Клиентский поиск по загруженному дереву каталога.
 */

/**
 * @param {unknown} value
 */
function normalizeSearchText(value) {
    return String(value ?? "")
        .toLowerCase()
        .replace(/ё/g, "е")
        .trim();
}

/**
 * @param {object} product
 * @param {string[]} categoryNames
 * @returns {string[]}
 */
function collectSearchableParts(product, categoryNames) {
    const parts = [];

    const push = (value) => {
        const normalized = normalizeSearchText(value);
        if (normalized) {
            parts.push(normalized);
        }
    };

    push(product?.name);
    push(product?.slug);
    push(product?.description);

    const raw = product?.raw;
    if (raw && typeof raw === "object") {
        push(raw.name);
        push(raw.slug);
        push(raw.description);
        push(raw.ingredients);
        push(raw.sku);
        push(raw.weight);
    }

    const tags = Array.isArray(product?.tags) ? product.tags : [];
    for (const tag of tags) {
        push(tag?.label);
        push(tag?.code);
    }

    const lines = Array.isArray(product?.lines) ? product.lines : [];
    for (const line of lines) {
        push(line?.product_name);
    }

    if (product?.nutrition && typeof product.nutrition === "object") {
        const n = product.nutrition;
        push(
            [n.calories, n.proteins, n.fats, n.carbs, n.basis]
                .filter((entry) => entry != null && entry !== "")
                .join(" "),
        );
    }

    for (const categoryName of categoryNames) {
        push(categoryName);
    }

    return [...new Set(parts)];
}

/**
 * @param {Array<{ category?: object, products?: object[] }>} categories
 * @returns {Array<{ product: object, categoryNames: string[] }>}
 */
export function flattenCatalogForSearch(categories) {
    if (!Array.isArray(categories)) {
        return [];
    }

    /** @type {Map<string, { product: object, categoryNames: Set<string> }>} */
    const indexed = new Map();

    for (const entry of categories) {
        const categoryName = String(entry?.category?.name || "").trim();
        const products = Array.isArray(entry?.products) ? entry.products : [];

        for (const product of products) {
            if (!product || product.id == null) {
                continue;
            }

            const key = `${String(product.kind || "product")}:${Number(product.id)}`;
            const existing = indexed.get(key);

            if (existing) {
                if (categoryName) {
                    existing.categoryNames.add(categoryName);
                }
                continue;
            }

            indexed.set(key, {
                product,
                categoryNames: categoryName ? new Set([categoryName]) : new Set(),
            });
        }
    }

    return Array.from(indexed.values()).map((entry) => ({
        product: entry.product,
        categoryNames: Array.from(entry.categoryNames),
    }));
}

/**
 * @param {object} product
 * @param {string} query
 * @param {string[]} parts
 */
function scoreCatalogSearchHit(product, query, parts) {
    const q = normalizeSearchText(query);
    if (!q) {
        return 0;
    }

    const name = normalizeSearchText(product?.name);

    if (name === q) {
        return 100;
    }
    if (name.startsWith(q)) {
        return 80;
    }
    if (name.includes(q)) {
        return 60;
    }

    if (parts.some((part) => part.includes(q))) {
        return 40;
    }

    const tokens = q.split(/\s+/).filter(Boolean);
    if (
        tokens.length > 1
        && tokens.every((token) => parts.some((part) => part.includes(token)))
    ) {
        return 30;
    }

    return 0;
}

/**
 * @param {Array<{ category?: object, products?: object[] }>} categories
 * @param {string} query
 * @returns {object[]}
 */
export function searchCatalogItems(categories, query) {
    const trimmed = String(query ?? "").trim();
    if (!trimmed) {
        return [];
    }

    const hits = flattenCatalogForSearch(categories)
        .map(({ product, categoryNames }) => {
            const parts = collectSearchableParts(product, categoryNames);
            const score = scoreCatalogSearchHit(product, trimmed, parts);

            return { product, score };
        })
        .filter((entry) => entry.score > 0)
        .sort((a, b) => {
            if (b.score !== a.score) {
                return b.score - a.score;
            }

            return String(a.product?.name || "").localeCompare(
                String(b.product?.name || ""),
                "ru",
            );
        });

    return hits.map((entry) => entry.product);
}
