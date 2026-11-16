import {
    normalizeCatalogCategory,
    normalizeCatalogItem,
} from "./catalogMappers";

function mapCategoryNode(item) {
    const rawItems = Array.isArray(item?.items) ? item.items : [];
    return {
        category: normalizeCatalogCategory(item?.category),
        products: rawItems
            .map((row) => normalizeCatalogItem(row))
            .filter(Boolean)
            .filter(
                (product) =>
                    !Boolean(product?.promotion_meta?.complement_set)
                    && !Boolean(product?.raw?.promotion_meta?.complement_set),
            ),
    };
}

function buildProductNameIndex(categoryNodes) {
    const nameById = new Map();

    for (const entry of categoryNodes) {
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

function enrichCatalogSetLineNames(categoryNodes) {
    const nameById = buildProductNameIndex(categoryNodes);

    for (const entry of categoryNodes) {
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

    return categoryNodes;
}

function sortCategoryNodes(nodes) {
    return [...nodes].sort(
        (a, b) => (a.category.sort_order ?? 0) - (b.category.sort_order ?? 0),
    );
}

export function mapCatalogTreeFromPayload(payload) {
    const menuNodes = sortCategoryNodes(
        (Array.isArray(payload?.categories) ? payload.categories : []).map(
            mapCategoryNode,
        ),
    );

    const accompanyingNodes = sortCategoryNodes(
        (
            Array.isArray(payload?.accompanying_categories)
                ? payload.accompanying_categories
                : []
        ).map(mapCategoryNode),
    );

    const nameIndexSource = [...menuNodes, ...accompanyingNodes];
    enrichCatalogSetLineNames(nameIndexSource);

    const complementProducts = (
        Array.isArray(payload?.complement_products)
            ? payload.complement_products
            : []
    )
        .map((row) => normalizeCatalogItem(row))
        .filter(Boolean);

    return {
        categories: menuNodes,
        accompanyingCategories: accompanyingNodes,
        complementProducts,
    };
}

