<script setup>
import { useCatalogPageModel } from "../composables/catalog/useCatalogPageModel";
import { useAppDesign } from "../design/useAppDesign";

const d = useAppDesign().components.pages.home.desktop;

const {
    showProductDetailModal,
    openProductDetail,
    selectedCategoryId,
    selectedTag,
    desktopCardsPerRow,
    menuSections,
    categoryTabs,
    tagTabs,
    selectedProduct,
    loading,
    catalogEmptyMessage,
} = useCatalogPageModel();
</script>

<template>
    <HomeJumbotron />

    <div :class="d.root">
        <HomePromotionsDesktop />

        <section>
            <header :class="d.menuHeader">
                <div :class="d.menuTitleBlock">
                    <h2 :class="d.menuTitle">
                        Меню
                    </h2>
                    <p :class="d.menuSubtitle">
                        Категории или поиск — как удобнее.
                    </p>
                </div>

                <div :class="d.searchCol">
                    <CatalogSearchTrigger
                        input-id="catalog-search"
                        :wrap-class="d.searchWrap"
                        :input-class="d.searchInput"
                        :icon-class="d.searchIconPos"
                    />
                </div>
            </header>

            <CatalogCategoriesDesktop
                v-model="selectedCategoryId"
                :categories="categoryTabs"
            />
            <CatalogCategoriesDesktop
                v-model="selectedTag"
                :categories="tagTabs"
                all-label="Все теги"
            />

            <div :class="d.catalogControlsRow">
                <CatalogViewControlsDesktop v-model:cards-per-row="desktopCardsPerRow" />
            </div>

            <CatalogProductsDesktop
                :sections="menuSections"
                :loading="loading"
                :empty-message="catalogEmptyMessage"
                :cards-per-row="desktopCardsPerRow"
                @product-image-click="openProductDetail"
            />
        </section>
    </div>

    <ProductDetailModal
        v-model="showProductDetailModal"
        :product="selectedProduct"
    />
</template>

<style scoped></style>
