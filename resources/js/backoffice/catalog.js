import { defineStore } from "pinia";
import { ref } from "vue";
import { backofficeRequest } from "./api";

const STATUS_LABELS = Object.freeze({
    active: "Активен",
    archived: "В архиве",
});

/**
 * @param {string} status
 */
export function catalogStatusLabel(status) {
    return STATUS_LABELS[status] ?? status;
}

function mapProduct(row) {
    return {
        id: row.id,
        name: row.name ?? "",
        categoryId: row.category_id ?? null,
        price: row.price ?? 0,
        oldPrice: row.old_price ?? null,
        weight: row.weight ?? "—",
        status: row.status ?? "",
        isSystem: Boolean(row.is_system),
        description: row.description ?? "",
    };
}

function mapSet(row) {
    return {
        id: row.id,
        name: row.name ?? "",
        slug: row.slug ?? "—",
        sku: row.sku ?? "—",
        lineCount: row.line_count ?? 0,
        price: row.price ?? 0,
        oldPrice: row.old_price ?? null,
        status: row.status ?? "",
    };
}

export const useCatalog = defineStore("catalog-board", () => {
    const categories = ref([]);
    const products = ref([]);
    const sets = ref([]);
    const tags = ref([]);
    const catalogError = ref("");
    const catalogReady = ref(false);

    function categoryName(categoryId) {
        return categories.value.find((category) => category.id === categoryId)?.name ?? "—";
    }

    function productsInCategory(categoryId) {
        return products.value.filter((product) => product.categoryId === categoryId);
    }

    async function loadCatalog() {
        try {
            const payload = await backofficeRequest("GET", "/api/backoffice/catalog");
            categories.value = Array.isArray(payload.categories)
                ? payload.categories.map((row) => ({
                    id: row.id,
                    name: row.name ?? "",
                    isActive: Boolean(row.is_active),
                    isAccompanying: Boolean(row.is_accompanying),
                }))
                : [];
            products.value = Array.isArray(payload.products) ? payload.products.map(mapProduct) : [];
            sets.value = Array.isArray(payload.sets) ? payload.sets.map(mapSet) : [];
            tags.value = Array.isArray(payload.tags)
                ? payload.tags.map((row) => ({
                    id: row.id,
                    code: row.code ?? "",
                    label: row.label ?? "",
                    color: row.color ?? "",
                    isActive: Boolean(row.is_active),
                }))
                : [];
            catalogError.value = "";
        } catch (error) {
            catalogError.value = error instanceof Error ? error.message : "Не удалось связаться с сервером.";
        } finally {
            catalogReady.value = true;
        }
    }

    loadCatalog();

    return {
        categories,
        products,
        sets,
        tags,
        catalogError,
        catalogReady,
        categoryName,
        productsInCategory,
    };
});
