<script setup>
import { useCatalogPageModel } from "../modules/catalog/application/models";
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
    <HomeJumbotronBase variant="desktop" />

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

            <CatalogCategoriesBase
                v-model="selectedCategoryId"
                :categories="categoryTabs"
                variant="desktop"
            />
            <CatalogCategoriesBase
                v-model="selectedTag"
                :categories="tagTabs"
                all-label="Все теги"
                variant="desktop"
            />

            <div :class="d.catalogControlsRow">
                <CatalogViewControlsDesktop v-model:cards-per-row="desktopCardsPerRow" />
            </div>

            <CatalogProductsBase
                :sections="menuSections"
                :loading="loading"
                :empty-message="catalogEmptyMessage"
                :cards-per-row="desktopCardsPerRow"
                variant="desktop"
                @product-image-click="openProductDetail"
            />
        </section>
    </div>

    <ProductDetailModalBase
        v-model="showProductDetailModal"
        :product="selectedProduct"
        variant="desktop"
    />
</template>

<style scoped></style>
