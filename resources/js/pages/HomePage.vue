<script setup>
import { computed, ref } from "vue";
import { mockCategories, mockProducts } from "../mocks/catalogMock";
import { useUserStore } from "../stores/userStore";

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

const userStore = useUserStore();

const bottomBarLabel = computed(() =>
    userStore.showBottomNav ? "Скрыть панель" : "Показать панель",
);

const toggleBottomBar = () => {
    userStore.toggleBottomNav();
};
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

                <button
                    type="button"
                    class="hidden sm:inline-flex items-center rounded-full border px-4 py-1.5 text-xs sm:text-sm transition-colors backdrop-blur"
                    :class="
                        userStore.showBottomNav
                            ? 'border-amber-400/60 text-amber-200 bg-[rgba(0,0,0,0.75)]'
                            : 'border-white/15 text-slate-300 bg-[rgba(0,0,0,0.55)] hover:border-amber-400/50 hover:text-amber-200'
                    "
                    @click="toggleBottomBar"
                >
                    {{ bottomBarLabel }}
                </button>
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
