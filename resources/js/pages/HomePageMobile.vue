<script setup>
import { computed, nextTick, ref, unref, watch } from "vue";
import { useCatalogPageModel } from "../modules/catalog/application/models";
import { useCatalogCategoryScrollSpy } from "../modules/catalog/application/useCatalogCategoryScrollSpy";
import { useAppDesign } from "../design/useAppDesign";

const m = useAppDesign().components.pages.home.mobile;

const {
    showProductDetailModal,
    openProductDetail,
    selectedCategoryId,
    selectedTag,
    mobileCardViewMode,
    menuSections,
    categoryTabs,
    tagTabs,
    selectedProduct,
    loading,
    catalogEmptyMessage,
} = useCatalogPageModel();

const categoryBarRef = ref(null);
const productsCompRef = ref(null);

const barRef = computed(() => {
    const bar = categoryBarRef.value;
    if (!bar) return null;
    const pinned = Boolean(unref(bar.isPinned));
    if (pinned) {
        return unref(bar.pinnedIslandEl) ?? unref(bar.islandEl) ?? null;
    }
    return unref(bar.islandEl) ?? null;
});
const productsRef = computed(() => unref(productsCompRef.value?.sectionsRoot) ?? null);

const { scrollToCategory, reconnect } = useCatalogCategoryScrollSpy({
    productsRef,
    barRef,
    activeId: selectedCategoryId,
    syncPillScroll: true,
});

function onCategoryChange(id) {
    scrollToCategory(id);
}

watch(
    [menuSections, loading],
    async () => {
        await nextTick();
        reconnect();
    },
    { flush: "post" },
);
</script>

<template>
    <HomeJumbotronBase variant="mobile" />

    <div :class="m.root">
        <HomePromotionsMobile />

        <section>
            <header :class="m.menuHeader">
                <div>
                    <h2 :class="m.menuTitle">
                        Меню
                    </h2>
                    <p :class="m.menuSubtitle">
                        Категории или поиск — как удобнее.
                    </p>
                </div>

                <div :class="m.searchWrapOuter">
                    <CatalogSearchTrigger
                        input-id="catalog-search"
                        :wrap-class="m.searchWrap"
                        :input-class="m.searchInput"
                        :icon-class="m.searchIconPos"
                    />
                </div>
            </header>

            <CatalogCategoriesBase
                ref="categoryBarRef"
                v-model="selectedCategoryId"
                :categories="categoryTabs"
                variant="mobile"
                :show-all="false"
                pin-on-scroll
                @change="onCategoryChange"
            />
            <CatalogCategoriesBase
                v-model="selectedTag"
                :categories="tagTabs"
                all-label="Все теги"
                variant="mobile"
            />

            <CatalogViewControlsMobile
                :class="m.viewControls"
                v-model:view-mode="mobileCardViewMode"
            />

            <CatalogProductsBase
                ref="productsCompRef"
                :sections="menuSections"
                :loading="loading"
                :empty-message="catalogEmptyMessage"
                :mobile-card-view-mode="mobileCardViewMode"
                variant="mobile"
                @product-image-click="openProductDetail"
            />
        </section>
    </div>

    <ProductDetailModalBase
        v-model="showProductDetailModal"
        :product="selectedProduct"
        variant="mobile"
    />
</template>

<style scoped></style>
