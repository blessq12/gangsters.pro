<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useCatalogStore } from "../stores/catalogStore";

import CatalogCategoriesMobile from "../components/catalog/CatalogCategoriesMobile.vue";
import CatalogProductsMobile from "../components/catalog/CatalogProductsMobile.vue";

const catalogStore = useCatalogStore();

const showProductDetailModal = ref(false);

function openProductDetail(product) {
    catalogStore.setSelectedProduct(product);
    showProductDetailModal.value = true;
}

watch(showProductDetailModal, (isOpen) => {
    if (!isOpen) {
        catalogStore.setSelectedProduct(null);
    }
});

onMounted(() => {
    if (!catalogStore.hasLoaded && !catalogStore.loading) {
        catalogStore.fetchCatalog();
    }
});

const selectedCategoryId = computed({
    get: () => catalogStore.selectedCategoryId,
    set: (value) => catalogStore.setSelectedCategoryId(value),
});

const productSearchQuery = computed({
    get: () => catalogStore.productSearchQuery,
    set: (value) => catalogStore.setProductSearchQuery(value),
});

const menuProducts = computed(() => catalogStore.menuProducts);

const catalogEmptyMessage = computed(() =>
    catalogStore.productSearchQuery.trim()
        ? "Ничего не нашли по этому запросу. Попробуй другое слово или сбрось поиск."
        : "Тут пока тихо. Выберите другую категорию.",
);
</script>

<template>
    <HomeJumbotron />

    <div class="home-page mt-4 space-y-8">
        <HomePromotions />

        <section>
            <header class="mb-4 flex flex-col gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-slate-50">
                        Меню
                    </h2>
                    <p class="text-sm text-slate-400">
                        Категории или поиск по названию — как удобнее.
                    </p>
                </div>

                <div class="w-full">
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
                            @click="catalogStore.setProductSearchQuery('')"
                        >
                            <i class="mdi mdi-close text-lg" />
                        </button>
                    </div>
                </div>
            </header>

            <CatalogCategoriesMobile
                v-model="selectedCategoryId"
                :categories="catalogStore.categoryTabs"
            />

            <CatalogProductsMobile
                :products="menuProducts"
                :loading="catalogStore.loading"
                :empty-message="catalogEmptyMessage"
                @product-image-click="openProductDetail"
            />
        </section>
    </div>

    <ProductDetailModal
        v-model="showProductDetailModal"
        :product="catalogStore.selectedProduct"
    />
</template>

<style scoped></style>

