<script>
import { mapStores } from "pinia";
import { appStore } from "../../stores/appStorage";
import { localStore } from "../../stores/localStore";

export default {
    props: {
        goods: Object,
        required: true,
    },
    data() {
        return {
            currentCategory: this.goods[0].uri,
            scrollPoint: null,
            intersectedCategory: null,
            isScrolled: false,
        };
    },
    computed: {
        ...mapStores(appStore, localStore),
    },
    methods: {
        scrollToCategory(category) {
            this.$nextTick(() => {
                const element = document.querySelector(
                    `[data-category="${category}"]`
                );
                if (element) {
                    const targetPosition =
                        element.getBoundingClientRect().top + window.scrollY;
                    window.scrollTo({
                        top: targetPosition - 100,
                        behavior: "smooth",
                    });
                }
            });
        },
        positionObserver() {
            const options = {
                threshold: 0.1,
            };
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        this.currentCategory = entry.target.dataset.category;
                    }
                });
            }, options);
            this.$refs.category.forEach((category) => {
                observer.observe(category);
            });
        },
        categooryBarObserver() {
            const categoryBarContainer = document.querySelector(
                "#category-bar-container"
            );

            const options = {
                threshold: 1,
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    this.isScrolled = !entry.isIntersecting;
                });
            }, options);

            if (categoryBarContainer) {
                observer.observe(categoryBarContainer);
            }
        },
        leftScrollCategory() {
            const categoryList = document.querySelector("#category-list");
            const categoryButton = document.querySelector(
                `#categoryButton-${this.currentCategory}`
            );

            const scrollAmount = categoryButton.offsetLeft;
            const startScrollLeft = categoryList.scrollLeft;
            const distance = scrollAmount - startScrollLeft;
            const duration = 300;
            let startTime = null;

            const animation = (currentTime) => {
                if (!startTime) startTime = currentTime;
                const timeElapsed = currentTime - startTime;
                const progress = Math.min(timeElapsed / duration, 1);
                categoryList.scrollLeft = startScrollLeft + distance * progress;
                if (timeElapsed < duration) {
                    requestAnimationFrame(animation);
                }
            };

            requestAnimationFrame(animation);
        },
    },
    mounted() {
        this.positionObserver();
        this.categooryBarObserver();
    },
    watch: {
        currentCategory(newVal) {
            this.leftScrollCategory();
        },
    },
};
</script>

<template>
    <div
        class="sticky top-0 z-50 bg-white/95 backdrop-blur-xl transition-all duration-300"
        :class="{ 'border-b border-neutral-100 shadow-sm': isScrolled }"
    >
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
            id="category-bar-container"
        >
            <ul
                class="flex items-center overflow-x-auto scrollbar-hide space-x-3 py-6"
                id="category-list"
            >
                <li>
                    <Search />
                </li>
                <li v-for="el in goods" :key="el.uri">
                    <a
                        :id="'categoryButton-' + el.uri"
                        @click="scrollToCategory(el.uri)"
                        class="px-6 py-3 rounded-lg transition-all duration-300 ease-out whitespace-nowrap text-sm font-medium relative overflow-hidden"
                        :class="{
                            'bg-neutral-900 text-white hover:bg-neutral-800':
                                el.uri === currentCategory,
                            'bg-neutral-100 text-neutral-600 hover:bg-neutral-200 hover:text-neutral-900':
                                el.uri !== currentCategory,
                        }"
                    >
                        {{ el.name }}
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <PrizeRow></PrizeRow>

    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 mb-12 lg:mb-36"
        id="catalog-container"
    >
        <div
            class="category relative space-y-12"
            v-for="category in goods"
            :key="category.uri"
            :data-category="category.uri"
            ref="category"
        >
            <div
                class="absolute -top-24 left-0"
                :data-category="category.uri"
            ></div>
            <div class="mb-8">
                <div>
                    <div class="w-full">
                        <h2
                            class="text-2xl md:text-4xl font-semibold mb-2 text-neutral-900"
                        >
                            {{ category.name }}
                        </h2>
                        <div class="h-px w-24 bg-neutral-900"></div>
                    </div>
                </div>
            </div>
            <div
                class="overflow-x-auto pb-4 -mx-4 sm:mx-0 sm:overflow-x-visible"
            >
                <div
                    class="grid grid-flow-col auto-cols-[240px] xs:auto-cols-[240px] sm:grid-flow-row sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 px-4 md:px-0"
                >
                    <ProductCard
                        v-for="product in category.products"
                        :key="product.id"
                        :product="product"
                        class="transform transition-all duration-300 hover:scale-[1.02] hover:shadow-xl rounded-xl bg-white relative overflow-hidden group"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
