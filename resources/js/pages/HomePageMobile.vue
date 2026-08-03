<script setup>
import { useCatalogPageModel } from "../composables/catalog/useCatalogPageModel";
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
                v-model="selectedCategoryId"
                :categories="categoryTabs"
                variant="mobile"
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
