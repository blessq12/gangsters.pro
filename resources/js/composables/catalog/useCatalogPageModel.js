import { computed, onMounted, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { useCatalogStore } from "../../stores/catalogStore";

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
