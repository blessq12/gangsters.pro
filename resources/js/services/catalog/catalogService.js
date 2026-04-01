import { fetchCatalogRequest } from "../../api/catalogApi";
import {
    normalizeCatalogCategory,
    normalizeCatalogProduct,
} from "../../domain/catalog/catalogMappers";

export async function fetchCatalogTree() {
    const payload = await fetchCatalogRequest();
    const rawCategories = Array.isArray(payload?.categories) ? payload.categories : [];

    const mapped = rawCategories.map((item) => {
        const products = Array.isArray(item?.products) ? item.products : [];
        return {
            category: normalizeCatalogCategory(item?.category),
            products: products.map((p) => normalizeCatalogProduct(p)).filter(Boolean),
        };
    });

    return mapped.sort(
        (a, b) => (a.category.sort_order ?? 0) - (b.category.sort_order ?? 0),
    );
}

