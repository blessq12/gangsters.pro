<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useCatalogStore } from "../stores/catalogStore";

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

const filteredProducts = computed(() => catalogStore.filteredProducts);

</script>

<template>
    <HomeJumbotron />

    <div class="home-page mt-4 space-y-10">
        <HomePromotions />

        <section>
            <header class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-semibold text-slate-50">
                        Меню
                    </h2>
                    <p class="text-sm text-slate-400">
                        Выбери категорию и собери свой гангстерский вечер.
                    </p>
                </div>

            </header>

            <CatalogCategories
                v-model="selectedCategoryId"
                :categories="catalogStore.categoryTabs"
            />

            <CatalogProducts
                :products="filteredProducts"
                :loading="catalogStore.loading"
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
