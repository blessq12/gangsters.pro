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
        image: banner.image || "",
    })),
);

const isLoading = computed(() => systemStore.loadingBanners);

const swiperRef = ref(null);

const handleSwiperInit = (swiper) => {
    if (!swiper) return;
    swiperRef.value = swiper;
    // Сразу переключаемся на слайд назад, чтобы видны были соседние.
    swiper.slidePrev(0);
};
</script>

<template>
    <section
        class="relative mt-6 mb-14 w-screen max-w-none overflow-visible [margin-left:calc(50%-50vw)]"
    >
        <div class="pointer-events-none absolute inset-0 opacity-50 mix-blend-screen">
            <div
                class="absolute -left-8 top-6 h-32 w-32 rounded-full bg-amber-500/15 blur-3xl"
            ></div>
            <div
                class="absolute -right-8 bottom-0 h-40 w-40 rounded-full bg-rose-500/10 blur-3xl"
            ></div>
        </div>

        <div class="relative px-3 sm:px-4 overflow-x-hidden">
            <div v-if="isLoading" class="mx-auto max-w-[95vw] px-1 py-6 sm:py-7">
                <div
                    class="h-88 w-full rounded-2xl border border-white/10 bg-slate-800/60 animate-pulse"
                ></div>
            </div>

            <Swiper
                v-else-if="slides.length"
                :loop="true"
                :looped-slides="slides.length"
                :loop-additional-slides="slides.length"
                :autoplay="{ delay: 4500 }"
                :speed="700"
                :space-between="10"
                :centered-slides="true"
                :watch-slides-progress="true"
                :slide-to-clicked-slide="true"
                :breakpoints="{
                    0: { slidesPerView: 1.18, spaceBetween: 10 },
                    480: { slidesPerView: 1.24 },
                }"
                class="!overflow-visible"
                @swiper="handleSwiperInit"
            >
                <SwiperSlide v-for="(slide, index) in slides" :key="index">
                    <div class="px-0.5 py-3">
                        <div
                            class="home-jumbotron-card relative rounded-2xl overflow-hidden border bg-slate-900/60"
                        >
                            <div class="home-jumbotron-media aspect-[3/4] w-full">
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
                                class="absolute left-3 top-3 inline-flex rounded-full border border-white/10 bg-[rgba(0,0,0,0.42)] px-2 py-[3px] text-[10px] font-medium uppercase tracking-[0.2em] text-slate-100 backdrop-blur"
                            >
                                Gangsters
                            </div>

                            <div
                                class="absolute right-3 top-3 inline-flex rounded-full border border-amber-400/20 bg-[rgba(0,0,0,0.38)] px-2 py-[3px] text-[10px] font-semibold text-amber-200 backdrop-blur"
                            >
                                {{ String(index + 1).padStart(2, "0") }}/{{ String(slides.length).padStart(2, "0") }}
                            </div>

                            <div
                                class="absolute inset-x-2.5 bottom-2 rounded-2xl border border-white/10 bg-[rgba(0,0,0,0.32)] px-3 py-2.5 backdrop-blur-xl"
                            >
                                <p
                                    class="mb-1 text-[10px] uppercase tracking-[0.26em] text-slate-300/80"
                                >
                                    Главное предложение
                                </p>

                                <h1
                                    class="text-xl font-semibold leading-tight text-amber-300"
                                >
                                    {{ slide.title }}
                                </h1>

                                <p class="mt-1 text-xs leading-relaxed text-slate-200/90">
                                    {{ slide.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </SwiperSlide>
            </Swiper>

            <div v-else class="mx-auto max-w-4xl py-8 text-center text-xs text-slate-500">
                Баннеры скоро появятся.
            </div>
        </div>
    </section>
</template>

<style scoped>
:deep(.swiper),
:deep(.swiper-wrapper),
:deep(.swiper-slide),
:deep(.swiper-3d) {
    overflow-x: visible !important;
    overflow-y: visible !important;
    overflow: visible !important;
}

:deep(.swiper .swiper-wrapper),
:deep(.swiper .swiper-slide),
:deep(.swiper .swiper-3d),
:deep(.swiper-slide-active) {
    overflow: visible !important;
}

:deep(.swiper),
:deep(.swiper-wrapper) {
    height: auto !important;
    min-height: 0 !important;
}

:deep(.swiper-wrapper) {
    align-items: stretch !important;
}

:deep(.swiper-slide) {
    /* Swiper считает высоту по не-скейленному layout'у, а визуально мы
       увеличиваем карточку scale'ом. Даем вертикальный запас, чтобы
       активный слайд не клипился сверху/снизу. */
    padding-top: 18px !important;
    padding-bottom: 18px !important;
    box-sizing: border-box;
}

:deep(.swiper-slide .home-jumbotron-card) {
    transform: scale(0.86);
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
    transform: scale(0.96);
    opacity: 0.58;
    cursor: pointer;
    border-color: rgba(255, 255, 255, 0.1);
}

:deep(.swiper-slide-prev .home-jumbotron-card:hover),
:deep(.swiper-slide-next .home-jumbotron-card:hover) {
    opacity: 0.7;
}

:deep(.swiper-slide-active .home-jumbotron-card) {
    transform: scale(1.1);
    opacity: 1;
    border-color: rgba(251, 191, 36, 0.35);
    box-shadow: 0 24px 50px rgba(0, 0, 0, 0.45);
    z-index: 10;
}
</style>

