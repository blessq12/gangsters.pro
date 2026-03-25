<script setup>
import "swiper/css";
import { computed, ref } from "vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import { useSystemStore } from "../../stores/systemStore";

const systemStore = useSystemStore();

const slides = computed(() =>
    (systemStore.banners || []).map((banner) => ({
        title: banner.title || "",
        description: banner.description || "",
        image: banner.image_desktop || banner.image || banner.image_mobile || "",
    })),
);

const isLoading = computed(() => systemStore.loadingBanners);

const swiperRef = ref(null);

const handleSwiperInit = (swiper) => {
    if (!swiper) return;
    swiperRef.value = swiper;
    // переключаемся на слайд назад, чтобы сразу видеть соседние
    swiper.slidePrev(0);
};

</script>

<template>
    <section
        class="relative mt-8 mb-12 w-screen max-w-none overflow-hidden sm:mt-12 sm:mb-18 [margin-left:calc(50%-50vw)] [margin-right:calc(50%-50vw)]"
    >
        <div
            class="pointer-events-none absolute inset-0 opacity-50 mix-blend-screen"
        >
            <div
                class="absolute -left-10 top-8 h-36 w-36 rounded-full bg-amber-500/15 blur-3xl sm:-left-16 sm:top-0 sm:h-56 sm:w-56"
            ></div>
            <div
                class="absolute -right-8 bottom-0 h-44 w-44 rounded-full bg-rose-500/10 blur-3xl sm:right-0 sm:h-64 sm:w-64"
            ></div>
        </div>

        <div class="relative px-4 sm:px-6 lg:px-8">
            <div
                v-if="isLoading"
                class="mx-auto max-w-5xl px-1 py-6 sm:py-8"
            >
                <div
                    class="h-64 sm:h-80 w-full rounded-2xl border border-white/10 bg-slate-800/60 animate-pulse"
                ></div>
            </div>

            <Swiper
                v-else-if="slides.length"
                :loop="true"
                :looped-slides="slides.length"
                :loop-additional-slides="slides.length"
                :autoplay="{ delay: 4500 }"
                :speed="700"
                :space-between="12"
                :centered-slides="true"
                :watch-slides-progress="true"
                :slide-to-clicked-slide="true"
                :breakpoints="{
                    0: { slidesPerView: 1.18, spaceBetween: 12 },
                    768: { slidesPerView: 1.35 },
                    1024: { slidesPerView: 1.55 },
                }"
                class="!overflow-visible"
                @swiper="handleSwiperInit"
            >
                <SwiperSlide
                    v-for="(slide, index) in slides"
                    :key="index"
                >
                    <div class="px-1 py-3 sm:py-5">
                        <div
                            class="home-jumbotron-card relative rounded-2xl overflow-hidden border bg-slate-900/60"
                        >
                            <div class="aspect-[5/4] w-full sm:aspect-[16/9]">
                                <img
                                    :src="slide.image"
                                    :alt="slide.title"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <div
                                class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/85"
                            ></div>
                            <div
                                class="absolute left-3 top-3 inline-flex rounded-full border border-white/10 bg-[rgba(0,0,0,0.42)] px-2.5 py-1 text-[10px] font-medium uppercase tracking-[0.2em] text-slate-100 backdrop-blur sm:left-4 sm:top-4 sm:text-[11px]"
                            >
                                Gangsters
                            </div>
                            <div
                                class="absolute right-3 top-3 inline-flex rounded-full border border-amber-400/20 bg-[rgba(0,0,0,0.38)] px-2.5 py-1 text-[10px] font-semibold text-amber-200 backdrop-blur sm:right-4 sm:top-4 sm:text-[11px]"
                            >
                                {{ String(index + 1).padStart(2, "0") }}/{{ String(slides.length).padStart(2, "0") }}
                            </div>
                            <div
                                class="absolute inset-x-3 bottom-3 rounded-2xl border border-white/10 bg-[rgba(0,0,0,0.32)] px-4 py-3 backdrop-blur-xl sm:inset-x-0 sm:bottom-0 sm:rounded-none sm:border-0 sm:bg-transparent sm:px-6 sm:py-4 sm:backdrop-blur-0"
                            >
                                <p
                                    class="mb-1 text-[10px] uppercase tracking-[0.26em] text-slate-300/80 sm:text-sm sm:tracking-[0.3em]"
                                >
                                    Главное предложение
                                </p>
                                <h1
                                    class="text-xl font-semibold leading-tight text-amber-300 sm:text-2xl"
                                >
                                    {{ slide.title }}
                                </h1>
                                <p
                                    class="mt-1 max-w-[18rem] text-xs leading-relaxed text-slate-200/90 sm:max-w-none sm:text-sm"
                                >
                                    {{ slide.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </SwiperSlide>
            </Swiper>

            <div
                v-else
                class="mx-auto max-w-4xl py-8 text-center text-xs text-slate-500"
            >
                Баннеры скоро появятся.
            </div>
        </div>
    </section>
</template>

<style scoped>
:deep(.swiper-slide .home-jumbotron-card) {
    transform: scale(0.72);
    opacity: 0;
    border-color: transparent;
    transition:
        transform 500ms ease,
        opacity 500ms ease,
        border-color 500ms ease,
        box-shadow 500ms ease;
}

:deep(.swiper-slide-prev .home-jumbotron-card),
:deep(.swiper-slide-next .home-jumbotron-card) {
    transform: scale(0.82);
    opacity: 0.55;
    cursor: pointer;
    border-color: rgba(255, 255, 255, 0.1);
}

:deep(.swiper-slide-prev .home-jumbotron-card:hover),
:deep(.swiper-slide-next .home-jumbotron-card:hover) {
    opacity: 0.7;
}

:deep(.swiper-slide-active .home-jumbotron-card) {
    transform: scale(1.05);
    opacity: 1;
    border-color: rgba(251, 191, 36, 0.35);
    box-shadow: 0 24px 50px rgba(0, 0, 0, 0.45);
    z-index: 10;
}

@media (max-width: 767px) {
    :deep(.swiper-slide .home-jumbotron-card) {
        transform: scale(0.82);
    }

    :deep(.swiper-slide-prev .home-jumbotron-card),
    :deep(.swiper-slide-next .home-jumbotron-card) {
        transform: scale(0.9);
        opacity: 0.58;
    }

    :deep(.swiper-slide-active .home-jumbotron-card) {
        transform: scale(1.02);
        box-shadow: 0 20px 42px rgba(0, 0, 0, 0.42);
    }
}
</style>
