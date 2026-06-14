import { fetchCatalogRequest } from "../../api/catalogApi";
import {
    normalizeCatalogCategory,
    normalizeCatalogItem,
} from "../../domain/catalog/catalogMappers";

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

    return mapped.sort(
        (a, b) => (a.category.sort_order ?? 0) - (b.category.sort_order ?? 0),
    );
}
