<script setup>
import { useCatalogPageModel } from "../composables/catalog/useCatalogPageModel";

const {
    showProductDetailModal,
    openProductDetail,
    selectedCategoryId,
    selectedTag,
    productSearchQuery,
    menuProducts,
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

    <div class="home-page mt-4 space-y-10">
        <HomePromotionsDesktop />

        <section>
            <header class="mb-4 flex items-end justify-between gap-4">
                <div class="block">
                    <h2 class="text-xl font-semibold leading-tight text-slate-50">
                        Меню
                    </h2>
                    <p class="text-sm text-slate-400">
                        Категории или поиск по названию — как удобнее.
                    </p>
                </div>

                <div class="w-full max-w-xs shrink-0">
                    <label class="sr-only" for="catalog-search">Поиск в меню</label>
                    <div class="relative">
                        <i
                            class="mdi mdi-magnify pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-lg text-slate-500"
                            aria-hidden="true"
                        />
                        <input
                            id="catalog-search"
                            v-model="productSearchQuery"
                            type="search"
                            autocomplete="off"
                            placeholder="Найти по названию…"
                            class="w-full rounded-2xl border border-white/10 bg-black/40 py-2.5 pl-10 pr-10 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400/50"
                        />
                        <button
                            v-if="productSearchQuery.trim()"
                            type="button"
                            class="absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 transition hover:bg-white/10 hover:text-slate-200"
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

            <CatalogProductsDesktop
                :products="menuProducts"
                :loading="loading"
                :empty-message="catalogEmptyMessage"
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

