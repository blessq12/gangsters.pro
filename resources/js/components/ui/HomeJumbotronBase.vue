<script setup>
import "swiper/css";
import { computed, ref } from "vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import { useSystemStore } from "../../stores/systemStore";

const props = defineProps({
    variant: {
        type: String,
        default: "desktop",
        validator: (value) => ["desktop", "mobile"].includes(value),
    },
});

const systemStore = useSystemStore();
const isMobile = computed(() => props.variant === "mobile");

const slides = computed(() =>
    (systemStore.banners || []).map((banner) => ({
        title: banner.title || "",
        description: banner.description || "",
        image: isMobile.value
            ? banner.image_mobile || banner.image || banner.image_desktop || ""
            : banner.image_desktop || banner.image || banner.image_mobile || "",
    })),
);

const isLoading = computed(() => systemStore.loadingBanners);
const swiperRef = ref(null);
const loopReady = computed(() => slides.value.length >= 3);
const rewindEnabled = computed(() => slides.value.length > 1 && !loopReady.value);

const swiperBreakpoints = computed(() =>
    isMobile.value
        ? {
              0: { slidesPerView: 1.18, spaceBetween: 10 },
              480: { slidesPerView: 1.24 },
          }
        : {
              0: { slidesPerView: 1.08, spaceBetween: 8 },
              768: { slidesPerView: 1.12, spaceBetween: 8 },
              1024: { slidesPerView: 1.18, spaceBetween: 10 },
              1280: { slidesPerView: 1.24, spaceBetween: 10 },
          },
);

const handleSwiperInit = (swiper) => {
    if (!swiper) return;
    swiperRef.value = swiper;
    swiper.slidePrev(0);
};

const goPrev = () => {
    const swiper = swiperRef.value;
    const total = slides.value.length;
    if (!swiper || total < 2) return;

    const targetIndex = (swiper.realIndex - 1 + total) % total;
    if (loopReady.value) {
        swiper.slideToLoop(targetIndex);
        return;
    }

    swiper.slideTo(targetIndex);
};

const goNext = () => {
    const swiper = swiperRef.value;
    const total = slides.value.length;
    if (!swiper || total < 2) return;

    const targetIndex = (swiper.realIndex + 1) % total;
    if (loopReady.value) {
        swiper.slideToLoop(targetIndex);
        return;
    }

    swiper.slideTo(targetIndex);
};
</script>

<template>
    <section
        :class="
            isMobile
                ? 'home-jumbotron home-jumbotron--mobile relative mt-6 mb-14 w-screen max-w-none overflow-x-clip [margin-left:calc(50%-50vw)] [margin-right:calc(50%-50vw)]'
                : 'home-jumbotron home-jumbotron--desktop relative mt-8 mb-12 w-screen max-w-none overflow-hidden sm:mt-12 sm:mb-18 [margin-left:calc(50%-50vw)] [margin-right:calc(50%-50vw)]'
        "
    >
        <div class="pointer-events-none absolute inset-0 opacity-50 mix-blend-screen">
            <div
                :class="
                    isMobile
                        ? 'absolute -left-8 top-6 h-32 w-32 rounded-full bg-amber-500/15 blur-3xl'
                        : 'absolute -left-10 top-8 h-36 w-36 rounded-full bg-amber-500/15 blur-3xl sm:-left-16 sm:top-0 sm:h-56 sm:w-56'
                "
            ></div>
            <div
                :class="
                    isMobile
                        ? 'absolute -right-8 bottom-0 h-40 w-40 rounded-full bg-rose-500/10 blur-3xl'
                        : 'absolute -right-8 bottom-0 h-44 w-44 rounded-full bg-rose-500/10 blur-3xl sm:right-0 sm:h-64 sm:w-64'
                "
            ></div>
        </div>

        <div :class="isMobile ? 'relative px-3 sm:px-4 overflow-x-clip' : 'relative px-4 sm:px-6 lg:px-8'">
            <div
                v-if="isLoading"
                :class="isMobile ? 'flex justify-center px-1 py-6 sm:py-7' : 'flex justify-center px-1 py-6 sm:py-8'"
            >
                <div
                    :class="
                        isMobile
                            ? 'home-jumbotron-aspect-slot rounded-2xl border border-white/10 bg-slate-800/60 animate-pulse'
                            : 'home-jumbotron-aspect-slot rounded-2xl border border-white/10 bg-slate-800/60 animate-pulse'
                    "
                ></div>
            </div>

            <Swiper
                v-else-if="slides.length"
                :loop="loopReady"
                :rewind="rewindEnabled"
                :looped-slides="loopReady ? slides.length : 0"
                :loop-additional-slides="loopReady ? slides.length : 0"
                :autoplay="slides.length > 1 ? { delay: 4500 } : false"
                :speed="700"
                :space-between="isMobile ? 10 : 8"
                :centered-slides="true"
                :watch-slides-progress="true"
                :slide-to-clicked-slide="true"
                :breakpoints="swiperBreakpoints"
                :observer="true"
                :observe-parents="true"
                :resize-observer="true"
                class="!overflow-visible"
                @swiper="handleSwiperInit"
            >
                <SwiperSlide v-for="(slide, index) in slides" :key="index">
                    <div :class="isMobile ? 'flex min-w-0 justify-center px-0.5 py-2' : 'flex min-w-0 justify-center px-0.5 py-3 sm:py-4'">
                        <div
                            class="home-jumbotron-card relative w-full max-w-full overflow-hidden rounded-2xl border bg-slate-900/60"
                        >
                            <div class="home-jumbotron-media home-jumbotron-aspect-slot relative">
                                <img
                                    :src="slide.image"
                                    :alt="slide.title"
                                    class="absolute inset-0 h-full w-full object-cover"
                                    :width="isMobile ? 900 : 1920"
                                    :height="isMobile ? 1200 : 1080"
                                    :loading="index === 0 ? 'eager' : 'lazy'"
                                    decoding="async"
                                />
                            </div>
                            <div
                                class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/85"
                            ></div>
                            <div
                                :class="
                                    isMobile
                                        ? 'absolute left-3 top-3 inline-flex rounded-full border border-white/10 bg-[rgba(0,0,0,0.42)] px-2 py-[3px] text-[10px] font-medium uppercase tracking-[0.2em] text-slate-100 backdrop-blur'
                                        : 'absolute left-3 top-3 inline-flex rounded-full border border-white/10 bg-[rgba(0,0,0,0.42)] px-2.5 py-1 text-[10px] font-medium uppercase tracking-[0.2em] text-slate-100 backdrop-blur sm:left-4 sm:top-4 sm:text-[11px]'
                                "
                            >
                                Gangsters
                            </div>
                            <div
                                :class="
                                    isMobile
                                        ? 'absolute right-3 top-3 inline-flex rounded-full border border-amber-400/20 bg-[rgba(0,0,0,0.38)] px-2 py-[3px] text-[10px] font-semibold text-amber-200 backdrop-blur'
                                        : 'absolute right-3 top-3 inline-flex rounded-full border border-amber-400/20 bg-[rgba(0,0,0,0.38)] px-2.5 py-1 text-[10px] font-semibold text-amber-200 backdrop-blur sm:right-4 sm:top-4 sm:text-[11px]'
                                "
                            >
                                {{ String(index + 1).padStart(2, "0") }}/{{ String(slides.length).padStart(2, "0") }}
                            </div>
                            <div
                                :class="
                                    isMobile
                                        ? 'absolute inset-x-2.5 bottom-2 rounded-2xl border border-white/10 bg-[rgba(0,0,0,0.32)] px-3 py-2.5 backdrop-blur-xl'
                                        : 'absolute inset-x-3 bottom-3 rounded-2xl border border-white/10 bg-[rgba(0,0,0,0.32)] px-4 py-3 backdrop-blur-xl sm:inset-x-0 sm:bottom-0 sm:rounded-none sm:border-0 sm:bg-transparent sm:px-6 sm:py-4 sm:backdrop-blur-0'
                                "
                            >
                                <h1
                                    :class="
                                        isMobile
                                            ? 'text-xl font-semibold leading-tight text-amber-300'
                                            : 'text-xl font-semibold leading-tight text-amber-300 sm:text-2xl'
                                    "
                                >
                                    {{ slide.title }}
                                </h1>
                                <p
                                    :class="
                                        isMobile
                                            ? 'mt-1 text-xs leading-relaxed text-slate-200/90'
                                            : 'mt-1 max-w-[18rem] text-xs leading-relaxed text-slate-200/90 sm:max-w-none sm:text-sm'
                                    "
                                >
                                    {{ slide.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </SwiperSlide>
            </Swiper>

            <div
                v-if="!isMobile && slides.length > 1"
                class="pointer-events-none absolute inset-y-0 left-0 right-0 z-20 hidden items-center justify-between px-5 md:flex lg:px-8"
            >
                <button
                    type="button"
                    class="pointer-events-auto inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/15 bg-black/45 text-slate-100 backdrop-blur transition hover:border-amber-300/40 hover:text-amber-200"
                    aria-label="Предыдущий слайд"
                    @click="goPrev"
                >
                    <i class="mdi mdi-chevron-left text-2xl" aria-hidden="true"></i>
                </button>
                <button
                    type="button"
                    class="pointer-events-auto inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/15 bg-black/45 text-slate-100 backdrop-blur transition hover:border-amber-300/40 hover:text-amber-200"
                    aria-label="Следующий слайд"
                    @click="goNext"
                >
                    <i class="mdi mdi-chevron-right text-2xl" aria-hidden="true"></i>
                </button>
            </div>

            <div
                v-if="!isLoading && !slides.length"
                class="mx-auto max-w-4xl py-8 text-center text-xs text-slate-500"
            >
                Баннеры скоро появятся.
            </div>
        </div>
    </section>
</template>

<style scoped>
/*
 * Карточка всегда w-full от слайда — иначе fit-content + min(100%,…) даёт цикл и ширина 0.
 * Медиа: min(100%, …) от карточки; десктоп = витрина под 1920×1080 (16:9), мобила = 3:4.
 */
.home-jumbotron--mobile .home-jumbotron-card {
    width: 100%;
    max-width: 100%;
}

/* Мобила: арт 3:4 (выше слот — запас под scale активного слайда) */
.home-jumbotron--mobile .home-jumbotron-aspect-slot {
    --jh-m-h: min(88dvh, 960px);
    width: min(100%, calc(var(--jh-m-h) * 3 / 4));
    max-height: var(--jh-m-h);
    aspect-ratio: 3 / 4;
    max-width: 100%;
    margin-inline: auto;
}

@media (orientation: landscape) {
    .home-jumbotron--mobile .home-jumbotron-aspect-slot {
        --jh-ml-h: min(88dvh, 520px);
        width: min(100%, calc(var(--jh-ml-h) * 16 / 9));
        max-height: var(--jh-ml-h);
        aspect-ratio: 16 / 9;
    }
}

.home-jumbotron--desktop .home-jumbotron-card {
    width: 100%;
    max-width: 100%;
}

/* Десктоп: витрина под 1920×1080 (16:9) */
.home-jumbotron--desktop .home-jumbotron-aspect-slot {
    --jh-d-h: min(75dvh, 1080px);
    width: min(100%, 1920px, calc(var(--jh-d-h) * 16 / 9));
    max-height: var(--jh-d-h);
    aspect-ratio: 16 / 9;
    max-width: 100%;
    margin-inline: auto;
}

.home-jumbotron--desktop :deep(.swiper-slide) {
    min-width: 0;
}

.home-jumbotron--mobile :deep(.swiper-slide) {
    min-width: 0;
}

.home-jumbotron--desktop :deep(.swiper-slide .home-jumbotron-card) {
    transform: scale(0.86);
    opacity: 0;
    border-color: transparent;
    transition:
        transform 500ms ease,
        opacity 500ms ease,
        border-color 500ms ease,
        box-shadow 500ms ease;
}

.home-jumbotron--desktop :deep(.swiper-slide-prev .home-jumbotron-card),
.home-jumbotron--desktop :deep(.swiper-slide-next .home-jumbotron-card) {
    transform: scale(0.92);
    opacity: 0.55;
    cursor: pointer;
    border-color: rgba(255, 255, 255, 0.1);
}

.home-jumbotron--desktop :deep(.swiper-slide-prev .home-jumbotron-card:hover),
.home-jumbotron--desktop :deep(.swiper-slide-next .home-jumbotron-card:hover) {
    opacity: 0.7;
}

.home-jumbotron--desktop :deep(.swiper-slide-active .home-jumbotron-card) {
    transform: scale(1.02);
    opacity: 1;
    border-color: rgba(251, 191, 36, 0.35);
    box-shadow: 0 24px 50px rgba(0, 0, 0, 0.45);
    z-index: 10;
}

@media (max-width: 767px) {
    .home-jumbotron--desktop :deep(.swiper-slide .home-jumbotron-card) {
        transform: scale(0.82);
    }

    .home-jumbotron--desktop :deep(.swiper-slide-prev .home-jumbotron-card),
    .home-jumbotron--desktop :deep(.swiper-slide-next .home-jumbotron-card) {
        transform: scale(0.9);
        opacity: 0.58;
    }

    .home-jumbotron--desktop :deep(.swiper-slide-active .home-jumbotron-card) {
        transform: scale(1.02);
        box-shadow: 0 20px 42px rgba(0, 0, 0, 0.42);
    }
}

.home-jumbotron--mobile :deep(.swiper),
.home-jumbotron--mobile :deep(.swiper-wrapper),
.home-jumbotron--mobile :deep(.swiper-slide),
.home-jumbotron--mobile :deep(.swiper-3d) {
    overflow-x: visible !important;
    overflow-y: visible !important;
    overflow: visible !important;
}

.home-jumbotron--mobile :deep(.swiper .swiper-wrapper),
.home-jumbotron--mobile :deep(.swiper .swiper-slide),
.home-jumbotron--mobile :deep(.swiper .swiper-3d),
.home-jumbotron--mobile :deep(.swiper-slide-active) {
    overflow: visible !important;
}

.home-jumbotron--mobile :deep(.swiper),
.home-jumbotron--mobile :deep(.swiper-wrapper) {
    height: auto !important;
    min-height: 0 !important;
}

.home-jumbotron--mobile :deep(.swiper-wrapper) {
    align-items: stretch !important;
}

.home-jumbotron--mobile :deep(.swiper-slide) {
    padding-top: clamp(18px, 5dvh, 36px) !important;
    padding-bottom: clamp(18px, 5dvh, 36px) !important;
    box-sizing: border-box;
}

.home-jumbotron--mobile :deep(.swiper-slide .home-jumbotron-card) {
    transform: scale(0.78);
    opacity: 0;
    border-color: transparent;
    transition:
        transform 500ms ease,
        opacity 500ms ease,
        border-color 500ms ease,
        box-shadow 500ms ease;
}

.home-jumbotron--mobile :deep(.swiper-slide-prev .home-jumbotron-card),
.home-jumbotron--mobile :deep(.swiper-slide-next .home-jumbotron-card) {
    transform: scale(0.86);
    opacity: 0.58;
    cursor: pointer;
    border-color: rgba(255, 255, 255, 0.1);
}

.home-jumbotron--mobile :deep(.swiper-slide-prev .home-jumbotron-card:hover),
.home-jumbotron--mobile :deep(.swiper-slide-next .home-jumbotron-card:hover) {
    opacity: 0.7;
}

.home-jumbotron--mobile :deep(.swiper-slide-active .home-jumbotron-card) {
    transform: scale(1.14);
    opacity: 1;
    border-color: rgba(251, 191, 36, 0.35);
    box-shadow: 0 24px 50px rgba(0, 0, 0, 0.45);
    z-index: 10;
}
</style>
