<script setup>
import { computed, ref } from "vue";
import { mockCategories, mockProducts } from "../mocks/catalogMock";

const categories = mockCategories;
const allProducts = mockProducts;

const selectedCategoryId = ref(null);

const filteredProducts = computed(() => {
    if (!selectedCategoryId.value) {
        return allProducts;
    }

    return allProducts.filter(
        (product) => product.categoryId === selectedCategoryId.value,
    );
});
</script>

<template>
    <HomeJumbotron />

    <div class="home-page mt-4 space-y-10">
        <HomePromotions />

        <section>
            <header class="mb-4 flex items-end justify-between gap-3">
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
                :categories="categories"
            />

            <CatalogProducts :products="filteredProducts" />
        </section>
    </div>
</template>

<style scoped></style>
