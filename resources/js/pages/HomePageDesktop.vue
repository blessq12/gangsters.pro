<script setup>
import { useCatalogPageModel } from "../composables/catalog/useCatalogPageModel";
import { useAppDesign } from "../design/useAppDesign";

const d = useAppDesign().components.pages.home.desktop;

const {
    showProductDetailModal,
    openProductDetail,
    selectedCategoryId,
    selectedTag,
    productSearchQuery,
    desktopCardsPerRow,
    menuSections,
    categoryTabs,
    tagTabs,
    selectedProduct,
    loading,
    catalogEmptyMessage,
    clearSearch,
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
                        Категории или поиск по названию — как удобнее.
                    </p>
                </div>

                <div :class="d.searchCol">
                    <label
                        :class="d.srOnlyLabel"
                        for="catalog-search"
                    >Поиск в меню</label>
                    <div :class="d.searchWrap">
                        <i
                            class="mdi mdi-magnify"
                            :class="d.searchIconPos"
                            aria-hidden="true"
                        />
                        <input
                            id="catalog-search"
                            v-model="productSearchQuery"
                            type="search"
                            autocomplete="off"
                            placeholder="Найти по названию…"
                            :class="d.searchInput"
                        />
                        <button
                            v-if="productSearchQuery.trim()"
                            type="button"
                            :class="d.searchClear"
                            aria-label="Очистить поиск"
                            @click="clearSearch"
                        >
                            <i class="mdi mdi-close text-lg" />
                        </button>
                    </div>
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

