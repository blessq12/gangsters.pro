import { computed, ref, watch } from "vue";
import { useCatalogReadModel } from "../../features/catalog/useCatalogReadModel";
import { useCatalogStore } from "../../stores/catalogStore";

export function useCatalogPageModel() {
    const catalogStore = useCatalogStore();
    const {
        menuSections,
        menuProducts,
        categoryTabs,
        tagTabs,
        loading,
    } = useCatalogReadModel({ autoload: true });

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

    const productSearchQuery = computed({
        get: () => catalogStore.productSearchQuery,
        set: (value) => catalogStore.setProductSearchQuery(value),
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
        catalogStore.productSearchQuery.trim()
            ? "Ничего не нашли по этому запросу. Попробуй другое слово или сбрось поиск."
            : "Тут пока тихо. Выберите другую категорию.",
    );

    const clearSearch = () => catalogStore.setProductSearchQuery("");

    return {
        showProductDetailModal,
        openProductDetail,
        selectedCategoryId,
        selectedTag,
        productSearchQuery,
        desktopCardsPerRow,
        mobileCardViewMode,
        menuSections,
        menuProducts,
        categoryTabs,
        tagTabs,
        selectedProduct,
        loading,
        catalogEmptyMessage,
        clearSearch,
    };
}
