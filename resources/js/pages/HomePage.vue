<script setup>
import { computed, onMounted } from "vue";
import { useUserStore } from "../stores/userStore";
import { useCatalogStore } from "../stores/catalogStore";

const userStore = useUserStore();
const catalogStore = useCatalogStore();

onMounted(() => {
    if (!catalogStore.hasLoaded && !catalogStore.loading) {
        catalogStore.fetchCatalog();
    }
});

const categories = computed(() =>
    catalogStore.categories.map((entry) => ({
        id: entry.category.id,
        name: entry.category.name,
        uri: entry.category.slug,
    })),
);

const selectedCategoryId = computed({
    get: () => catalogStore.selectedCategoryId,
    set: (value) => catalogStore.setSelectedCategoryId(value),
});

const filteredProducts = computed(() => catalogStore.filteredProducts);

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
            <header class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
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

            <CatalogProducts
                :products="filteredProducts"
                :loading="catalogStore.loading"
            />
        </section>
    </div>
</template>

<style scoped></style>
