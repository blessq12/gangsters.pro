<script setup>
import CatalogCategoriesMobile from "../components/catalog/CatalogCategoriesMobile.vue";
import CatalogProductsMobile from "../components/catalog/CatalogProductsMobile.vue";
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
    <HomeJumbotronMobile />

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

            <CatalogCategoriesMobile
                v-model="selectedCategoryId"
                :categories="categoryTabs"
            />
            <CatalogCategoriesMobile
                v-model="selectedTag"
                :categories="tagTabs"
                all-label="Все теги"
            />

            <CatalogViewControlsMobile
                :class="m.viewControls"
                v-model:view-mode="mobileCardViewMode"
            />

            <CatalogProductsMobile
                :sections="menuSections"
                :loading="loading"
                :empty-message="catalogEmptyMessage"
                :mobile-card-view-mode="mobileCardViewMode"
                @product-image-click="openProductDetail"
            />
        </section>
    </div>

    <ProductDetailModalMobile
        v-model="showProductDetailModal"
        :product="selectedProduct"
    />
</template>

<style scoped></style>
