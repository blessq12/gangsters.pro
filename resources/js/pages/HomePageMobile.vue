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
    productSearchQuery,
    mobileCardViewMode,
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
                        Категории или поиск по названию — как удобнее.
                    </p>
                </div>

                <div :class="m.searchWrapOuter">
                    <label
                        :class="m.srOnlyLabel"
                        for="catalog-search"
                    >Поиск в меню</label>
                    <div :class="m.searchWrap">
                        <i
                            class="mdi mdi-magnify"
                            :class="m.searchIconPos"
                            aria-hidden="true"
                        />
                        <input
                            id="catalog-search"
                            v-model="productSearchQuery"
                            type="search"
                            autocomplete="off"
                            placeholder="Найти по названию…"
                            :class="m.searchInput"
                        />
                        <button
                            v-if="productSearchQuery.trim()"
                            type="button"
                            :class="m.searchClear"
                            aria-label="Очистить поиск"
                            @click="clearSearch"
                        >
                            <i class="mdi mdi-close text-lg" />
                        </button>
                    </div>
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

