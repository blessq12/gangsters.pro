import { fetchCatalogRequest } from "../../api/catalogApi";
import {
    normalizeCatalogCategory,
    normalizeCatalogItem,
} from "../../domain/catalog/catalogMappers";

function buildProductNameIndex(categories) {
    const nameById = new Map();

    for (const entry of categories) {
        for (const product of entry?.products || []) {
            if (product?.kind !== "product" || product.id == null) continue;

            const name = String(product.name || "").trim();
            if (name) {
                nameById.set(Number(product.id), name);
            }
        }
    }

    return nameById;
}

function enrichSetLineName(line, nameById) {
    if (!line || typeof line !== "object") return line;

    const productId = line.product_id ?? line.productId ?? null;
    if (productId == null) return line;

    const apiName = String(line.product_name || line.productName || line.name || "").trim();
    const resolvedName = apiName || nameById.get(Number(productId)) || null;

    return {
        ...line,
        product_id: productId,
        product_name: resolvedName,
    };
}

function enrichCatalogSetLineNames(categories) {
    const nameById = buildProductNameIndex(categories);

    for (const entry of categories) {
        for (const product of entry?.products || []) {
            if (product?.kind !== "set") continue;

            if (Array.isArray(product.lines)) {
                product.lines = product.lines.map((line) =>
                    enrichSetLineName(line, nameById),
                );
            }

            if (product.raw && Array.isArray(product.raw.lines)) {
                product.raw = {
                    ...product.raw,
                    lines: product.raw.lines.map((line) =>
                        enrichSetLineName(line, nameById),
                    ),
                };
            }
        }
    }

    return categories;
}

export async function fetchCatalogTree() {
    const payload = await fetchCatalogRequest();
    const rawCategories = Array.isArray(payload?.categories)
        ? payload.categories
        : [];

    const mapped = rawCategories.map((item) => {
        const rawItems = Array.isArray(item?.items) ? item.items : [];
        return {
            category: normalizeCatalogCategory(item?.category),
            products: rawItems
                .map((row) => normalizeCatalogItem(row))
                .filter(Boolean),
        };
    });

    const sorted = mapped.sort(
        (a, b) => (a.category.sort_order ?? 0) - (b.category.sort_order ?? 0),
    );

    return enrichCatalogSetLineNames(sorted);
}
