import { computed, unref } from "vue";
import { useCatalogStore } from "../../stores/catalogStore";

function pickSetLinesSource(product) {
    const topLevel = Array.isArray(product?.lines) ? product.lines : [];
    const rawLevel = Array.isArray(product?.raw?.lines) ? product.raw.lines : [];

    if (topLevel.length === 0) {
        return rawLevel;
    }

    if (rawLevel.length === 0) {
        return topLevel;
    }

    const rawNameById = new Map(
        rawLevel
            .map((line) => {
                const productId = line?.product_id ?? line?.productId ?? null;
                if (productId == null) return null;

                const name = String(
                    line?.product_name || line?.productName || line?.name || "",
                ).trim();

                return name ? [Number(productId), name] : null;
            })
            .filter(Boolean),
    );

    return topLevel.map((line) => {
        const productId = line?.product_id ?? line?.productId ?? null;
        const topName = String(
            line?.product_name || line?.productName || line?.name || "",
        ).trim();
        const rawName = productId != null ? rawNameById.get(Number(productId)) : "";

        return {
            ...line,
            product_id: productId,
            product_name: topName || rawName || null,
        };
    });
}

function resolveLineProductName(line, nameById) {
    const productId = line?.product_id ?? line?.productId ?? null;
    if (productId == null) return null;

    const explicitName = String(
        line?.product_name || line?.productName || line?.name || "",
    ).trim();
    if (explicitName) return explicitName;

    const catalogName = nameById.get(Number(productId));
    if (catalogName) return catalogName;

    return `Товар #${productId}`;
}

export function useCatalogItemDisplay(productSource) {
    const catalogStore = useCatalogStore();
    const product = computed(() => unref(productSource) ?? null);

    const catalogProductNameById = computed(() => {
        const map = new Map();

        catalogStore.flatProducts.forEach((item) => {
            if (item?.kind !== "product" || item.id == null) return;

            const name = String(item.name || "").trim();
            if (name) {
                map.set(Number(item.id), name);
            }
        });

        return map;
    });

    const isSet = computed(() => product.value?.kind === "set");
    const isProduct = computed(() => !isSet.value);

    const setLines = computed(() => {
        const lines = pickSetLinesSource(product.value);

        return lines
            .map((line) => {
                if (!line || typeof line !== "object") return null;

                const productId = line.product_id ?? line.productId ?? null;
                if (productId == null) return null;

                return {
                    productId: Number(productId),
                    quantity: Number(line.quantity) || 0,
                    productName: resolveLineProductName(
                        line,
                        catalogProductNameById.value,
                    ),
                };
            })
            .filter(Boolean);
    });

    const setPositionCount = computed(() => setLines.value.length);

    const setItemsCount = computed(() =>
        setLines.value.reduce((sum, line) => sum + (line.quantity || 0), 0),
    );

    const setCountLabel = computed(() => {
        const positions = setPositionCount.value;
        if (positions <= 0) return null;
        return `${positions} поз.`;
    });

    const description = computed(() => {
        const raw =
            product.value?.description ??
            product.value?.raw?.description;
        if (typeof raw !== "string") return null;

        const trimmed = raw.trim();
        return trimmed !== "" ? trimmed : null;
    });

    const hasSetComposition = computed(
        () => isSet.value && setLines.value.length > 0,
    );

    return {
        isSet,
        isProduct,
        setLines,
        setPositionCount,
        setItemsCount,
        setCountLabel,
        description,
        hasSetComposition,
    };
}
