<script setup>
import { computed, nextTick, ref, unref, watch } from "vue";
import { useCatalogPageModel } from "../modules/catalog/application/models";
import { useCatalogCategoryScrollSpy } from "../modules/catalog/application/useCatalogCategoryScrollSpy";
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
    syncPillScroll: false,
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
                ref="categoryBarRef"
                v-model="selectedCategoryId"
                :categories="categoryTabs"
                variant="desktop"
                :show-all="false"
                pin-on-scroll
                @change="onCategoryChange"
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
                ref="productsCompRef"
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
