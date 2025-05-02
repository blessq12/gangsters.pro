<script>
import gsap from "gsap";
import { mapStores } from "pinia";
import { Autoplay, Navigation, Pagination } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";
import { appStore } from "../../stores/appStorage";
import { localStore } from "../../stores/localStore";

import "swiper/css";
import "swiper/css/autoplay";
import "swiper/css/navigation";
import "swiper/css/pagination";

export default {
    setup() {
        return {
            Pagination,
            Navigation,
            Autoplay,
        };
    },
    components: {
        Swiper,
        SwiperSlide,
    },
    computed: {
        ...mapStores(appStore, localStore),
    },
    data() {
        return {
            product: { name: "" },
            currentSlide: 0,
            swiperInstance: null,
        };
    },
    methods: {
        closeModal() {
            gsap.to("#additional-content", {
                opacity: 0,
                y: 20,
                scale: 0.95,
                duration: 0.2,
                ease: "power2.in",
                onComplete: () => {
                    this.appStore.currentAdditional = null;
                },
            });
        },
        onSwiperInit(swiper) {
            this.swiperInstance = swiper;
            this.currentSlide = swiper.realIndex;
        },
        slideNext() {
            if (this.swiperInstance) {
                this.swiperInstance.slideNext();
            }
        },
        slidePrev() {
            if (this.swiperInstance) {
                this.swiperInstance.slidePrev();
            }
        },
        slideTo(index) {
            if (this.swiperInstance) {
                this.swiperInstance.slideTo(index);
            }
        },
        onSlideChange(swiper) {
            this.currentSlide = swiper.realIndex;
        },
    },
    watch: {
        "appStore.currentAdditional": {
            handler(val) {
                this.product = val || { name: "" };
                if (val) {
                    this.$nextTick(() => {
                        gsap.fromTo(
                            "#additional-content",
                            {
                                opacity: 0,
                                y: 20,
                                scale: 0.95,
                            },
                            {
                                opacity: 1,
                                y: 0,
                                scale: 1,
                                duration: 0.3,
                                ease: "back.out(1.7)",
                            }
                        );
                    });
                }
            },
            immediate: true,
        },
    },
};
</script>

<template>
    <teleport to="body">
        <div
            class="fixed inset-0 z-50 min-h-screen w-full overflow-y-auto backdrop-blur-sm bg-black/10 flex justify-center items-center p-4 transition-all duration-300"
            v-show="appStore.currentAdditional"
            id="additional"
            tabindex="-1"
            aria-labelledby="additionalLabel"
            aria-hidden="true"
            @click.self="closeModal"
        >
            <div
                id="additional-content"
                class="w-full max-w-5xl bg-transparent rounded-2xl shadow-2xl overflow-hidden relative h-[90vh] md:h-[80vh]"
            >
                <!-- Слайдер изображений как фон -->
                <div class="absolute inset-0 z-0">
                    <div
                        v-if="
                            product &&
                            product.images &&
                            product.images.length > 0
                        "
                        class="h-full"
                    >
                        <swiper
                            :modules="[Pagination, Navigation, Autoplay]"
                            :pagination="false"
                            :navigation="false"
                            :autoplay="{
                                delay: 5000,
                                disableOnInteraction: false,
                            }"
                            :loop="true"
                            :slides-per-view="1"
                            :effect="'fade'"
                            class="h-full"
                            @swiper="onSwiperInit"
                            @slideChange="onSlideChange"
                        >
                            <swiper-slide
                                v-for="image in product.images"
                                :key="image"
                                class="relative h-full"
                            >
                                <img
                                    v-lazy="image"
                                    :alt="product.name"
                                    class="object-cover w-full h-full"
                                />
                            </swiper-slide>
                        </swiper>
                    </div>
                    <div v-else class="h-full">
                        <img
                            src="/images/placeholder/product-image-empty-1024x1024.jpg"
                            alt=""
                            class="object-cover w-full h-full"
                        />
                    </div>
                </div>
                <div class="relative z-10 h-full flex flex-col justify-between">
                    <div
                        class="flex items-center justify-between p-4 md:p-6 bg-black/20 backdrop-blur-xs"
                    >
                        <h4
                            class="text-lg md:text-2xl font-bold text-white line-clamp-1"
                            id="additionalLabel"
                        >
                            {{ product.name + " - " + product.price + " руб." }}
                        </h4>
                        <button
                            type="button"
                            class="p-2 hover:bg-white/10 rounded-full transition-colors"
                            @click="closeModal"
                        >
                            <span
                                class="mdi mdi-close text-2xl text-white"
                            ></span>
                        </button>
                    </div>

                    <div
                        class="p-4 md:p-6 bg-black/20 backdrop-blur-xs space-y-4"
                    >
                        <!-- Навигация слайдера -->
                        <div
                            v-if="
                                product &&
                                product.images &&
                                product.images.length > 1
                            "
                            class="flex items-center justify-between gap-4 mb-4"
                        >
                            <button
                                @click="slidePrev"
                                class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors backdrop-blur-sm text-white"
                            >
                                <span
                                    class="mdi mdi-chevron-left text-2xl"
                                ></span>
                            </button>
                            <div
                                class="flex gap-2 items-center justify-center flex-1"
                            >
                                <button
                                    v-for="(_, index) in product.images"
                                    :key="index"
                                    @click="slideTo(index)"
                                    class="w-2 h-2 rounded-full transition-all duration-300"
                                    :class="{
                                        'bg-white': currentSlide === index,
                                        'bg-white/50': currentSlide !== index,
                                    }"
                                ></button>
                            </div>
                            <button
                                @click="slideNext"
                                class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors backdrop-blur-sm text-white"
                            >
                                <span
                                    class="mdi mdi-chevron-right text-2xl"
                                ></span>
                            </button>
                        </div>

                        <!-- Основная информация -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-4">
                                <!-- Состав -->
                                <div
                                    class="bg-white/10 p-4 rounded-xl backdrop-blur-sm"
                                >
                                    <h5
                                        class="text-base md:text-lg font-bold mb-2 text-white"
                                    >
                                        Состав:
                                    </h5>
                                    <p
                                        class="text-sm md:text-base text-white/90 leading-relaxed"
                                    >
                                        {{ product.consist }}
                                    </p>
                                </div>

                                <!-- Цена и вес -->
                                <div
                                    class="flex flex-wrap gap-4 text-base md:text-lg bg-white/10 p-4 rounded-xl backdrop-blur-sm"
                                >
                                    <div>
                                        <span class="font-bold text-white"
                                            >Цена:</span
                                        >
                                        <span
                                            class="ml-2 font-semibold text-green-400"
                                            >{{ product.price }} руб.</span
                                        >
                                    </div>
                                    <div>
                                        <span class="font-bold text-white"
                                            >Вес:</span
                                        >
                                        <span class="ml-2 text-white/90"
                                            >{{ product.weight }} гр.</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Дополнительная информация -->
                                <div
                                    class="bg-white/10 p-4 rounded-xl backdrop-blur-sm"
                                >
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            class="px-3 py-1.5 rounded-full text-sm font-semibold bg-gradient-to-r from-red-500/90 to-red-600/90 text-white shadow-sm flex items-center gap-2 backdrop-blur-sm"
                                            v-if="product.hit"
                                        >
                                            <span class="mdi mdi-fire"></span>
                                            Хит
                                        </span>
                                        <span
                                            class="px-3 py-1.5 rounded-full text-sm font-semibold bg-gradient-to-r from-amber-500/90 to-amber-600/90 text-white shadow-sm flex items-center gap-2 backdrop-blur-sm"
                                            v-if="product.spicy"
                                        >
                                            <span
                                                class="mdi mdi-chili-hot"
                                            ></span>
                                            Острое
                                        </span>
                                        <span
                                            class="px-3 py-1.5 rounded-full text-sm font-semibold bg-gradient-to-r from-green-500/90 to-green-600/90 text-white shadow-sm flex items-center gap-2 backdrop-blur-sm"
                                            v-if="product.kidsAllow"
                                        >
                                            <span
                                                class="mdi mdi-baby-face"
                                            ></span>
                                            Для детей
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <transition
                                        enter-active-class="animate__animated animate__fadeIn"
                                        leave-active-class="animate__animated animate__fadeOut"
                                        mode="out-in"
                                    >
                                        <button
                                            v-if="
                                                !localStore.checkExist(
                                                    'cart',
                                                    product
                                                )
                                            "
                                            class="flex-1 px-4 py-3 bg-gradient-to-r from-green-500/90 to-green-600/90 hover:from-green-600/90 hover:to-green-700/90 text-white rounded-xl transition-all duration-300 transform hover:scale-105 flex items-center justify-center gap-2 font-semibold shadow-lg backdrop-blur-sm text-sm md:text-base"
                                            @click="
                                                localStore.manageStore(
                                                    'cart',
                                                    product
                                                )
                                            "
                                        >
                                            <span
                                                class="mdi mdi-cart text-xl"
                                            ></span>
                                            В корзину
                                        </button>

                                        <div
                                            v-else
                                            class="flex-1 flex items-center bg-white/10 rounded-xl overflow-hidden shadow-lg backdrop-blur-sm"
                                        >
                                            <button
                                                class="px-4 py-3 hover:bg-white/20 transition-colors text-xl text-white font-bold"
                                                @click="
                                                    localStore.manageQty(
                                                        false,
                                                        product
                                                    )
                                                "
                                            >
                                                <span
                                                    class="mdi mdi-minus"
                                                ></span>
                                            </button>
                                            <span
                                                class="flex-1 text-center py-3 text-base md:text-lg font-semibold text-white"
                                            >
                                                {{ localStore.getQty(product) }}
                                            </span>
                                            <button
                                                class="px-4 py-3 hover:bg-white/20 transition-colors text-xl text-white font-bold"
                                                @click="
                                                    localStore.manageQty(
                                                        true,
                                                        product
                                                    )
                                                "
                                            >
                                                <span
                                                    class="mdi mdi-plus"
                                                ></span>
                                            </button>
                                        </div>
                                    </transition>

                                    <button
                                        class="p-3 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg backdrop-blur-sm"
                                        :class="{
                                            'bg-white/10 text-red-400 hover:bg-white/20':
                                                !localStore.checkExist(
                                                    'fav',
                                                    product
                                                ),
                                            'bg-gradient-to-r from-red-500/90 to-red-600/90 text-white hover:from-red-600/90 hover:to-red-700/90':
                                                localStore.checkExist(
                                                    'fav',
                                                    product
                                                ),
                                        }"
                                        @click="
                                            localStore.manageStore(
                                                'fav',
                                                product
                                            )
                                        "
                                    >
                                        <span
                                            class="mdi text-xl"
                                            :class="{
                                                'mdi-heart':
                                                    localStore.checkExist(
                                                        'fav',
                                                        product
                                                    ),
                                                'mdi-heart-outline':
                                                    !localStore.checkExist(
                                                        'fav',
                                                        product
                                                    ),
                                            }"
                                        ></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<style scoped lang="sass">
.swiper
    &-slide
        @apply transition-opacity duration-300
        &-active
            @apply opacity-100
        &-inactive
            @apply opacity-0
</style>
