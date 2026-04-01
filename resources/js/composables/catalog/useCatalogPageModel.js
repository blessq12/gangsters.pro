import { computed, onMounted, ref, watch } from "vue";
import { useCatalogStore } from "../../stores/catalogStore";

export function useCatalogPageModel() {
    const catalogStore = useCatalogStore();
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

    onMounted(() => {
        if (!catalogStore.hasLoaded && !catalogStore.loading) {
            catalogStore.fetchCatalog();
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

    const menuProducts = computed(() => catalogStore.menuProducts);
    const categoryTabs = computed(() => catalogStore.categoryTabs);
    const selectedProduct = computed(() => catalogStore.selectedProduct);
    const loading = computed(() => catalogStore.loading);

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
        productSearchQuery,
        menuProducts,
        categoryTabs,
        selectedProduct,
        loading,
        catalogEmptyMessage,
        clearSearch,
    };
}

