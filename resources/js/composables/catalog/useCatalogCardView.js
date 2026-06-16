import { computed, unref } from "vue";
import { useCatalogItemDisplay } from "./useCatalogItemDisplay";

function normalizeProductTags(product) {
    const tags =
        (Array.isArray(product?.tags) && product.tags) ||
        (Array.isArray(product?.raw?.tags) && product.raw.tags) ||
        (Array.isArray(product?.raw?.product_tags) && product.raw.product_tags) ||
        [];

    if (!Array.isArray(tags)) return [];

    return tags
        .map((tag) => {
            const code = String(tag?.code || "").trim();
            if (!code) return null;

            return {
                code,
                label: String(tag?.label || code).trim(),
                color: String(tag?.color || "amber").trim().toLowerCase(),
            };
        })
        .filter(Boolean);
}

export function useCatalogCardView(productSource) {
    const product = computed(() => unref(productSource) ?? null);

    const primaryThumb = computed(() => {
        const item = product.value;
        if (Array.isArray(item?.images) && item.images.length) {
            return item.images[0];
        }
        return null;
    });

    const imageSrcset = computed(() => {
        const list = product.value?.imageSrcset;
        if (!Array.isArray(list) || list.length === 0) return null;

        return list
            .map(({ url, width }) => (url && width ? `${url} ${width}w` : null))
            .filter(Boolean)
            .join(", ");
    });

    const primaryTag = computed(() => normalizeProductTags(product.value)[0] ?? null);

    const {
        isSet,
        isProduct,
        setCountLabel,
    } = useCatalogItemDisplay(product);

    return {
        product,
        primaryThumb,
        imageSrcset,
        primaryTag,
        isSet,
        isProduct,
        setCountLabel,
    };
}
