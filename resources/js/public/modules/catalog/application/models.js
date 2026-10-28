import { computed, inject, onMounted, ref, unref, watch } from "vue";
import { storeToRefs } from "pinia";
import { useCatalogStore } from "../store";
import { getProductNutritionNumbers, hasProductNutrition } from "../domain/productView";
import { CatalogSearchActionSourceKey } from "./search";
import { useCheckoutSession } from "../../checkout/application/session";
import { useCheckoutStore } from "../../checkout/store";
import { useFavoritesStore } from "../../client/store/favoritesStore";
import { DOMAIN_EVENTS, emitDomainEvent } from "../../../platform/domainEvents";

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

export function useProductMeta(productSource) {
    const product = computed(() => unref(productSource) ?? null);

    const nutrition = computed(() => getProductNutritionNumbers(product.value));
    const hasNutrition = computed(() => hasProductNutrition(product.value));

    const ingredients = computed(() => {
        const raw = product.value?.raw?.ingredients;
        if (!Array.isArray(raw)) return [];

        return raw
            .map((item) => {
                if (typeof item === "string") {
                    const name = item.trim();
                    return name ? { name, amount: null, unit: "", isAllergen: false } : null;
                }

                if (!item || (!item.name && !item.amount)) return null;

                return {
                    name: item.name || "",
                    amount: item.amount,
                    unit: item.unit || "",
                    isAllergen: Boolean(item.is_allergen),
                };
            })
            .filter(Boolean);
    });

    const hasIngredients = computed(() => ingredients.value.length > 0);

    const ingredientsText = computed(() =>
        ingredients.value
            .map((i) => i?.name)
            .filter(Boolean)
            .join(", "),
    );

    return {
        nutrition,
        hasNutrition,
        ingredients,
        hasIngredients,
        ingredientsText,
    };
}

export function useCatalogPageModel() {
    const catalogStore = useCatalogStore();
    const {
        menuSections,
        menuProducts,
        categoryTabs,
        tagTabs,
        loading,
    } = storeToRefs(catalogStore);

    onMounted(() => {
        if (!catalogStore.hasLoaded && !catalogStore.loading) {
            void catalogStore.fetchAll();
        }
    });

    const showProductDetailModal = ref(false);

    const openProductDetail = (product) => {
        catalogStore.setSelectedProduct(product);
        showProductDetailModal.value = true;
    };

    watch(showProductDetailModal, (isOpen) => {
        if (!isOpen) {
            catalogStore.setSelectedProduct(null);
        }
    });

    const selectedCategoryId = computed({
        get: () => catalogStore.selectedCategoryId,
        set: (value) => catalogStore.setSelectedCategoryId(value),
    });

    const selectedTag = computed({
        get: () => catalogStore.selectedTag,
        set: (value) => catalogStore.setSelectedTag(value),
    });
    const desktopCardsPerRow = computed({
        get: () => catalogStore.desktopCardsPerRow,
        set: (value) => catalogStore.setDesktopCardsPerRow(value),
    });
    const mobileCardViewMode = computed({
        get: () => catalogStore.mobileCardViewMode,
        set: (value) => catalogStore.setMobileCardViewMode(value),
    });

    const selectedProduct = computed(() => catalogStore.selectedProduct);

    const catalogEmptyMessage = computed(() =>
        "Тут пока тихо. Выберите другую категорию.",
    );

    return {
        showProductDetailModal,
        openProductDetail,
        selectedCategoryId,
        selectedTag,
        desktopCardsPerRow,
        mobileCardViewMode,
        menuSections,
        menuProducts,
        categoryTabs,
        tagTabs,
        selectedProduct,
        loading,
        catalogEmptyMessage,
    };
}

export function useProductActions(productSource) {
    const checkoutStore = useCheckoutStore();
    const cartReadModel = useCheckoutSession();
    const favoritesStore = useFavoritesStore();
    const searchActionSource = inject(CatalogSearchActionSourceKey, null);
    const cartEventSource = searchActionSource ?? "catalog";

    const product = computed(() => unref(productSource) ?? null);
    const productId = computed(() => product.value?.id ?? null);

    const qtyInCart = computed(() =>
        productId.value ? cartReadModel.quantityByProduct(productId.value) : 0,
    );

    const isFav = computed(() =>
        productId.value ? favoritesStore.isFavorite(productId.value) : false,
    );

    const addToCart = async (qty = 1) => {
        if (!productId.value) return;
        emitDomainEvent(DOMAIN_EVENTS.CART_ADD_REQUESTED, {
            product: product.value,
            qty,
            source: cartEventSource,
        });
        await checkoutStore.addToCart(product.value, qty);
    };

    const incrementCart = async () => {
        if (!productId.value) return;
        emitDomainEvent(DOMAIN_EVENTS.CART_INCREMENT_REQUESTED, {
            productId: productId.value,
            source: cartEventSource,
        });
        await checkoutStore.incrementCart(productId.value);
    };

    const decrementCart = async () => {
        if (!productId.value) return;
        await checkoutStore.decrementCart(productId.value);
    };

    const toggleFavorite = () => {
        if (!productId.value) return;
        const adding = !isFav.value;
        if (adding) {
            emitDomainEvent(DOMAIN_EVENTS.FAVORITE_ADD_REQUESTED, {
                product: product.value,
                source: cartEventSource,
            });
        }
        void favoritesStore.toggleFavorite(product.value);
    };

    return {
        productId,
        qtyInCart,
        isFav,
        addToCart,
        incrementCart,
        decrementCart,
        toggleFavorite,
    };
}
